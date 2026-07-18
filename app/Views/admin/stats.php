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
        <div class="employe-table-wrap" style="margin-bottom:48px;">
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

        <div class="stats-chart-wrap reveal">
            <h2 class="menu-detail-section-title">Commandes par menu</h2>
            <canvas id="statsChart" height="100"></canvas>
        </div>
    </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-jb8JQMbMoBUzgWatfe6COACi2ljcDdZQ2OxczGA3bGNeWe+6DChMTBJemed7ZnvJ" crossorigin="anonymous"></script>
<script nonce="<?= CSP_NONCE ?>">
const ctx = document.getElementById('statsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($stats, 'titre'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        datasets: [{
            label: 'Nombre de commandes',
            data: <?= json_encode(array_column($stats, 'nb_commandes')) ?>,
            backgroundColor: 'rgba(196, 82, 10, 0.7)',
            borderColor: '#C4520A',
            borderWidth: 1
        }, {
            label: 'Chiffre d\'affaires (€)',
            data: <?= json_encode(array_column($stats, 'chiffre_affaires')) ?>,
            backgroundColor: 'rgba(46, 64, 53, 0.7)',
            borderColor: '#2E4035',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</main>
