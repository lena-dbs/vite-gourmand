<?php
// --- Préparation des données ---
$moisFr = [1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.', 5 => 'mai', 6 => 'juin',
           7 => 'juil.', 8 => 'août', 9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.'];

// Indicateurs de synthèse (sur les commandes honorées, toutes périodes)
$totalCommandes = array_sum(array_map(fn ($r) => (int)$r['nb_commandes'], $stats));
$totalCA        = array_sum(array_map(fn ($r) => (float)$r['chiffre_affaires'], $stats));
$panierMoyen    = $totalCommandes > 0 ? $totalCA / $totalCommandes : 0;
$topMenu        = $stats[0]['titre'] ?? '—';

// Vue « Par mois » : timeline chronologique (mois réellement présents)
$moisLabels = array_map(function ($row) use ($moisFr) {
    [$annee, $mois] = explode('-', (string)$row['periode']);
    return $moisFr[(int)$mois] . ' ' . $annee;
}, $statsMois);

// Vue « Par année » : chaque année éclatée sur 12 mois (janvier → décembre, zéros comblés)
$parAnnee = [];
foreach ($statsMois as $row) {
    [$y, $m] = array_map('intval', explode('-', (string)$row['periode']));
    if (!isset($parAnnee[$y])) {
        $parAnnee[$y] = ['commandes' => array_fill(0, 12, 0), 'ca' => array_fill(0, 12, 0.0)];
    }
    $parAnnee[$y]['commandes'][$m - 1] = (int)$row['nb_commandes'];
    $parAnnee[$y]['ca'][$m - 1]        = (float)$row['chiffre_affaires'];
}
krsort($parAnnee);
$annees = array_keys($parAnnee);
?>
<main>
<section class="employe-s">

    <div class="user-header reveal">
        <div class="wrap">
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Statistiques <em>& chiffres</em></h1>
        </div>
    </div>

    <div class="user-nav">
        <a href="/admin" class="user-nav-link">Commandes</a>
        <a href="/admin/menus" class="user-nav-link">Menus</a>
        <a href="/admin/avis" class="user-nav-link">Avis</a>
        <a href="/admin/employes" class="user-nav-link">Employés</a>
        <a href="/admin/stats" class="user-nav-link active">Statistiques</a>
        <form method="POST" action="/deconnexion" style="display:inline;"><?= $_csrf_field ?><button type="submit" class="user-nav-link user-nav-logout">Se déconnecter</button></form>
    </div>

    <div class="wrap">
        <form method="GET" action="/admin/stats" class="commande-filtres" style="background:#fff;border:1px solid #e5ddd3;border-radius:6px;padding:20px 24px;margin-bottom:32px;display:flex;flex-wrap:wrap;gap:20px;align-items:flex-end;">
            <div class="form-group" style="margin:0;">
                <label>Menu</label>
                <select name="menu_id">
                    <option value="">Tous les menus</option>
                    <?php foreach (($menus ?? []) as $mn): ?>
                        <option value="<?= $mn['menu_id'] ?>" <?= (string)($fMenu ?? '') === (string)$mn['menu_id'] ? 'selected' : '' ?>><?= htmlspecialchars($mn['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>Du</label>
                <input type="date" name="from" value="<?= htmlspecialchars($fFrom ?? '') ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Au</label>
                <input type="date" name="to" value="<?= htmlspecialchars($fTo ?? '') ?>">
            </div>
            <button type="submit" class="hbtn">Filtrer</button>
            <?php if (!empty($fMenu) || !empty($fFrom) || !empty($fTo)): ?>
                <a href="/admin/stats" class="menu-detail-back" style="align-self:center;">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <div class="stats-kpis">
            <div class="stats-kpi">
                <span class="stats-kpi-label">Commandes honorées</span>
                <span class="stats-kpi-value"><?= number_format($totalCommandes, 0, ',', ' ') ?></span>
            </div>
            <div class="stats-kpi">
                <span class="stats-kpi-label">Chiffre d'affaires</span>
                <span class="stats-kpi-value"><?= number_format($totalCA, 2, ',', ' ') ?> €</span>
            </div>
            <div class="stats-kpi">
                <span class="stats-kpi-label">Panier moyen</span>
                <span class="stats-kpi-value"><?= number_format($panierMoyen, 2, ',', ' ') ?> €</span>
            </div>
            <div class="stats-kpi">
                <span class="stats-kpi-label">Menu le plus vendu</span>
                <span class="stats-kpi-value stats-kpi-value-sm"><?= htmlspecialchars($topMenu) ?></span>
            </div>
        </div>

        <div class="stats-chart-wrap" style="margin-bottom:48px;">
            <div class="stats-chart-head">
                <h2 class="menu-detail-section-title">Évolution dans le temps</h2>
                <div class="stats-controls">
                    <label class="stats-year-field" data-role="year">
                        <span class="sr-only">Année</span>
                        <select id="statsYear" class="stats-year-select">
                            <?php foreach ($annees as $a): ?>
                            <option value="<?= $a ?>"><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="stats-toggle" role="group" aria-label="Choisir la période">
                        <button type="button" class="stats-toggle-btn active" data-periode="mois">Par mois</button>
                        <button type="button" class="stats-toggle-btn" data-periode="annee">Par année</button>
                    </div>
                </div>
            </div>
            <div class="stats-chart-canvas">
                <canvas id="statsTimeChart"></canvas>
            </div>
            <p class="stats-empty" id="statsTimeEmpty" hidden>Aucune commande honorée sur cette période.</p>
        </div>

        <div class="stats-chart-wrap" style="margin-bottom:48px;">
            <h2 class="menu-detail-section-title">Répartition par menu</h2>
            <p class="form-hint" style="margin-top:-8px;margin-bottom:16px;">Nombre de commandes et chiffre d'affaires par menu — source : <?= htmlspecialchars($statsSource ?? 'MongoDB') ?> (base non relationnelle).</p>
            <div class="stats-chart-canvas">
                <canvas id="statsChart"></canvas>
            </div>
        </div>

        <div class="employe-table-wrap">
            <table class="employe-table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Nb commandes</th>
                        <th>Chiffre d'affaires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statsMenu as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['titre']) ?></td>
                        <td><?= $stat['nb_commandes'] ?></td>
                        <td><?= number_format($stat['chiffre_affaires'], 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</section>

<script nonce="<?= CSP_NONCE ?>">
window.VG_STATS = {
    moisLabels: <?= json_encode($moisLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    statsMois: <?= json_encode(array_map(fn ($r) => [
        'nb_commandes' => (int)$r['nb_commandes'],
        'chiffre_affaires' => (float)$r['chiffre_affaires']
    ], $statsMois)) ?>,
    parAnnee: <?= json_encode($parAnnee, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    statsMenu: <?= json_encode(array_map(fn ($r) => [
        'titre' => $r['titre'],
        'nb_commandes' => (int)$r['nb_commandes'],
        'chiffre_affaires' => (float)$r['chiffre_affaires']
    ], $statsMenu), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>
<script src="/assets/js/vendor/chart.umd.min.js"></script>
<script src="/assets/js/pages/admin-stats.js"></script>
</main>
