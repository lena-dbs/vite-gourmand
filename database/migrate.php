<?php
declare(strict_types=1);

// Migration idempotente — à lancer en CLI uniquement :
//   fly ssh console -C "php /var/www/html/database/migrate.php"
if (php_sapi_name() !== 'cli') {
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

require_once __DIR__ . '/../config/config.php';

$db = new PDO(
    'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$db->exec('
    CREATE TABLE IF NOT EXISTS `login_attempt` (
        `attempt_id`      INT          NOT NULL AUTO_INCREMENT,
        `email`           VARCHAR(255) NOT NULL,
        `ip`              VARCHAR(45)  NOT NULL,
        `type`            VARCHAR(20)  NOT NULL DEFAULT \'login\',
        `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`attempt_id`),
        INDEX `idx_attempt_email` (`email`, `created_at`),
        INDEX `idx_attempt_ip` (`ip`, `created_at`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4
');

echo "Migration OK : table login_attempt en place.\n";

// Mise à jour de l'ENUM des statuts de commande (ajout de 'acceptee' et 'en_livraison',
// migration des anciennes valeurs 'prete' vers 'en_livraison'). Idempotent.
try {
    $db->exec("ALTER TABLE `suivi_commande` MODIFY `statut`
        ENUM('en_attente','acceptee','en_preparation','en_livraison','prete','livree','annulee','retour_materiel','terminee')
        NOT NULL DEFAULT 'en_attente'");
    $db->exec("UPDATE `suivi_commande` SET `statut` = 'en_livraison' WHERE `statut` = 'prete'");
    $db->exec("ALTER TABLE `suivi_commande` MODIFY `statut`
        ENUM('en_attente','acceptee','en_preparation','en_livraison','livree','annulee','retour_materiel','terminee')
        NOT NULL DEFAULT 'en_attente'");
    echo "Migration OK : statuts de commande mis a jour.\n";
} catch (\Throwable $e) {
    echo "Migration statuts ignoree : " . $e->getMessage() . "\n";
}

// Synchronisation des chemins d'images (menus + plats) vers les fichiers du dépôt. Idempotent.
try {
    $menusImg = [
        'Noël Prestige'                => '/assets/images/menu-noel.jpg',
        'Brunch Pâques Végétarien'     => '/assets/images/brunch-paques.jpg',
        'Cocktail Dînatoire Prestige'  => '/assets/images/menu-cocktail.jpg',
        'Anniversaire Sans Gluten'     => '/assets/images/menu-anniversaire.jpg',
    ];
    $stmtM = $db->prepare('UPDATE `menu` SET `image` = :img WHERE `titre` = :t');
    foreach ($menusImg as $t => $img) {
        $stmtM->execute([':img' => $img, ':t' => $t]);
    }

    $platsImg = [
        'Foie gras mi-cuit aux figues'               => '/assets/images/noel-foie-gras.jpg',
        'Magret de canard confit aux épices'         => '/assets/images/menu-noel.jpg',
        'Bûche artisanale chocolat Valrhona'         => '/assets/images/noel-buche.jpg',
        'Tarte salée poireaux et chèvre'             => '/assets/images/brunch-tarte.jpg',
        'Salade printanière aux fleurs comestibles'  => '/assets/images/brunch-paques.jpg',
        'Sablés décorés maison'                      => '/assets/images/brunch-sables.jpg',
        'Verrines avocat-crevettes'                  => '/assets/images/cocktail-verrines.jpg',
        'Planches charcuterie et fromages affinés'   => '/assets/images/cocktail-planche.jpg',
        'Mignardises et macarons maison'             => '/assets/images/cocktail-macaron.jpg',
        'Carpaccio de saint-jacques et agrumes'      => '/assets/images/anniversaire-carpaccio.jpg',
        'Filet de boeuf sauce bordelaise'            => '/assets/images/menu-anniversaire.jpg',
        'Fondant chocolat sans gluten'               => '/assets/images/anniversaire-fondant.jpg',
    ];
    $stmtP = $db->prepare('UPDATE `plat` SET `photo` = :p WHERE `nom` = :n');
    foreach ($platsImg as $n => $p) {
        $stmtP->execute([':p' => $p, ':n' => $n]);
    }
    echo "Migration OK : images des menus et plats synchronisees.\n";
} catch (\Throwable $e) {
    echo "Migration images ignoree : " . $e->getMessage() . "\n";
}

// Backfill des statistiques dans MongoDB (base non relationnelle) à partir des commandes.
// Idempotent : upsert par commande_id, donc relançable sans créer de doublons.
try {
    $rows = $db->query('
        SELECT c.commande_id, c.menu_id, m.titre AS menu_titre, c.prix_total, c.date_livraison
        FROM commande c
        JOIN menu m ON c.menu_id = m.menu_id
    ')->fetchAll(PDO::FETCH_ASSOC);

    $col = (new MongoDB\Client(MONGO_URI, ['serverSelectionTimeoutMS' => 2000]))
        ->vite_gourmand->statistiques;

    foreach ($rows as $r) {
        $col->updateOne(
            ['commande_id' => (int)$r['commande_id']],
            ['$set' => [
                'commande_id'    => (int)$r['commande_id'],
                'menu_id'        => (int)$r['menu_id'],
                'menu_titre'     => $r['menu_titre'],
                'prix_total'     => (float)$r['prix_total'],
                'date_livraison' => $r['date_livraison'],
                'created_at'     => new MongoDB\BSON\UTCDateTime(strtotime($r['date_livraison']) * 1000),
            ]],
            ['upsert' => true]
        );
    }
    echo 'Backfill MongoDB statistiques OK (' . count($rows) . " commandes).\n";
} catch (\Throwable $e) {
    echo 'Backfill MongoDB ignoré : ' . $e->getMessage() . "\n";
}
