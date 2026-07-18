<?php
declare(strict_types=1);

class CommandeModel extends Model
{
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
        // Remet le menu en stock à la première annulation
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

    public function getStatsByMenu(): array
    {
        $stmt = $this->db->query('
            SELECT
                m.titre,
                COUNT(c.commande_id) AS nb_commandes,
                SUM(c.prix_total) AS chiffre_affaires
            FROM commande c
            JOIN menu m ON c.menu_id = m.menu_id
            GROUP BY c.menu_id, m.titre
            ORDER BY nb_commandes DESC
        ');
        return $stmt->fetchAll();
    }

    public function saveStatToMongo(int $commandeId, int $menuId, string $menuTitre, float $prixTotal): void
    {
        try {
            $collection = MongoStats::getCollection('statistiques');
            $collection->insertOne([
                'commande_id'  => $commandeId,
                'menu_id'      => $menuId,
                'menu_titre'   => $menuTitre,
                'prix_total'   => $prixTotal,
                'created_at'   => new \MongoDB\BSON\UTCDateTime(),
            ]);
        } catch (\Exception $e) {
        }
    }

    public function getStatsFromMongo(): array
    {
        try {
            $collection = MongoStats::getCollection('statistiques');
            $pipeline = [
                [
                    '$group' => [
                        '_id'              => '$menu_titre',
                        'nb_commandes'     => ['$sum' => 1],
                        'chiffre_affaires' => ['$sum' => '$prix_total'],
                    ]
                ],
                ['$sort' => ['nb_commandes' => -1]]
            ];
            $result = $collection->aggregate($pipeline)->toArray();
            return array_map(fn($r) => [
                'titre'            => $r['_id'],
                'nb_commandes'     => $r['nb_commandes'],
                'chiffre_affaires' => $r['chiffre_affaires'],
            ], $result);
        } catch (\Exception $e) {
            return [];
        }
    }
}
