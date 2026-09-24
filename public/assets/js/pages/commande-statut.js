(function () {
    var sel = document.getElementById('statut-select');
    var box = document.getElementById('annulation-fields');
    if (!sel || !box) return;
    var mc = box.querySelector('select[name=mode_contact]');
    var mo = box.querySelector('input[name=motif_annulation]');
    function toggle() {
        var on = sel.value === 'annulee';
        box.style.display = on ? 'block' : 'none';
        if (mc) mc.required = on;
        if (mo) mo.required = on;
    }
    sel.addEventListener('change', toggle); toggle();
})();
