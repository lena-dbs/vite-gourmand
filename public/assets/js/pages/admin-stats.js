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
    labels: window.VG_STATS.moisLabels,
    commandes: window.VG_STATS.statsMois.map(r => r.nb_commandes),
    ca: window.VG_STATS.statsMois.map(r => r.chiffre_affaires)
};
const parAnnee = window.VG_STATS.parAnnee;

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
        labels: window.VG_STATS.statsMenu.map(r => r.titre),
        datasets: [{
            label: 'Nombre de commandes', data: window.VG_STATS.statsMenu.map(r => r.nb_commandes),
            backgroundColor: 'rgba(196, 82, 10, 0.75)', borderRadius: 4, yAxisID: 'y'
        }, {
            label: 'Chiffre d\'affaires (€)', data: window.VG_STATS.statsMenu.map(r => r.chiffre_affaires),
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
