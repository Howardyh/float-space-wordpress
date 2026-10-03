'use strict';

(() => {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const pointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  const animations = new Map();
  const accordions = new Map();
  const surfaces = [...document.querySelectorAll('.project-card, .detail-showcase, .notes-teaser-inner')];
  let activeSurface = null;
  let pointerX = 0;
  let pointerY = 0;
  let pointerFrame = 0;

  const motionAllowed = () => !reduced.matches && !document.hidden;

  function animate(element, frames, delay = 0) {
    animations.get(element)?.cancel();
    if (!motionAllowed() || !element.animate) return;
    const animation = element.animate(frames, { duration: 280, delay, easing: 'cubic-bezier(.22,.75,.25,1)' });
    animations.set(element, animation);
    const cleanup = () => { if (animations.get(element) === animation) animations.delete(element); };
    animation.onfinish = cleanup;
    animation.oncancel = cleanup;
  }

  function clearSurface() {
    if (pointerFrame) cancelAnimationFrame(pointerFrame);
    pointerFrame = 0;
    if (!activeSurface) return;
    activeSurface.classList.remove('is-surface-active');
    activeSurface.style.removeProperty('--surface-x');
    activeSurface.style.removeProperty('--surface-y');
    activeSurface = null;
  }

  function paintSurface() {
    pointerFrame = 0;
    if (!activeSurface || !pointer.matches || !motionAllowed()) return clearSurface();
    const bounds = activeSurface.getBoundingClientRect();
    if (pointerX < bounds.left || pointerX > bounds.right || pointerY < bounds.top || pointerY > bounds.bottom) return clearSurface();
    activeSurface.style.setProperty('--surface-x', `${(pointerX - bounds.left).toFixed(1)}px`);
    activeSurface.style.setProperty('--surface-y', `${(pointerY - bounds.top).toFixed(1)}px`);
  }

  function requestSurface() {
    if (!pointerFrame) pointerFrame = requestAnimationFrame(paintSurface);
  }

  surfaces.forEach(surface => {
    surface.classList.add('motion-surface');
    surface.addEventListener('pointermove', event => {
      if (!pointer.matches || !motionAllowed() || event.pointerType === 'touch') return;
      if (activeSurface !== surface) {
        clearSurface();
        activeSurface = surface;
        surface.classList.add('is-surface-active');
      }
      pointerX = event.clientX;
      pointerY = event.clientY;
      requestSurface();
    }, { passive: true });
    surface.addEventListener('pointerleave', () => { if (activeSurface === surface) clearSurface(); });
  });
  window.addEventListener('scroll', () => { if (activeSurface) requestSurface(); }, { passive: true });
  window.addEventListener('resize', clearSurface);
  window.addEventListener('blur', clearSurface);
  pointer.addEventListener('change', clearSurface);

  document.querySelectorAll('.poster-bars, .sample-chart').forEach(chart => {
    [...chart.children].forEach((bar, index) => bar.style.setProperty('--bar-order', String(index % 6)));
  });

  const tabLists = [...document.querySelectorAll('.monitor-tabs')];
  function updateIndicator(tabList) {
    const selected = tabList.querySelector('[aria-selected="true"]');
    if (!selected) return;
    const bounds = tabList.getBoundingClientRect();
    const tab = selected.getBoundingClientRect();
    tabList.style.setProperty('--tab-left', `${(tab.left - bounds.left).toFixed(1)}px`);
    tabList.style.setProperty('--tab-width', `${tab.width.toFixed(1)}px`);
    tabList.classList.add('has-tab-indicator');
  }
  tabLists.forEach(updateIndicator);
  if ('ResizeObserver' in window) {
    const observer = new ResizeObserver(() => tabLists.forEach(updateIndicator));
    tabLists.forEach(list => {
      observer.observe(list);
      [...list.children].forEach(tab => observer.observe(tab));
    });
  } else {
    window.addEventListener('resize', () => tabLists.forEach(updateIndicator));
  }
  document.addEventListener('site:languagechange', () => tabLists.forEach(updateIndicator));

  document.addEventListener('site:demochange', event => {
    const demo = event.target;
    if (event.detail.kind === 'schedule') {
      const distance = event.detail.direction < 0 ? -8 : 8;
      demo.querySelectorAll('[data-course-list] > *').forEach((row, index) => {
        animate(row, [{ opacity: .55, transform: `translateX(${distance}px)` }, { opacity: 1, transform: 'none' }], index * 35);
      });
      animate(demo.querySelector('[data-date]'), [{ opacity: .6, transform: 'translateY(4px)' }, { opacity: 1, transform: 'none' }]);
    }
    if (event.detail.kind === 'tab') {
      updateIndicator(demo.querySelector('.monitor-tabs'));
      const panel = demo.querySelector('.monitor-panel:not([hidden])');
      animate(panel, [{ opacity: .55, transform: 'translateY(7px)' }, { opacity: 1, transform: 'none' }]);
    }
    if (event.detail.kind === 'sample') {
      demo.querySelectorAll('.monitor-panel:not([hidden]) [data-metric], .monitor-panel:not([hidden]) [data-performance-cpu], .monitor-panel:not([hidden]) [data-network-rx], .monitor-panel:not([hidden]) [data-network-tx]').forEach(value => {
        animate(value, [{ opacity: .45 }, { opacity: 1 }]);
      });
    }
  });

  function finishAccordion(detail) {
    const state = accordions.get(detail);
    if (!state) return;
    accordions.delete(detail);
    state.animation.cancel();
    detail.open = state.open;
    detail.style.removeProperty('overflow');
    delete detail.dataset.motionOpen;
  }

  // Keep native details as the fallback; only the height transition is enhanced.
  document.addEventListener('click', event => {
    const summary = event.target.closest('.course > summary');
    if (!summary || event.defaultPrevented || !motionAllowed() || !summary.parentElement.animate) return;
    event.preventDefault();
    const detail = summary.parentElement;
    const previous = accordions.get(detail);
    const open = !(previous ? previous.open : detail.open);
    const from = detail.getBoundingClientRect().height;
    if (previous) finishAccordion(detail);
    detail.open = true;
    const borders = getComputedStyle(detail);
    const to = open ? detail.getBoundingClientRect().height : summary.getBoundingClientRect().height + parseFloat(borders.borderTopWidth) + parseFloat(borders.borderBottomWidth);
    detail.style.overflow = 'clip';
    detail.dataset.motionOpen = String(open);
    const animation = detail.animate([{ height: `${from}px` }, { height: `${to}px` }], { duration: 260, easing: 'cubic-bezier(.22,.75,.25,1)' });
    accordions.set(detail, { animation, open });
    animation.onfinish = () => { if (accordions.get(detail)?.animation === animation) finishAccordion(detail); };
  });

  function settle() {
    clearSurface();
    animations.forEach(animation => animation.cancel());
    animations.clear();
    [...accordions.keys()].forEach(finishAccordion);
  }
  reduced.addEventListener('change', () => { if (reduced.matches) settle(); });
  document.addEventListener('visibilitychange', () => { if (document.hidden) settle(); });
})();
