// Filtres dynamiques sans rechargement
const filtreTheme     = document.getElementById('filtre-theme');
const filtreRegime    = document.getElementById('filtre-regime');
const filtrePrixMin   = document.getElementById('filtre-prix-min');
const filtrePrixMax   = document.getElementById('filtre-prix-max');
const filtrePersonnes = document.getElementById('filtre-personnes');
const filtreReset     = document.getElementById('filtre-reset');
const menus           = document.querySelectorAll('.menu-row');
const noResults       = document.getElementById('no-results');

function filtrer() {
    const theme     = filtreTheme.value.toLowerCase();
    const regime    = filtreRegime.value.toLowerCase();
    const prixMin   = filtrePrixMin.value ? parseFloat(filtrePrixMin.value) : null;
    const prixMax   = filtrePrixMax.value ? parseFloat(filtrePrixMax.value) : null;
    const personnes = filtrePersonnes.value ? parseInt(filtrePersonnes.value) : null;

    let visible = 0;

    menus.forEach(menu => {
        const mTheme     = menu.dataset.theme.toLowerCase();
        const mRegime    = menu.dataset.regime.toLowerCase();
        const mPrix      = parseFloat(menu.dataset.prix);
        const mPersonnes = parseInt(menu.dataset.personnes);

        const ok =
            (!theme     || mTheme === theme) &&
            (!regime    || mRegime === regime) &&
            (prixMin === null || mPrix >= prixMin) &&
            (prixMax === null || mPrix <= prixMax) &&
            (!personnes || mPersonnes <= personnes);

        menu.style.display = ok ? 'grid' : 'none';
        if (ok) visible++;
    });

    noResults.style.display = visible === 0 ? 'block' : 'none';
}

function reinitialiser() {
    filtreTheme.value = '';
    filtreRegime.value = '';
    filtrePrixMin.value = '';
    filtrePrixMax.value = '';
    filtrePersonnes.value = '';
    filtrer();
}

filtreTheme.addEventListener('change', filtrer);
filtreRegime.addEventListener('change', filtrer);
filtrePrixMin.addEventListener('input', filtrer);
filtrePrixMax.addEventListener('input', filtrer);
filtrePersonnes.addEventListener('input', filtrer);
filtreReset.addEventListener('click', reinitialiser);
