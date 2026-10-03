'use strict';

// Progressive enhancement: the pages, projects and filing links work without JS.
document.documentElement.classList.add('js');
window.SitePreferences.initialize();

const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.primary-nav');
const siteHeader = document.querySelector('.site-header');
const mobile = window.matchMedia('(max-width: 1023px)');

function setMenu(open, restoreFocus = false) {
  siteHeader.classList.toggle('menu-open', open);
  menuButton.setAttribute('aria-expanded', String(open));
  window.SitePreferences.setText(menuButton.querySelector('span:first-child'), open ? 'Close' : 'Menu');
  if (restoreFocus) menuButton.focus();
}

menuButton.addEventListener('click', () => {
  setMenu(menuButton.getAttribute('aria-expanded') !== 'true');
});

navigation.addEventListener('click', event => {
  if (event.target.closest('a')) setMenu(false);
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
    setMenu(false, true);
  }
});

document.addEventListener('click', event => {
  if (!siteHeader.contains(event.target) && menuButton.getAttribute('aria-expanded') === 'true') {
    setMenu(false);
  }
});

siteHeader.addEventListener('focusout', event => {
  if (mobile.matches && event.relatedTarget && !siteHeader.contains(event.relatedTarget)) setMenu(false);
});

mobile.addEventListener('change', () => setMenu(false));
document.addEventListener('site:languagechange', () => {
  setMenu(menuButton.getAttribute('aria-expanded') === 'true');
});
document.querySelectorAll('[data-year]').forEach(element => {
  element.textContent = String(new Date().getFullYear());
});

// Observation starts a finite entrance; content remains readable beforehand.
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let revealObserver;
function startReveals() {
  if (reducedMotion.matches || !('IntersectionObserver' in window)) return;
  revealObserver = new IntersectionObserver(entries => {
    for (const entry of entries) {
      if (!entry.isIntersecting) continue;
      entry.target.classList.add('is-in-view');
      revealObserver.unobserve(entry.target);
    }
  }, { threshold: .06, rootMargin: '0px 0px -20px 0px' });
  document.querySelectorAll('.featured-grid, .projects-heading, .project-card, .about-copy, .notes-teaser-inner, .page-intro, .detail-hero, .detail-showcase, .detail-section-copy, .architecture, .next-project, .notes-page-lead, .coming-soon').forEach(element => {
    if (element.classList.contains('is-in-view')) return;
    element.classList.add('reveal-ready');
    revealObserver.observe(element);
  });
}
startReveals();
reducedMotion.addEventListener('change', () => {
  if (revealObserver) revealObserver.disconnect();
  document.querySelectorAll('.reveal-ready').forEach(element => element.classList.add('is-in-view'));
  if (!reducedMotion.matches) startReveals();
});
