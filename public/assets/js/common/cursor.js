/* CURSEUR */
if (matchMedia('(pointer: fine)').matches) {
  const dot  = document.createElement('div');
  const ring = document.createElement('div');
  dot.className  = 'cursor-dot hide';
  ring.className = 'cursor-ring hide';
  document.body.append(dot, ring);
  document.body.classList.add('has-cursor');

  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let x = 0, y = 0, rx = 0, ry = 0, started = false;

  addEventListener('mousemove', e => {
    x = e.clientX; y = e.clientY;
    if (!started) { started = true; rx = x; ry = y; dot.classList.remove('hide'); ring.classList.remove('hide'); }
    dot.style.transform = `translate(${x}px,${y}px)`;
    const field = e.target.closest('input,textarea,select');
    dot.classList.toggle('hide', !!field);
    ring.classList.toggle('hide', !!field);
    ring.classList.toggle('grow', !field && !!e.target.closest('a,button,label,summary'));
  });

  (function suivre() {
    const k = reduced ? 1 : .16;
    rx += (x - rx) * k;
    ry += (y - ry) * k;
    ring.style.transform = `translate(${rx}px,${ry}px)`;
    requestAnimationFrame(suivre);
  })();

  addEventListener('mousedown', () => ring.classList.add('press'));
  addEventListener('mouseup',   () => ring.classList.remove('press'));
  document.documentElement.addEventListener('mouseleave', () => { dot.classList.add('hide'); ring.classList.add('hide'); });
  document.documentElement.addEventListener('mouseenter', () => { if (started) { dot.classList.remove('hide'); ring.classList.remove('hide'); } });
}
