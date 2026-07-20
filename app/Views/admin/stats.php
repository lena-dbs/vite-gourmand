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
                    <?php foreach ($stats as $stat): ?>
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

<script src="/assets/js/chart.umd.min.js"></script>
<script nonce="<?= CSP_NONCE ?>">
const COL_CMD = '#C4520A';
const COL_CA  = '#2E4035';

const euro = v => v.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

const tooltipLabel = c => c.dataset.yAxisID === 'y1'
    ? c.dataset.label + ' : ' + euro(c.parsed.y)
    : c.dataset.label + ' : ' + c.parsed.y;

const dualAxes = {
    y:  { position: 'left',  beginAtZero: true, ticks: { precision: 0, color: COL_CMD },
          title: { display: true, text: 'Commandes', color: COL_CMD }, grid: { color: 'rgba(38,26,13,.06)' } },
    y1: { position: 'right', beginAtZero: true, ticks: { color: COL_CA, callback: v => v.toLocaleString('fr-FR') + ' €' },
          title: { display: true, text: "Chiffre d'affaires", color: COL_CA }, grid: { drawOnChartArea: false } },
    x:  { ticks: { color: '#261A0D', font: { family: 'DM Sans' } }, grid: { display: false } }
};

// --- Graphique : évolution dans le temps ---
const moisFull = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

const dataMois = {
    labels: <?= json_encode($moisLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    commandes: <?= json_encode(array_map(fn ($r) => (int)$r['nb_commandes'], $statsMois)) ?>,
    ca: <?= json_encode(array_map(fn ($r) => (float)$r['chiffre_affaires'], $statsMois)) ?>
};
const parAnnee = <?= json_encode($parAnnee, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const yearSelect = document.getElementById('statsYear');
const yearField  = document.querySelector('[data-role="year"]');
const emptyMsg   = document.getElementById('statsTimeEmpty');
let periode = 'mois';

function currentData() {
    if (periode === 'annee') {
        const a = parAnnee[yearSelect.value] || { commandes: [], ca: [] };
        return { labels: moisFull, commandes: a.commandes, ca: a.ca };
    }
    return dataMois;
}

const timeCtx = document.getElementById('statsTimeChart').getContext('2d');
const timeChart = new Chart(timeCtx, {
    type: 'line',
    data: {
        labels: dataMois.labels,
        datasets: [{
            label: 'Nombre de commandes', data: dataMois.commandes,
            borderColor: COL_CMD, backgroundColor: 'rgba(196, 82, 10, 0.12)',
            fill: true, tension: 0.3, pointRadius: 3, yAxisID: 'y'
        }, {
            label: 'Chiffre d\'affaires (€)', data: dataMois.ca,
            borderColor: COL_CA, backgroundColor: 'rgba(46, 64, 53, 0.12)',
            fill: true, tension: 0.3, pointRadius: 3, yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { font: { family: 'DM Sans' } } },
            tooltip: { callbacks: { label: tooltipLabel } }
        },
        scales: dualAxes
    }
});

function refreshTime() {
    const d = currentData();
    const hasData = d.commandes.some(v => v > 0);
    timeChart.data.labels = d.labels;
    timeChart.data.datasets[0].data = d.commandes;
    timeChart.data.datasets[1].data = d.ca;
    timeChart.update();
    emptyMsg.hidden = hasData;
}

document.querySelectorAll('.stats-toggle-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        periode = btn.dataset.periode;
        document.querySelectorAll('.stats-toggle-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        yearField.style.display = periode === 'annee' ? '' : 'none';
        refreshTime();
    });
});
yearSelect.addEventListener('change', refreshTime);
yearField.style.display = 'none';

// --- Graphique : répartition par menu ---
const ctx = document.getElementById('statsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($stats, 'titre'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        datasets: [{
            label: 'Nombre de commandes', data: <?= json_encode(array_map(fn ($r) => (int)$r['nb_commandes'], $stats)) ?>,
            backgroundColor: 'rgba(196, 82, 10, 0.75)', borderRadius: 4, yAxisID: 'y'
        }, {
            label: 'Chiffre d\'affaires (€)', data: <?= json_encode(array_map(fn ($r) => (float)$r['chiffre_affaires'], $stats)) ?>,
            backgroundColor: 'rgba(46, 64, 53, 0.75)', borderRadius: 4, yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { font: { family: 'DM Sans' } } },
            tooltip: { callbacks: { label: tooltipLabel } }
        },
        scales: dualAxes
    }
});
</script>
</main>
