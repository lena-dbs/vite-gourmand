<?php
declare(strict_types=1);

class CommandeModel extends Model
{
    /** Statuts de commande dans l'ordre du cycle de vie, avec leur libellé. */
    public const STATUT_LABELS = [
        'en_attente'      => 'En attente',
        'acceptee'        => 'Acceptée',
        'en_preparation'  => 'En préparation',
        'en_livraison'    => 'En cours de livraison',
        'livree'          => 'Livrée',
        'retour_materiel' => 'En attente du retour de matériel',
        'terminee'        => 'Terminée',
        'annulee'         => 'Annulée',
    ];

    /** Libellé lisible d'un statut (avec accents). */
    public static function statutLabel(string $statut): string
    {
        return self::STATUT_LABELS[$statut] ?? ucfirst(str_replace('_', ' ', $statut));
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO commande (
                utilisateur_id, menu_id, nb_personnes,
                date_livraison, heure_livraison,
                prix_menu, prix_livraison, prix_reduction, prix_total,
                pret_materiel, restitution_materiel
            ) VALUES (
                :utilisateur_id, :menu_id, :nb_personnes,
                :date_livraison, :heure_livraison,
                :prix_menu, :prix_livraison, :prix_reduction, :prix_total,
                0, 0
            )
        ');
        $stmt->execute($data);
        $commandeId = (int)$this->db->lastInsertId();

        $suivi = $this->db->prepare('
            INSERT INTO suivi_commande (commande_id, statut)
            VALUES (:commande_id, "en_attente")
        ');
        $suivi->execute([':commande_id' => $commandeId]);

        return $commandeId;
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                c.*,
                m.titre AS menu_titre,
                m.image AS menu_image,
                s.statut AS statut_actuel
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            LEFT JOIN suivi_commande s ON s.suivi_id = (
                SELECT MAX(suivi_id) FROM suivi_commande WHERE commande_id = c.commande_id
            )
            WHERE c.utilisateur_id = :user_id
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('
            SELECT c.*, m.titre AS menu_titre, m.nb_personnes_min,
                   m.prix_base, u.nom, u.prenom, u.email, u.telephone
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            WHERE c.commande_id = :id
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getSuivi(int $commandeId): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM suivi_commande
            WHERE commande_id = :id
            ORDER BY created_at ASC
        ');
        $stmt->execute([':id' => $commandeId]);
        return $stmt->fetchAll();
    }

    public function getDernierStatut(int $commandeId): string
    {
        $stmt = $this->db->prepare('
            SELECT statut FROM suivi_commande
            WHERE commande_id = :id
            ORDER BY suivi_id DESC
            LIMIT 1
        ');
        $stmt->execute([':id' => $commandeId]);
        $suivi = $stmt->fetch();
        return $suivi ? $suivi['statut'] : 'en_attente';
    }

    public function canCancel(int $commandeId): bool
    {
        return $this->getDernierStatut($commandeId) === 'en_attente';
    }

    public function cancel(int $commandeId, string $motif): bool
    {
        $stmt = $this->db->prepare('
            UPDATE commande SET motif_annulation = :motif
            WHERE commande_id = :id
        ');
        $stmt->execute([':motif' => $motif, ':id' => $commandeId]);

        return $this->addSuivi($commandeId, 'annulee', $motif);
    }

    public function getAllFiltered(string $statut = '', string $search = '', int $page = 1, int $perPage = 15): array
    {
        $where = ' WHERE 1=1';
        $params = [];

        if ($statut) {
            $where .= ' AND s.statut = :statut';
            $params[':statut'] = $statut;
        }

        if ($search) {
            $where .= ' AND (u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $from = '
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
            LEFT JOIN suivi_commande s ON s.suivi_id = (
                SELECT MAX(suivi_id) FROM suivi_commande WHERE commande_id = c.commande_id
            )
        ';

        $countStmt = $this->db->prepare('SELECT COUNT(*) ' . $from . $where);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT c.*, m.titre AS menu_titre, u.nom, u.prenom, u.email, u.telephone, s.statut AS statut_actuel'
             . $from . $where . ' ORDER BY c.created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function addSuivi(int $commandeId, string $statut, string $commentaire = ''): bool
    {
        if ($statut === 'annulee' && !$this->hasStatut($commandeId, 'annulee')) {
            $this->restock($commandeId);
        }

        $stmt = $this->db->prepare('
            INSERT INTO suivi_commande (commande_id, statut, commentaire)
            VALUES (:commande_id, :statut, :commentaire)
        ');
        return $stmt->execute([
            ':commande_id' => $commandeId,
            ':statut'      => $statut,
            ':commentaire' => $commentaire ?: null,
        ]);
    }

    private function hasStatut(int $commandeId, string $statut): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1 FROM suivi_commande
            WHERE commande_id = :id AND statut = :statut
            LIMIT 1
        ');
        $stmt->execute([':id' => $commandeId, ':statut' => $statut]);
        return (bool)$stmt->fetch();
    }

    private function restock(int $commandeId): void
    {
        $stmt = $this->db->prepare('
            UPDATE menu m
            JOIN commande c ON c.menu_id = m.menu_id
            SET m.stock = m.stock + 1
            WHERE c.commande_id = :id
        ');
        $stmt->execute([':id' => $commandeId]);
    }

    public function getAvisEnAttente(): array
    {
        $stmt = $this->db->query('
            SELECT a.*, u.nom, u.prenom, m.titre AS menu_titre
            FROM avis a
            JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
            JOIN commande c ON a.commande_id = c.commande_id
            JOIN menu m ON c.menu_id = m.menu_id
            WHERE a.statut = "en_attente"
            ORDER BY a.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    public function updateAvis(int $id, string $statut): bool
    {
        $stmt = $this->db->prepare('
            UPDATE avis SET statut = :statut WHERE avis_id = :id
        ');
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    public function getAvisForCommande(int $commandeId): array|false
    {
        $stmt = $this->db->prepare('
            SELECT * FROM avis WHERE commande_id = :id LIMIT 1
        ');
        $stmt->execute([':id' => $commandeId]);
        return $stmt->fetch();
    }

    public function createAvis(int $userId, int $commandeId, int $note, string $commentaire): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO avis (utilisateur_id, commande_id, note, commentaire)
            VALUES (:utilisateur_id, :commande_id, :note, :commentaire)
        ');
        return $stmt->execute([
            ':utilisateur_id' => $userId,
            ':commande_id'    => $commandeId,
            ':note'           => $note,
            ':commentaire'    => $commentaire,
        ]);
    }

    public function getAvisValides(int $limit = 3): array
    {
        $stmt = $this->db->prepare('
            SELECT a.note, a.commentaire, a.created_at, u.prenom, u.nom, m.titre AS menu_titre
            FROM avis a
            JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
            JOIN commande c ON a.commande_id = c.commande_id
            JOIN menu m ON c.menu_id = m.menu_id
            WHERE a.statut = "valide"
            ORDER BY a.created_at DESC
            LIMIT ' . $limit);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Construit la clause de filtre commune aux stats (par menu et sur une durée). */
    private function statsFiltre(?int $menuId, ?string $from, ?string $to): array
    {
        $sql = '';
        $params = [];
        if ($menuId) {
            $sql .= ' AND c.menu_id = :menu';
            $params[':menu'] = $menuId;
        }
        if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $sql .= ' AND c.date_livraison >= :from';
            $params[':from'] = $from;
        }
        if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $sql .= ' AND c.date_livraison <= :to';
            $params[':to'] = $to;
        }
        return [$sql, $params];
    }

    public function getStatsByMenu(?int $menuId = null, ?string $from = null, ?string $to = null): array
    {
        // Ne compte que les commandes réellement honorées (livrées, retour matériel, terminées).
        // Filtrable par menu et sur une durée (dates de livraison).
        [$filtreSql, $params] = $this->statsFiltre($menuId, $from, $to);
        $stmt = $this->db->prepare('
            SELECT
                m.titre,
                COUNT(c.commande_id) AS nb_commandes,
                SUM(c.prix_total) AS chiffre_affaires
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            JOIN suivi_commande s ON s.suivi_id = (
                SELECT MAX(suivi_id) FROM suivi_commande WHERE commande_id = c.commande_id
            )
            WHERE s.statut IN ("livree", "retour_materiel", "terminee")' . $filtreSql . '
            GROUP BY c.menu_id, m.titre
            ORDER BY nb_commandes DESC
        ');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStatsByMonth(?int $menuId = null, ?string $from = null, ?string $to = null): array
    {
        // Commandes honorées regroupées par mois de livraison (AAAA-MM), mêmes filtres.
        [$filtreSql, $params] = $this->statsFiltre($menuId, $from, $to);
        $stmt = $this->db->prepare('
            SELECT
                DATE_FORMAT(c.date_livraison, "%Y-%m") AS periode,
                COUNT(c.commande_id) AS nb_commandes,
                SUM(c.prix_total) AS chiffre_affaires
            FROM commande c
            JOIN suivi_commande s ON s.suivi_id = (
                SELECT MAX(suivi_id) FROM suivi_commande WHERE commande_id = c.commande_id
            )
            WHERE s.statut IN ("livree", "retour_materiel", "terminee")' . $filtreSql . '
            GROUP BY DATE_FORMAT(c.date_livraison, "%Y-%m")
            ORDER BY periode ASC
        ');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function saveStatToMongo(int $commandeId, int $menuId, string $menuTitre, float $prixTotal, string $dateLivraison = ''): void
    {
        try {
            $collection = MongoStats::getCollection('statistiques');
            // upsert par commande_id pour rester idempotent
            $collection->updateOne(
                ['commande_id' => $commandeId],
                ['$set' => [
                    'commande_id'    => $commandeId,
                    'menu_id'        => $menuId,
                    'menu_titre'     => $menuTitre,
                    'prix_total'     => $prixTotal,
                    'date_livraison' => $dateLivraison,
                    'created_at'     => new \MongoDB\BSON\UTCDateTime(),
                ]],
                ['upsert' => true]
            );
        } catch (\Exception $e) {
        }
    }

    /**
     * Nombre de commandes et chiffre d'affaires par menu, lus depuis MongoDB (base non relationnelle).
     * Filtrable par menu et sur une durée (dates de livraison). Renvoie le même format que getStatsByMenu.
     */
    public function getStatsByMenuMongo(?int $menuId = null, ?string $from = null, ?string $to = null): array
    {
        try {
            $match = [];
            if ($menuId) {
                $match['menu_id'] = $menuId;
            }
            $dateCond = [];
            if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
                $dateCond['$gte'] = $from;
            }
            if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                $dateCond['$lte'] = $to;
            }
            if ($dateCond) {
                $match['date_livraison'] = $dateCond;
            }

            $pipeline = [];
            if ($match) {
                $pipeline[] = ['$match' => $match];
            }
            $pipeline[] = ['$group' => [
                '_id'              => '$menu_titre',
                'nb_commandes'     => ['$sum' => 1],
                'chiffre_affaires' => ['$sum' => '$prix_total'],
            ]];
            $pipeline[] = ['$sort' => ['nb_commandes' => -1]];

            $cursor = MongoStats::getCollection('statistiques')->aggregate($pipeline);
            $out = [];
            foreach ($cursor as $doc) {
                $out[] = [
                    'titre'            => (string)$doc['_id'],
                    'nb_commandes'     => (int)$doc['nb_commandes'],
                    'chiffre_affaires' => (float)$doc['chiffre_affaires'],
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
