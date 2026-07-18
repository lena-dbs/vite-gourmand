<main>
<section class="employe-s">
    <div class="wrap">

        <div class="user-header reveal">
            <p class="kicker">Espace administrateur</p>
            <h1 class="sec-h2">Statistiques <em>& chiffres</em></h1>
        </div>

        <div class="user-nav">
            <a href="/admin" class="user-nav-link">Commandes</a>
            <a href="/admin/menus" class="user-nav-link">Menus</a>
            <a href="/admin/avis" class="user-nav-link">Avis</a>
            <a href="/admin/employes" class="user-nav-link">Employés</a>
            <a href="/admin/stats" class="user-nav-link active">Statistiques</a>
            <a href="/deconnexion" class="user-nav-link user-nav-logout">Se déconnecter</a>
        </div>

        <!-- Tableau des stats -->
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

        <!-- Graphique -->
        <div class="stats-chart-wrap reveal">
            <h2 class="menu-detail-section-title">Commandes par menu</h2>
            <canvas id="statsChart" height="100"></canvas>
        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($stats, 'titre')) ?>,
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