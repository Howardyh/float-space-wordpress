'use strict';

// Native links and lists remain usable when JavaScript is unavailable.
(() => {
  const header = document.querySelector('[data-navigation]');
  if (!header) return;
  const menuButton = header.querySelector('.menu-toggle');
  const panel = header.querySelector('.navigation-panel');
  const navigation = header.querySelector('.primary-nav');
  const backdrop = header.querySelector('[data-nav-backdrop]');
  if (!menuButton || !panel || !navigation) return;

  const mobile = window.matchMedia('(max-width: 1023px)');
  const items = Array.from(navigation.querySelectorAll('.nav-item.has-children'));
  let drawerMode = false;
  let layoutFrame = 0;
  let scrollFrame = 0;
  const chinese = () => (window.SitePreferences?.language || document.documentElement.lang).startsWith('zh');
  const directRow = item => Array.from(item.children).find(element => element.classList.contains('nav-row'));
  const toggleFor = item => directRow(item)?.querySelector('.nav-submenu-toggle');
  const submenuFor = item => Array.from(item.children).find(element => element.classList.contains('nav-submenu'));
  const isOpen = () => header.classList.contains('menu-open');
  const visible = element => element.getClientRects().length > 0 && !element.closest('[inert]');

  function revealFocusedControl(element) {
    let container = element.parentElement;
    while (container && container !== header) {
      if (container.matches('.navigation-panel, .nav-submenu')
        && /^(auto|scroll)$/.test(getComputedStyle(container).overflowY)
        && container.scrollHeight > container.clientHeight + 1) {
        const viewport = container.getBoundingClientRect();
        const target = element.getBoundingClientRect();
        const top = viewport.top + container.clientTop + 4;
        const bottom = viewport.top + container.clientTop + container.clientHeight - 4;
        if (target.top < top || target.height > bottom - top) container.scrollTop += target.top - top;
        else if (target.bottom > bottom) container.scrollTop += target.bottom - bottom;
        return;
      }
      container = container.parentElement;
    }
  }

  const focus = element => {
    if (!element || !visible(element)) return;
    element.focus({preventScroll: true});
    // Reveal keyboard targets inside navigation without moving the page below.
    revealFocusedControl(element);
  };

  function updateItemLabel(item) {
    const toggle = toggleFor(item);
    const link = directRow(item)?.querySelector('.nav-link');
    if (!toggle || !link) return;
    const label = (link.querySelector('.nav-label') || link.querySelector('[data-wp-zh][data-wp-en]') || link).textContent.trim();
    const open = item.classList.contains('is-open');
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', chinese()
      ? `${open ? '收起' : '展开'}${label}子菜单`
      : `${open ? 'Collapse' : 'Expand'} ${label} submenu`);
  }

  function closeBranch(item) {
    item.classList.remove('is-open');
    updateItemLabel(item);
    item.querySelectorAll('.nav-item.has-children').forEach(child => {
      child.classList.remove('is-open');
      updateItemLabel(child);
    });
  }

  function closeAllSubmenus() {
    items.forEach(item => {
      item.classList.remove('is-open');
      updateItemLabel(item);
    });
  }

  function openItem(item) {
    // A separate button opens the branch; its adjacent link still navigates.
    Array.from(item.parentElement.children).forEach(sibling => {
      if (sibling !== item && sibling.classList.contains('has-children')) closeBranch(sibling);
    });
    item.classList.add('is-open');
    updateItemLabel(item);
  }

  function updateMenuLabel() {
    const label = menuButton.querySelector('[data-wp-zh][data-wp-en]');
    const open = isOpen();
    if (label) {
      label.setAttribute('data-wp-zh', open ? '关闭' : '菜单');
      label.setAttribute('data-wp-en', open ? 'Close' : 'Menu');
      label.textContent = label.getAttribute(chinese() ? 'data-wp-zh' : 'data-wp-en');
    }
    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute('aria-label', chinese()
      ? (open ? '关闭导航菜单' : '打开导航菜单')
      : (open ? 'Close navigation menu' : 'Open navigation menu'));
    if (backdrop) backdrop.setAttribute('aria-label', chinese() ? '关闭菜单' : 'Close menu');
  }

  function updateCompact() {
    // Keep the open panel steady while the page scrolls underneath it.
    if (!isOpen()) header.classList.toggle('is-compact', window.scrollY > 24);
  }

  function updatePanelPosition() {
    // The WordPress toolbar and wrapped brand can change the actual header edge.
    const bottom = Math.max(0, header.getBoundingClientRect().bottom);
    header.style.setProperty('--navigation-panel-top', `${Math.ceil(bottom)}px`);
  }

  function setPanel(open, restoreFocus = false) {
    open = Boolean(open && drawerMode);
    header.classList.toggle('menu-open', open);
    panel.inert = drawerMode && !open;
    if (drawerMode && !open) panel.setAttribute('aria-hidden', 'true');
    else panel.removeAttribute('aria-hidden');
    if (!open) closeAllSubmenus();
    updateMenuLabel();
    updateCompact();
    updatePanelPosition();
    if (restoreFocus) focus(menuButton);
  }

  const gap = element => parseFloat(getComputedStyle(element).columnGap) || 0;

  function refreshLayout() {
    layoutFrame = 0;
    let overflow = false;
    if (!mobile.matches) {
      // Measure the normal desktop row, including administrator-added labels.
      // Classes are restored in the same frame, without cloning controls or IDs.
      header.classList.remove('nav-overflow', 'is-drawer');
      const inner = header.querySelector('.header-inner');
      const brand = header.querySelector('.brand');
      const list = navigation.querySelector('.nav-list');
      const tools = panel.querySelector('.navigation-tools');
      if (inner && brand && list) {
        const rows = Array.from(list.children).map(item => directRow(item) || item);
        const navigationWidth = rows.reduce((width, row) => width + row.getBoundingClientRect().width, 0)
          + Math.max(0, rows.length - 1) * gap(list);
        const toolsWidth = tools ? tools.getBoundingClientRect().width : 0;
        const innerStyle = getComputedStyle(inner);
        const availableWidth = inner.clientWidth - (parseFloat(innerStyle.paddingLeft) || 0) - (parseFloat(innerStyle.paddingRight) || 0);
        const requiredWidth = brand.getBoundingClientRect().width + navigationWidth + toolsWidth
          + gap(inner) + (tools ? gap(panel) : 0);
        overflow = requiredWidth > availableWidth + 1;
      }
    }
    const nextMode = mobile.matches || overflow;
    header.classList.toggle('nav-overflow', overflow);
    header.classList.toggle('is-drawer', nextMode);
    const changed = nextMode !== drawerMode;
    drawerMode = nextMode;
    if (changed) {
      const active = document.activeElement;
      setPanel(false, nextMode && panel.contains(active));
      if (!nextMode && active instanceof HTMLElement && !visible(active)) {
        focus(navigation.querySelector('.nav-link'));
      }
    } else {
      panel.inert = drawerMode && !isOpen();
      if (panel.inert) panel.setAttribute('aria-hidden', 'true');
      else panel.removeAttribute('aria-hidden');
    }
    header.classList.add('navigation-ready');
    updatePanelPosition();
  }

  function queueLayout() {
    if (!layoutFrame) layoutFrame = window.requestAnimationFrame(refreshLayout);
  }

  function branchLinks(item) {
    return Array.from(submenuFor(item)?.querySelectorAll('.nav-link') || []).filter(visible);
  }

  menuButton.addEventListener('click', () => setPanel(!isOpen()));
  menuButton.addEventListener('keydown', event => {
    if (!drawerMode || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
    event.preventDefault();
    setPanel(true);
    const links = Array.from(navigation.querySelectorAll('.nav-link')).filter(visible);
    focus(event.key === 'ArrowUp' ? links[links.length - 1] : links[0]);
  });
  if (backdrop) backdrop.addEventListener('click', () => setPanel(false, true));

  navigation.addEventListener('click', event => {
    if (!(event.target instanceof Element)) return;
    const toggle = event.target.closest('.nav-submenu-toggle');
    if (toggle) {
      const item = toggle.closest('.nav-item.has-children');
      if (!item) return;
      if (item.classList.contains('is-open')) closeBranch(item);
      else openItem(item);
      return;
    }
    const link = event.target.closest('.nav-link');
    if (!link || !drawerMode) return;
    setPanel(false);
    // A same-page anchor keeps its normal scroll and moves focus into content.
    if (!event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey && link.target !== '_blank') {
      const destination = new URL(link.href, window.location.href);
      if (destination.origin === window.location.origin && destination.pathname === window.location.pathname && destination.hash) {
        let target;
        try { target = document.getElementById(decodeURIComponent(destination.hash.slice(1))); } catch { return; }
        if (target instanceof HTMLElement) window.requestAnimationFrame(() => {
          if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
          target.focus({preventScroll: true});
        });
      }
    }
  });

  navigation.addEventListener('keydown', event => {
    if (!(event.target instanceof Element) || !['ArrowDown', 'ArrowUp', 'ArrowLeft'].includes(event.key)) return;
    if (event.key === 'ArrowLeft') {
      const submenu = event.target.closest('.nav-submenu');
      const parent = submenu?.parentElement;
      if (parent?.classList.contains('has-children') && parent.classList.contains('is-open')) {
        event.preventDefault();
        closeBranch(parent);
        focus(toggleFor(parent));
      }
      return;
    }
    const row = event.target.closest('.nav-row');
    const item = row?.parentElement;
    if (!item?.classList.contains('has-children')) return;
    event.preventDefault();
    openItem(item);
    const links = branchLinks(item);
    focus(event.key === 'ArrowUp' ? links[links.length - 1] : links[0]);
  });

  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    const opened = items.filter(item => item.classList.contains('is-open'));
    if (opened.length) {
      // Descendants are last in DOM order, so Escape closes the deepest branch.
      const branch = opened[opened.length - 1];
      event.preventDefault();
      closeBranch(branch);
      focus(toggleFor(branch));
    } else if (isOpen()) {
      event.preventDefault();
      setPanel(false, true);
    }
  });

  document.addEventListener('click', event => {
    if (!header.contains(event.target)) {
      closeAllSubmenus();
      if (isOpen()) setPanel(false);
    }
  });

  header.addEventListener('focusout', event => {
    const next = event.relatedTarget;
    if (!(next instanceof Node)) return;
    items.forEach(item => {
      if (item.classList.contains('is-open') && !item.contains(next)) closeBranch(item);
    });
    if (drawerMode && isOpen() && !header.contains(next)) setPanel(false);
  });

  window.addEventListener('resize', queueLayout, {passive: true});
  mobile.addEventListener('change', queueLayout);
  window.addEventListener('scroll', () => {
    if (scrollFrame) return;
    scrollFrame = window.requestAnimationFrame(() => {
      scrollFrame = 0;
      updateCompact();
      updatePanelPosition();
    });
  }, {passive: true});
  if ('ResizeObserver' in window) {
    // A finite height transition may run after the layout/scroll frame.
    const headerObserver = new ResizeObserver(updatePanelPosition);
    headerObserver.observe(header);
  }
  header.addEventListener('transitionend', event => {
    if (event.propertyName === 'min-height') updatePanelPosition();
  });
  document.addEventListener('site:languagechange', () => {
    items.forEach(updateItemLabel);
    updateMenuLabel();
    queueLayout();
  });
  if (document.fonts?.ready) document.fonts.ready.then(queueLayout);

  closeAllSubmenus();
  refreshLayout();
  setPanel(false);
  updateCompact();
})();
