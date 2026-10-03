'use strict';

(() => {
  const hero = document.querySelector('[data-motion-hero]');
  if (!hero) return;
  const wordmark = hero.querySelector('[data-interactive-wordmark]');
  const letters = [...wordmark.querySelectorAll('.hero-letter')].map(element => ({
    element, ink: element.querySelector('.hero-letter-ink'), x: 0, y: 0, rotation: 0, scale: 1,
    centerX: 0, centerY: 0, left: 0, top: 0
  }));
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  const target = { x: .62, y: .32, presence: 0 };
  const current = { ...target };
  let heroRect;
  let wordRect;
  let frame = 0;
  let previousTime = 0;
  let geometryDirty = true;
  let inside = false;
  let visible = true;
  let pointerX = 0;
  let pointerY = 0;

  const enabled = () => finePointer.matches && !reducedMotion.matches && visible && !document.hidden;
  const clamp = (value, low, high) => Math.max(low, Math.min(high, value));
  const px = value => `${value.toFixed(2)}px`;
  const degrees = value => `${value.toFixed(3)}deg`;

  function measure() {
    heroRect = hero.getBoundingClientRect();
    wordRect = wordmark.getBoundingClientRect();
    // Read geometry together before any style writes in this frame.
    const wordLeft = wordRect.left - heroRect.left - (current.x - .5) * 14 * current.presence;
    const wordTop = wordRect.top - heroRect.top - (current.y - .4) * 8 * current.presence;
    letters.forEach(letter => {
      letter.left = wordLeft + letter.element.offsetLeft;
      letter.top = wordTop + letter.element.offsetTop;
      letter.centerX = letter.left + letter.element.offsetWidth / 2;
      letter.centerY = letter.top + letter.element.offsetHeight / 2;
    });
    geometryDirty = false;
  }

  function writeScene() {
    const nx = (current.x - .5) * current.presence;
    const ny = (current.y - .4) * current.presence;
    const set = (name, value) => hero.style.setProperty(name, value);
    set('--pointer-presence', current.presence.toFixed(3));
    set('--glow-x', px(current.x * heroRect.width));
    set('--glow-y', px(current.y * heroRect.height));
    set('--grid-x', px(-nx * 18));
    set('--grid-y', px(-ny * 14));
    set('--grid-light-x', `${(current.x * 100).toFixed(2)}%`);
    set('--grid-light-y', `${(current.y * 100).toFixed(2)}%`);
    set('--word-x', px(nx * 14));
    set('--word-y', px(ny * 8));
    set('--word-rotate-x', degrees(-ny * 2));
    set('--word-rotate-y', degrees(nx * 3));
    set('--art-x', px(-nx * 26));
    set('--art-y', px(-ny * 18));
    set('--art-rotation', degrees(nx * 4));
  }

  function tick(time) {
    frame = 0;
    if (!enabled()) { reset(); return; }
    const delta = previousTime ? clamp(time - previousTime, 1, 40) : 16.67;
    previousTime = time;
    if (geometryDirty) measure();
    if (inside) {
      target.x = clamp((pointerX - heroRect.left) / heroRect.width, 0, 1);
      target.y = clamp((pointerY - heroRect.top) / heroRect.height, 0, 1);
    }
    const follow = 1 - Math.exp(-delta / 105);
    const spring = 1 - Math.exp(-delta / 72);
    let unsettled = false;
    for (const key of ['x', 'y', 'presence']) {
      const difference = target[key] - current[key];
      current[key] += difference * follow;
      if (Math.abs(difference) > .0005) unsettled = true;
      else current[key] = target[key];
    }

    const cursorX = current.x * heroRect.width;
    const cursorY = current.y * heroRect.height;
    const radius = Math.max(110, wordRect.height * .8);
    letters.forEach(letter => {
      const dx = cursorX - letter.centerX;
      const dy = cursorY - letter.centerY;
      const distance = Math.hypot(dx, dy);
      const proximity = Math.max(0, 1 - distance / radius) * current.presence;
      const strength = proximity * proximity;
      const letterTarget = {
        x: -(dx / Math.max(distance, 1)) * strength * 13,
        y: -(dy / Math.max(distance, 1)) * strength * 15,
        rotation: -(dx / radius) * proximity * 3,
        scale: 1 + proximity * .025
      };
      for (const key of ['x', 'y', 'rotation', 'scale']) {
        const difference = letterTarget[key] - letter[key];
        letter[key] += difference * spring;
        if (Math.abs(difference) > (key === 'scale' ? .0001 : .02)) unsettled = true;
        else letter[key] = letterTarget[key];
      }
      letter.element.style.setProperty('--letter-x', px(letter.x));
      letter.element.style.setProperty('--letter-y', px(letter.y));
      letter.element.style.setProperty('--letter-rotation', degrees(letter.rotation));
      letter.element.style.setProperty('--letter-scale', letter.scale.toFixed(4));
      letter.ink.style.setProperty('--letter-light-x', px(cursorX - letter.left - letter.x));
      letter.ink.style.setProperty('--letter-light-y', px(cursorY - letter.top - letter.y));
    });
    writeScene();
    if (unsettled) frame = requestAnimationFrame(tick);
    else {
      previousTime = 0;
      if (!inside) hero.classList.remove('is-pointer-active');
    }
  }

  function schedule() {
    if (enabled() && !frame) frame = requestAnimationFrame(tick);
  }

  function leave() {
    inside = false;
    target.x = .62;
    target.y = .32;
    target.presence = 0;
    schedule();
  }

  function reset() {
    if (frame) cancelAnimationFrame(frame);
    frame = 0;
    previousTime = 0;
    inside = false;
    target.x = current.x = .62;
    target.y = current.y = .32;
    target.presence = current.presence = 0;
    geometryDirty = true;
    hero.classList.remove('is-pointer-active');
    for (const name of ['--pointer-presence', '--glow-x', '--glow-y', '--grid-x', '--grid-y', '--grid-light-x', '--grid-light-y', '--word-x', '--word-y', '--word-rotate-x', '--word-rotate-y', '--art-x', '--art-y', '--art-rotation']) hero.style.removeProperty(name);
    letters.forEach(letter => {
      letter.x = letter.y = letter.rotation = 0;
      letter.scale = 1;
      for (const name of ['--letter-x', '--letter-y', '--letter-rotation', '--letter-scale']) letter.element.style.removeProperty(name);
      letter.ink.style.removeProperty('--letter-light-x');
      letter.ink.style.removeProperty('--letter-light-y');
    });
  }

  function invalidateGeometry() {
    geometryDirty = true;
    if (inside) schedule();
  }

  hero.addEventListener('pointermove', event => {
    if (!enabled() || event.pointerType !== 'mouse') return;
    pointerX = event.clientX;
    pointerY = event.clientY;
    if (!inside) geometryDirty = true;
    inside = true;
    target.presence = 1;
    hero.classList.add('is-pointer-active');
    schedule();
  }, { passive: true });
  hero.addEventListener('pointerleave', leave, { passive: true });
  hero.addEventListener('pointercancel', leave, { passive: true });
  window.addEventListener('blur', reset);
  window.addEventListener('resize', invalidateGeometry, { passive: true });
  window.addEventListener('scroll', invalidateGeometry, { passive: true });
  document.addEventListener('site:languagechange', invalidateGeometry);
  document.addEventListener('visibilitychange', () => { if (document.hidden) reset(); });
  reducedMotion.addEventListener('change', reset);
  finePointer.addEventListener('change', reset);
  if ('ResizeObserver' in window) new ResizeObserver(invalidateGeometry).observe(wordmark);
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(entries => {
      visible = entries[0].isIntersecting;
      if (!visible) reset();
    }).observe(hero);
  }
  if (!reducedMotion.matches) hero.classList.add('is-motion-ready');
})();
