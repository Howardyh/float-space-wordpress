'use strict';

(() => {
  const messages = {
    pageTitle: ["配置收藏 — FLOAT Space", "Collections — FLOAT Space"],
    pageDescription: ["原创虚构示例素材支持配置收藏、代码复制和本机草稿。", "Fictional original illustrations for collections, code copying, and local drafts."],
    heroLine1: ["示例", "Demo"], heroLine2: ["配置室", "collection"],
    heroDescription: ["整理配置、复制代码，并保存本机草稿。此页的示例素材均为原创虚构插画。", "Organize configurations, copy codes, and keep local drafts. All bundled artwork is original and fictional."],
    heroWeaponAlt: ["原创虚构示例步枪插画", "Original fictional demo rifle illustration"],
    heroWeaponCaption: ["原创虚构示例插画", "ORIGINAL FICTIONAL DEMO"],
    viewCollection: ['查看收藏', 'Browse collection'], newDraft: ['＋ 新建本机草稿', '+ New local draft'],
    collectionTitle: ['个人配置收藏', 'Personal collection'], loading: ['正在读取收藏与配件素材…', 'Loading the collection and attachment assets…'],
    searchLabel: ['搜索个人配置', 'Search loadouts'], searchPlaceholder: ['武器名称、配置码、备注…', 'Weapon, build code, notes…'],
    scopeLabel: ['收藏来源', 'Collection source'], published: ['公开收藏', 'Published'], drafts: ['本机草稿', 'Local drafts'],
    modeFilter: ["按模式筛选", "Filter by mode"], allModes: ['全部模式', 'All modes'],
    operations: ["模式 A", "Mode A"], warfare: ["模式 B", "Mode B"],
    import: ['导入草稿', 'Import drafts'], export: ['导出配置', 'Export JSON'], importFile: ['选择配置 JSON 文件', 'Choose a loadout JSON file'],
    step1: ["填入你的配置代码", "Enter your configuration code"],
    step2: ['记录武器与实际装配的配件', 'Record the weapon and its actual attachments'],
    step3: ['保存为本机草稿，导出后整理发布', 'Save a local draft, then export it for publishing'],
    listLabel: ['武器配置列表', 'Weapon loadouts'], detailLabel: ['配置详情', 'Loadout details'],
    iconNoteTitle: ["原创示例配件插画。", "Original demo attachment illustrations."],
    iconNote: ["随主题附带的插画均为虚构示例。你可按自己的用途扩展目录；未收录的配件可以先记录名称。", "Bundled illustrations are fictional examples. Adapt the catalog to your needs; record unlisted items by name."],
    iconPreview: ["示例配件图标预览", "Demo attachment previews"],
    editorTitle: ['整理一套配置', 'Create a loadout'], editTitle: ['编辑本机草稿', 'Edit a local draft'],
    closeEditor: ['关闭编辑器', 'Close editor'], close: ['关闭', 'Close'],
    draftNotice: ['草稿只保存在当前浏览器，不会自动发布到网站。建议导出备份，避免清理浏览器后丢失。', 'Drafts stay in this browser and are not published automatically. Export a backup before clearing browser data.'],
    nameLabel: ['配置名称', 'Loadout name'], namePlaceholder: ['给这套配置起个名字', 'Give this build a name'],
    weaponLabel: ['武器', 'Weapon'], chooseWeapon: ['请选择武器', 'Choose a weapon'], customWeaponLabel: ['未收录的武器名称', 'Unlisted weapon name'],
    customWeaponPlaceholder: ["填写名称，图片待补齐", "Enter a name; image pending"],
    modeLabel: ["配置模式", "Configuration mode"], seasonLabel: ['赛季 / 版本（可选）', 'Season / version (optional)'], seasonPlaceholder: ['例如：当前赛季', 'For example: current season'],
    codeLabel: ['完整配置码', 'Full configuration code'], codePlaceholder: ["填写完整配置代码", "Enter the full configuration code"],
    codeHelp: ["代码原样保存；配件需要按实际配置选择，不会自动解析。", "The code is kept as entered. Select the actual attachments manually; the code does not auto-fill them."],
    notesLabel: ['使用心得 / 精校说明（可选）', 'Notes / calibration (optional)'], notesPlaceholder: ['适用距离、操作手感、精校设置…', 'Range, handling, calibration settings…'],
    attachmentsTitle: ['选择实际配件', 'Choose the actual attachments'], slotFilter: ['配件分类', 'Attachment category'],
    attachmentSearchLabel: ['搜索配件名称', 'Search attachment names'], attachmentSearchPlaceholder: ["搜索配件名称…", "Search attachment names…"],
    showMore: ['显示更多', 'Show more'], missingAttachment: ['找不到配件？先记录名称', 'Missing an attachment? Record its name'],
    missingAttachmentHelp: ['未收录的图标显示“待补图”，不会使用其他配件图片代替。', 'Unlisted attachments show “Icon pending” until their actual images are added.'],
    customAttachmentName: ['缺失配件的名称', 'Missing attachment name'], customAttachmentPlaceholder: ["配件名称", "Attachment name"],
    add: ['添加', 'Add'], cancel: ['取消', 'Cancel'], saveDraft: ['保存本机草稿', 'Save local draft'],
    manualCopyTitle: ['复制配置码', 'Copy configuration code'], manualCopyHelp: ['浏览器暂时无法自动复制。代码已选中，可长按复制或使用 Ctrl/Cmd + C。', 'Automatic copying is unavailable. The code is selected: long-press to copy, or use Ctrl/Cmd + C.'],
    done: ['完成', 'Done'], undo: ['撤销', 'Undo'], retry: ['重新加载', 'Try again'],
    publishedHelp: ["展示站点维护者公开的配置。本机草稿不会上传。", "Configurations published by the site owner appear here. Local drafts are never uploaded."],
    draftsHelp: ['仅保存在当前浏览器。导出 JSON 可备份，也可交给站点维护者发布。', 'Stored only in this browser. Export JSON to back up your drafts or give them to the site owner to publish.'],
    publicEmptyTitle: ["第一套配置，等你来整理。", "A place for your configurations."],
    publicEmptyDescription: ["尚未公开配置。可以先添加本机草稿，记录代码和配件。", "No configurations are published yet. Start with a local draft to record a code and its attachments."],
    draftEmptyTitle: ['从一套常用配置开始。', 'Start with a favorite build.'],
    draftEmptyDescription: ["填写配置代码，再选出实际配件。你的草稿只留在这台设备的浏览器里。", "Enter a configuration code and choose its attachments. Your draft stays in the browser on this device."],
    noMatchesTitle: ['没有找到这套配置。', 'No matching loadouts.'], noMatchesDescription: ['试试其他武器名称，或清除模式筛选。', 'Try another weapon name or clear the mode filter.'],
    resetFilters: ['清除筛选', 'Clear filters'], count: ['{count} / {total} 套配置', '{count} / {total} LOADOUTS'],
    catalogCount: ["虚构示例目录 · {weapons} 个基础模型 · {parts} 个配件", "FICTIONAL DEMO CATALOG · {weapons} models · {parts} attachments"],
    selectedCount: ['已选 {count} / 32', '{count} / 32 selected'], pickerCount: ['显示 {count} / {total} 个配件', '{count} / {total} attachments'],
    customWeapon: ['其他 / 暂未收录', 'Other / not in the catalog'], allSlots: ['全部配件', 'All attachments'],
    pendingIcon: ['待补图', 'Icon pending'], removePart: ['移除：{name}', 'Remove: {name}'],
    attachmentCount: ['{count} 个配件', '{count} attachments'], partsHeading: ['装配清单', 'Attachment list'], noAttachments: ['尚未记录配件。', 'No attachments recorded yet.'],
    baseWeapon: ['原型武器图片', 'BASE WEAPON IMAGE'], copy: ['复制配置码', 'Copy code'], copied: ['配置码已复制。', 'Build code copied.'],
    share: ['复制配置链接', 'Copy link'], linkCopied: ['配置链接已复制。', 'Loadout link copied.'],
    copyToDraft: ['复制为本机草稿', 'Make a local draft'], edit: ['编辑', 'Edit'], delete: ['删除草稿', 'Delete draft'],
    saved: ['草稿已保存到当前浏览器。', 'Draft saved in this browser.'], sessionOnly: ['浏览器无法持久保存；配置只留在当前标签页，请及时导出。', 'Browser storage is unavailable. This draft stays in this tab only; export it now to keep it.'],
    deleted: ['草稿已删除。', 'Draft deleted.'], restored: ['草稿已恢复。', 'Draft restored.'],
    imported: ['已导入 {count} 套草稿；重复代码已跳过。', 'Imported {count} drafts; duplicate codes were skipped.'], exported: ['已导出配置 JSON。', 'Loadout JSON exported.'],
    exportEmpty: ['当前没有可以导出的配置。', 'There are no loadouts to export in this collection.'],
    badFile: ['文件无法导入。请使用本站导出的 JSON，最多 200 套配置、文件不超过 1 MB。', 'Cannot import this file. Use JSON exported here, with at most 200 loadouts and a file size under 1 MB.'],
    badData: ['配置数据不完整，请检查名称、武器、模式与配件。', 'Incomplete loadout data. Check the name, weapon, mode, and attachments.'],
    badCode: ['请填写完整配置码（不超过 512 个字符，保持单行）。', 'Enter the full build code on one line, up to 512 characters.'],
    duplicateCode: ['这条配置码已经在本机草稿里了，可直接编辑原配置。', 'This code is already in your local drafts. Edit that draft instead.'],
    tooManyBuilds: ['本机最多保存 200 套配置，请先导出并整理。', 'You can keep up to 200 local drafts. Export and organize them first.'],
    tooManyParts: ['每套配置最多记录 32 个配件。', 'You can record up to 32 attachments per build.'],
    customPartError: ['请填写配件名称，或移除已添加的同名配件。', 'Enter a name, or remove the existing attachment with that name.'],
    loadError: ['暂时无法读取收藏或配件资料。请重新加载，已保存的草稿不会被清除。', 'The collection or attachment catalog could not be loaded. Try again; saved drafts are kept intact.'],
    draftRecovery: ['本机草稿数据无法读取，原始内容已保留。请点击“导出配置”备份原文件，再检查 JSON 后重新导入。新草稿暂时只保存在此标签页。', 'Stored drafts could not be read and were kept intact. Export the original data, check the JSON, and re-import it. New drafts will stay in this tab meanwhile.'],
    recoveredExport: ['原始草稿数据已导出，浏览器中的原文件仍保留。', 'Original draft data exported; the browser copy is still intact.'],
    linkMissing: ['链接对应的配置当前未收录。', 'The linked loadout is not in this collection.'],
    noPartMatches: ["没有找到配件，可以手动记录名称。", "No attachments found. Record the name manually."]
  };
  const $ = selector => document.querySelector(selector);
  const all = selector => [...document.querySelectorAll(selector)];
  const t = (key, values = {}) => {
    let text = messages[key]?.[window.SitePreferences.language === 'en' ? 1 : 0] || key;
    Object.entries(values).forEach(([name, value]) => { text = text.replaceAll(`{${name}}`, String(value)); });
    return text;
  };
  const el = (tag, className = '', text = '') => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  };
  const button = (label, className, action) => {
    const node = el('button', className, label);
    node.type = 'button';
    node.addEventListener('click', action);
    return node;
  };
  const KEY = 'float-space-loadout-drafts-v1';
  const MAX_BUILDS = 200;
  const editor = $('#loadout-editor');
  const form = $('#loadout-form');
  const copyDialog = $('#manual-copy');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let catalog, weapons, parts, slots;
  let published = [], drafts = [], scope = 'published', selectedId = null;
  let editingId = null, selectedParts = [], pickerLimit = 20, editorOpener = null;
  let toastTimer, toastKey = '', toastValues = {}, deletedBuild, detailAnimation;
  let storageBlocked = false, recoveryRaw = null, ready = false, loading = false;

  function translatePage() {
    all('[data-delta-text]').forEach(node => { node.textContent = t(node.dataset.deltaText); });
    for (const attr of ['content', 'alt', 'placeholder']) {
      all(`[data-delta-${attr}]`).forEach(node => node.setAttribute(attr, t(node.getAttribute(`data-delta-${attr}`))));
    }
    all('[data-delta-aria]').forEach(node => node.setAttribute('aria-label', t(node.dataset.deltaAria)));
    if (ready) {
      updateSelectLabels();
      renderCollection();
      renderPreviews();
      if (editor.open) {
        $('#editor-title').textContent = t(editingId ? 'editTitle' : 'editorTitle');
        renderSelected();
        renderPicker();
      }
    }
    if (toastKey && !$('[data-toast]').hidden) $('[data-toast-message]').textContent = t(toastKey, toastValues);
  }

  function showToast(key, values = {}, undo = false) {
    clearTimeout(toastTimer);
    toastKey = key;
    toastValues = values;
    $('[data-toast-message]').textContent = t(key, values);
    $('[data-toast-undo]').hidden = !undo;
    $('[data-toast]').hidden = false;
    // A delete remains undoable until the next delete, even after the toast fades.
    toastTimer = setTimeout(() => { $('[data-toast]').hidden = true; }, undo ? 12000 : 6500);
  }

  function fail(key) { throw new Error(key); }
  function textField(value, limit, required = false) {
    if (typeof value !== 'string') return required ? fail('badData') : '';
    const result = value.trim();
    if (result.length > limit || (required && !result) || /[\u0000-\u0008\u000b\u000c\u000e-\u001f]/.test(result)) fail('badData');
    return result;
  }
  const newId = () => window.crypto?.randomUUID?.() || `draft-${Date.now()}-${Math.random().toString(36).slice(2, 12)}`;

  const recordText = (record, field = 'name') => window.SitePreferences.language === 'en' && record[`${field}En`] ? record[`${field}En`] : record[field];
  let editingTranslations = {};

  function normalizePart(value) {
    if (!value || typeof value !== 'object' || Array.isArray(value)) fail('badData');
    const known = typeof value.id === 'string' ? parts.get(value.id) : null;
    if (known) return {id: known.id, name: known.name, nameEn: textField(value.nameEn, 80), slot: known.slot};
    if (!slots.has(value.slot)) fail('badData');
    return {id: null, name: textField(value.name, 80, true), nameEn: textField(value.nameEn, 80), slot: value.slot};
  }

  function normalizeBuild(value, requireId = false) {
    if (!value || typeof value !== 'object' || Array.isArray(value)) fail('badData');
    const id = value.id || (requireId ? fail('badData') : newId());
    if (typeof id !== 'string' || !/^[a-zA-Z0-9_-]{1,90}$/.test(id)) fail('badData');
    const weapon = typeof value.weaponId === 'string' ? weapons.get(value.weaponId) : null;
    const code = textField(value.code, 512, true);
    if (code.length < 3 || /[\r\n\t]/.test(code)) fail('badCode');
    if (!['operations', 'warfare'].includes(value.mode)) fail('badData');
    if (!Array.isArray(value.attachments) || value.attachments.length > 32) fail('badData');
    const normalizedParts = value.attachments.map(normalizePart);
    if (new Set(normalizedParts.map(partKey)).size !== normalizedParts.length) fail('badData');
    return {
      id, name: textField(value.name, 80, true), nameEn: textField(value.nameEn, 80), weaponId: weapon?.id || null,
      weaponNameEn: textField(value.weaponNameEn, 80), seasonEn: textField(value.seasonEn, 40), notesEn: textField(value.notesEn, 1200),
      weaponName: weapon?.name || textField(value.weaponName, 80, true), mode: value.mode,
      code, season: textField(value.season, 40), notes: textField(value.notes, 1200),
      attachments: normalizedParts,
      updatedAt: typeof value.updatedAt === 'string' && !Number.isNaN(Date.parse(value.updatedAt)) ? value.updatedAt.slice(0, 40) : ''
    };
  }

  function readEnvelope(value, requireId = false) {
    if (!value || value.version !== 1 || !Array.isArray(value.builds) || value.builds.length > MAX_BUILDS) fail('badData');
    const builds = value.builds.map(build => normalizeBuild(build, requireId));
    if (new Set(builds.map(build => build.id)).size !== builds.length || new Set(builds.map(build => build.code)).size !== builds.length) fail('badData');
    return builds;
  }

  function readDrafts() {
    try {
      const raw = localStorage.getItem(KEY);
      if (!raw) return [];
      try {
        if (raw.length > 1_000_000) fail('badData');
        return readEnvelope(JSON.parse(raw));
      } catch {
        recoveryRaw = raw;
        storageBlocked = true;
        return [];
      }
    } catch {
      storageBlocked = true;
      return [];
    }
  }

  function saveDrafts(next) {
    drafts = next;
    if (recoveryRaw !== null) return false;
    try {
      localStorage.setItem(KEY, JSON.stringify({version: 1, builds: next}));
      storageBlocked = false;
      return true;
    } catch {
      storageBlocked = true;
      return false;
    }
  }

  const partKey = part => part.id || `custom:${part.slot}:${part.name}`;
  const slotLabel = id => window.SitePreferences.language === 'en' ? slots.get(id)?.en : slots.get(id)?.name;
  const collection = () => scope === 'draft' ? drafts : published;
  const localIcon = value => {
    if (typeof value !== 'string' || !/^\/assets\/images\/demo\/[a-z0-9-]+\.svg$/.test(value)) return null;
    return `${window.FloatArmory.assetsUrl}${value.slice('/assets/'.length)}`;
  };

  function iconNode(item, pending = 'pendingIcon') {
    const path = localIcon(item?.icon);
    if (!path) return el('span', 'attachment-placeholder', t(pending));
    const img = el('img');
    img.src = path;
    img.alt = '';
    img.loading = 'lazy';
    img.width = 300;
    img.height = 150;
    img.addEventListener('error', () => { img.replaceWith(el('span', 'attachment-placeholder', t(pending))); }, {once: true});
    return img;
  }

  function attachmentTile(record, selectable = false) {
    const known = parts.get(record.id);
    const tile = el(selectable ? 'button' : 'div', `attachment-tile${selectable ? ' attachment-choice' : ''}`);
    if (known?.grade) tile.dataset.grade = String(known.grade);
    tile.append(el('small', '', slotLabel(record.slot)), iconNode(known), el('strong', '', recordText(record)));
    if (selectable) {
      tile.type = 'button';
      tile.dataset.partId = record.id;
      tile.setAttribute('aria-pressed', String(selectedParts.some(part => part.id === record.id)));
      tile.addEventListener('click', () => togglePart(record));
    }
    return tile;
  }

  function renderPreviews() {
    $('[data-icon-preview]').replaceChildren(...catalog.attachments.slice(0, 3).map(part => attachmentTile(part)));
    $('[data-catalog-count]').textContent = t('catalogCount', {weapons: weapons.size, parts: parts.size});
  }

  function matches(build) {
    const query = $('#loadout-search').value.trim().toLocaleLowerCase();
    const mode = $('#loadout-mode').value;
    return (mode === 'all' || build.mode === mode) && (!query || [build.name, build.nameEn, build.weaponName, build.weaponNameEn, build.code, build.notes, build.notesEn, build.season, build.seasonEn, ...build.attachments.flatMap(part => [part.name, part.nameEn])].join(' ').toLocaleLowerCase().includes(query));
  }

  function replaceHash(id) {
    try { history.replaceState(null, '', id ? `#loadout=${encodeURIComponent(id)}` : location.pathname + location.search); } catch { /* Selection still works without history access. */ }
  }

  function renderCollection() {
    const builds = collection();
    const visible = builds.filter(matches);
    if (!visible.some(build => build.id === selectedId)) selectedId = visible[0]?.id || null;
    $('[data-collection-count]').textContent = t('count', {count: visible.length, total: builds.length});
    $('[data-scope-help]').textContent = recoveryRaw !== null ? t('draftRecovery') : storageBlocked && scope === 'draft' ? t('sessionOnly') : t(scope === 'draft' ? 'draftsHelp' : 'publishedHelp');
    all('[data-scope]').forEach(node => node.setAttribute('aria-pressed', String(node.dataset.scope === scope)));
    $('[data-export]').disabled = !builds.length && recoveryRaw === null;
    $('[data-loadouts]').hidden = !visible.length;
    $('[data-empty]').hidden = Boolean(visible.length);
    if (!visible.length) {
      const filtered = builds.length > 0;
      $('[data-empty-title]').textContent = t(filtered ? 'noMatchesTitle' : scope === 'draft' ? 'draftEmptyTitle' : 'publicEmptyTitle');
      $('[data-empty-description]').textContent = t(filtered ? 'noMatchesDescription' : scope === 'draft' ? 'draftEmptyDescription' : 'publicEmptyDescription');
      const action = $('[data-empty-action]');
      action.textContent = t(filtered ? 'resetFilters' : 'newDraft');
      action.onclick = filtered ? resetFilters : () => openEditor();
      $('[data-build-list]').replaceChildren();
      $('[data-build-detail]').replaceChildren();
      return;
    }
    $('[data-build-list]').replaceChildren(...visible.map((build, index) => {
      const card = button('', 'loadout-card', () => {
        selectedId = build.id;
        renderCollection();
        replaceHash(selectedId);
        all('[data-build-id]').find(node => node.dataset.buildId === selectedId)?.focus({preventScroll: true});
        animateDetail();
      });
      card.dataset.buildId = build.id;
      card.setAttribute('aria-pressed', String(build.id === selectedId));
      const top = el('div', 'loadout-card-top');
      top.append(el('span', '', `${String(index + 1).padStart(2, '0')} / ${t(build.mode)}`), el('span', '', `${build.attachments.length} PCS`));
      card.append(top, iconNode(weapons.get(build.weaponId)), el('h3', '', recordText(build)), el('p', '', recordText(build, 'weaponName')));
      return card;
    }));
    renderDetail(visible.find(build => build.id === selectedId));
  }

  function renderDetail(build) {
    const header = el('div', 'loadout-detail-header');
    const title = el('div');
    title.append(el('p', 'micro', recordText(build, 'weaponName')), el('h3', '', recordText(build)));
    const badges = el('div', 'loadout-badges');
    badges.append(el('span', 'loadout-badge', t(build.mode)));
    if (build.season || build.seasonEn) badges.append(el('span', 'loadout-badge', recordText(build, 'season')));
    if (scope === 'draft') badges.append(el('span', 'loadout-badge', t('drafts')));
    header.append(title, badges);
    const weapon = el('figure', 'loadout-weapon');
    const weaponIcon = iconNode(weapons.get(build.weaponId));
    if (weaponIcon.tagName === 'IMG') {
      weaponIcon.loading = 'eager';
      weaponIcon.alt = recordText(build, 'weaponName');
    }
    weapon.append(weaponIcon, el('figcaption', '', t('baseWeapon')));
    const body = el('div', 'loadout-body');
    const code = el('div', 'loadout-code');
    const codeValue = el('div');
    codeValue.append(el('p', 'micro', t('codeLabel')), el('code', '', build.code));
    code.append(codeValue, button(t('copy'), 'button armory-button', () => copyText(build.code, 'copied')));
    const heading = el('div', 'loadout-subheading');
    heading.append(el('h4', '', t('partsHeading')), el('span', 'micro', t('attachmentCount', {count: build.attachments.length})));
    const attachments = el('div', 'attachment-grid');
    attachments.append(...build.attachments.map(part => attachmentTile(part)));
    body.append(code, heading, build.attachments.length ? attachments : el('p', 'loadout-notes', t('noAttachments')));
    if (build.notes || build.notesEn) body.append(el('p', 'loadout-notes', recordText(build, 'notes')));
    const actions = el('div', 'loadout-detail-actions');
    if (scope === 'draft') {
      actions.append(button(t('edit'), 'armory-quiet', () => openEditor(build)), button(t('delete'), 'armory-quiet', () => deleteDraft(build)));
    } else {
      actions.append(button(t('share'), 'armory-quiet', () => copyText(`${window.FloatArmory.pageUrl}#loadout=${encodeURIComponent(build.id)}`, 'linkCopied')), button(t('copyToDraft'), 'armory-quiet', () => openEditor(build, true)));
    }
    body.append(actions);
    $('[data-build-detail]').replaceChildren(header, weapon, body);
  }

  function animateDetail() {
    detailAnimation?.cancel();
    if (reducedMotion.matches || document.hidden) return;
    detailAnimation = $('[data-build-detail]').animate([{opacity: .6, transform: 'translateY(5px)'}, {opacity: 1, transform: 'none'}], {duration: 220, easing: 'cubic-bezier(.22,.75,.25,1)'});
  }

  function resetFilters() {
    $('#loadout-search').value = '';
    $('#loadout-mode').value = 'all';
    renderCollection();
  }

  function updateSelectLabels() {
    const weaponSelect = form.elements.weaponId;
    [...weaponSelect.options].forEach(option => {
      if (option.value === 'custom') option.textContent = t('customWeapon');
      if (option.value === '') option.textContent = t('chooseWeapon');
    });
    for (const selector of ['#attachment-slot', '#custom-attachment-slot']) {
      [...$(selector).options].forEach(option => { option.textContent = option.value === 'all' ? t('allSlots') : slotLabel(option.value); });
    }
  }

  function initializeSelects() {
    const prompt = el('option', '', t('chooseWeapon'));
    prompt.value = '';
    prompt.disabled = true;
    prompt.defaultSelected = true;
    form.elements.weaponId.replaceChildren(prompt, ...catalog.weapons.map(weapon => {
      const option = el('option', '', weapon.name);
      option.value = weapon.id;
      return option;
    }));
    const other = el('option', '', t('customWeapon'));
    other.value = 'custom';
    form.elements.weaponId.append(other);
    const slotOptions = () => catalog.slots.map(slot => {
      const option = el('option', '', slotLabel(slot.id));
      option.value = slot.id;
      return option;
    });
    const any = el('option', '', t('allSlots'));
    any.value = 'all';
    $('#attachment-slot').replaceChildren(any, ...slotOptions());
    $('#custom-attachment-slot').replaceChildren(...slotOptions());
  }

  function updateCustomWeapon() {
    const custom = form.elements.weaponId.value === 'custom';
    $('[data-custom-weapon-field]').hidden = !custom;
    form.elements.weaponName.required = custom;
    form.elements.weaponName.disabled = !custom;
  }

  function openEditor(build = null, copy = false) {
    if (!ready) return;
    editorOpener = document.activeElement;
    form.reset();
    editingId = build && !copy ? build.id : null;
    editingTranslations = Object.fromEntries(['nameEn', 'weaponNameEn', 'seasonEn', 'notesEn'].map(key => [key, build?.[key] || '']));
    selectedParts = build ? build.attachments.map(part => ({...part})) : [];
    $('#editor-title').textContent = t(editingId ? 'editTitle' : 'editorTitle');
    $('[data-editor-error]').textContent = '';
    if (build) {
      ['name', 'mode', 'season', 'code', 'notes', 'weaponName'].forEach(key => { form.elements[key].value = build[key]; });
      form.elements.weaponId.value = weapons.has(build.weaponId) ? build.weaponId : 'custom';
    }
    updateCustomWeapon();
    $('#attachment-slot').value = 'scope';
    $('#attachment-search').value = '';
    $('#custom-attachment-name').value = '';
    $('.armory-custom').open = false;
    pickerLimit = 20;
    renderSelected();
    renderPicker();
    editor.showModal();
    document.body.classList.add('armory-dialog-open');
    $('.armory-editor-body').scrollTop = 0;
    form.elements.name.focus({preventScroll: true});
  }

  function renderSelected() {
    $('[data-selected-count]').textContent = t('selectedCount', {count: selectedParts.length});
    $('[data-selected-attachments]').replaceChildren(...selectedParts.map(part => {
      const chip = button(`${recordText(part)} ×`, '', () => {
        selectedParts = selectedParts.filter(value => partKey(value) !== partKey(part));
        renderSelected();
        updatePickerSelection();
        ($('[data-selected-attachments] button') || $('#attachment-search')).focus({preventScroll: true});
      });
      chip.setAttribute('aria-label', t('removePart', {name: recordText(part)}));
      return chip;
    }));
  }

  function updatePickerSelection() {
    all('[data-part-id]').forEach(node => node.setAttribute('aria-pressed', String(selectedParts.some(part => part.id === node.dataset.partId))));
  }

  function togglePart(part) {
    const exists = selectedParts.some(value => value.id === part.id);
    if (!exists && selectedParts.length >= 32) {
      $('[data-editor-error]').textContent = t('tooManyParts');
      return;
    }
    selectedParts = exists ? selectedParts.filter(value => value.id !== part.id) : [...selectedParts, {id: part.id, name: part.name, slot: part.slot}];
    $('[data-editor-error]').textContent = '';
    renderSelected();
    updatePickerSelection();
  }

  function renderPicker() {
    const category = $('#attachment-slot').value;
    const query = $('#attachment-search').value.trim().toLocaleLowerCase();
    const filtered = catalog.attachments.filter(part => (category === 'all' || part.slot === category) && (!query || part.name.toLocaleLowerCase().includes(query)));
    const visible = filtered.slice(0, pickerLimit);
    $('[data-attachment-picker]').replaceChildren(...visible.map(part => attachmentTile(part, true)));
    $('[data-picker-count]').textContent = filtered.length ? t('pickerCount', {count: visible.length, total: filtered.length}) : t('noPartMatches');
    $('[data-more-attachments]').hidden = pickerLimit >= filtered.length;
  }

  function addCustomPart() {
    const name = $('#custom-attachment-name').value.trim();
    const slot = $('#custom-attachment-slot').value;
    if (!name || name.length > 80 || selectedParts.some(part => part.name === name && part.slot === slot)) {
      $('[data-editor-error]').textContent = t('customPartError');
      $('#custom-attachment-name').focus();
      return;
    }
    if (selectedParts.length >= 32) {
      $('[data-editor-error]').textContent = t('tooManyParts');
      return;
    }
    selectedParts.push({id: null, name, slot});
    $('#custom-attachment-name').value = '';
    $('[data-editor-error]').textContent = '';
    renderSelected();
  }

  function saveForm(event) {
    event.preventDefault();
    try {
      if (!editingId && drafts.length >= MAX_BUILDS) fail('tooManyBuilds');
      const build = normalizeBuild({
        ...editingTranslations,
        id: editingId || newId(), name: form.elements.name.value, weaponId: form.elements.weaponId.value,
        weaponName: form.elements.weaponName.value, mode: form.elements.mode.value,
        season: form.elements.season.value, code: form.elements.code.value, notes: form.elements.notes.value,
        attachments: selectedParts, updatedAt: new Date().toISOString()
      });
      if (drafts.some(value => value.code === build.code && value.id !== editingId)) fail('duplicateCode');
      const next = drafts.some(value => value.id === editingId) ? drafts.map(value => value.id === editingId ? build : value) : [...drafts, build];
      const stored = saveDrafts(next);
      scope = 'draft';
      selectedId = build.id;
      resetFilters();
      replaceHash(build.id);
      editor.close();
      showToast(stored ? 'saved' : 'sessionOnly');
    } catch (error) {
      $('[data-editor-error]').textContent = t(messages[error.message] ? error.message : 'badData');
    }
  }

  function deleteDraft(build) {
    deletedBuild = {build, index: drafts.findIndex(value => value.id === build.id)};
    saveDrafts(drafts.filter(value => value.id !== build.id));
    renderCollection();
    replaceHash(selectedId);
    all('[data-build-id]').find(node => node.dataset.buildId === selectedId)?.focus({preventScroll: true});
    if (!selectedId) $('[data-empty-action]').focus({preventScroll: true});
    showToast('deleted', {}, true);
  }

  function undoDelete() {
    if (!deletedBuild || drafts.length >= MAX_BUILDS || drafts.some(value => value.code === deletedBuild.build.code)) return;
    const next = [...drafts];
    next.splice(deletedBuild.index, 0, deletedBuild.build);
    selectedId = deletedBuild.build.id;
    deletedBuild = null;
    const stored = saveDrafts(next);
    scope = 'draft';
    resetFilters();
    replaceHash(selectedId);
    showToast(stored ? 'restored' : 'sessionOnly');
  }

  async function copyText(value, successKey) {
    try {
      if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
      await navigator.clipboard.writeText(value);
      showToast(successKey);
      return;
    } catch { /* A visible manual-copy fallback follows. */ }
    const field = el('textarea');
    field.value = value;
    field.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;opacity:0;';
    field.setAttribute('aria-label', t('codeLabel'));
    document.body.append(field);
    const previouslyFocused = document.activeElement;
    field.focus();
    field.select();
    let copied = false;
    try { copied = document.execCommand('copy'); } catch { /* Use the dialog. */ }
    field.remove();
    previouslyFocused?.focus({preventScroll: true});
    if (copied) return showToast(successKey);
    $('#manual-copy-value').value = value;
    copyDialog.showModal();
    document.body.classList.add('armory-dialog-open');
    $('#manual-copy-value').focus();
    $('#manual-copy-value').select();
  }

  function download(content, filename) {
    const url = URL.createObjectURL(new Blob([content], {type: 'application/json;charset=utf-8'}));
    const link = el('a');
    link.href = url;
    link.download = filename;
    document.body.append(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  function exportBuilds() {
    if (recoveryRaw !== null) {
      download(recoveryRaw, 'delta-drafts-recovery.json');
      return showToast('recoveredExport');
    }
    if (!collection().length) return showToast('exportEmpty');
    download(JSON.stringify({version: 1, builds: collection()}, null, 2) + '\n', `float-space-loadouts-${scope}-${new Date().toISOString().slice(0, 10)}.json`);
    showToast('exported');
  }

  async function importFile(file) {
    if (!file) return;
    try {
      if (file.size > 1_000_000) fail('badFile');
      const imported = readEnvelope(JSON.parse((await file.text()).replace(/^\uFEFF/, '')));
      const codes = new Set(drafts.map(build => build.code));
      const fresh = imported.filter(build => !codes.has(build.code)).map(build => ({...build, id: newId()}));
      if (drafts.length + fresh.length > MAX_BUILDS) fail('tooManyBuilds');
      if (recoveryRaw !== null) {
        // Preserve the unreadable original separately before replacing it with
        // the user's explicitly imported, validated backup.
        try {
          localStorage.setItem(`${KEY}-recovery-${Date.now()}`, recoveryRaw);
          recoveryRaw = null;
        } catch { /* The current tab can still hold the validated import. */ }
      }
      const stored = saveDrafts([...drafts, ...fresh]);
      scope = 'draft';
      selectedId = fresh[0]?.id || selectedId;
      resetFilters();
      replaceHash(selectedId);
      showToast(stored ? 'imported' : 'sessionOnly', {count: fresh.length});
    } catch (error) {
      showToast(error.message === 'tooManyBuilds' ? 'tooManyBuilds' : 'badFile');
    } finally {
      $('[data-import-file]').value = '';
    }
  }

  function selectFromHash(announce = true) {
    const id = new URLSearchParams(location.hash.slice(1)).get('loadout');
    if (!id) return;
    const publicBuild = published.find(build => build.id === id);
    const draft = drafts.find(build => build.id === id);
    if (!publicBuild && !draft) {
      if (announce) showToast('linkMissing');
      return;
    }
    scope = publicBuild ? 'published' : 'draft';
    selectedId = id;
    resetFilters();
  }

  async function fetchJSON(path) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 12000);
    try {
      const response = await fetch(path, {credentials: 'omit', signal: controller.signal});
      if (!response.ok) throw new Error('loadError');
      return await response.json();
    } finally { clearTimeout(timeout); }
  }

  async function initialize() {
    if (loading) return;
    loading = true;
    $('[data-load-error]').hidden = true;
    $('[data-loading]').hidden = false;
    try {
      const [data, content] = await Promise.all([
        fetchJSON(window.FloatArmory.catalogUrl),
        window.FloatArmory.loadoutsUrl ? fetchJSON(window.FloatArmory.loadoutsUrl) : Promise.resolve({version: 1, builds: []})
      ]);
      if (data.version !== 1 || !Array.isArray(data.weapons) || !Array.isArray(data.attachments) || !Array.isArray(data.slots)) throw new Error('loadError');
      catalog = data;
      weapons = new Map(catalog.weapons.map(weapon => [weapon.id, weapon]));
      parts = new Map(catalog.attachments.map(part => [part.id, part]));
      slots = new Map(catalog.slots.map(slot => [slot.id, slot]));
      published = readEnvelope(content, true);
      drafts = readDrafts();
      ready = true;
      document.body.classList.add('armory-ready');
      initializeSelects();
      renderCollection();
      renderPreviews();
      selectFromHash();
    } catch {
      $('[data-load-error-message]').textContent = t('loadError');
      $('[data-load-error]').hidden = false;
    } finally {
      loading = false;
      $('[data-loading]').hidden = true;
    }
  }

  all('[data-new-build]').forEach(node => node.addEventListener('click', () => openEditor()));
  all('[data-close-editor]').forEach(node => node.addEventListener('click', () => editor.close()));
  all('[data-close-copy]').forEach(node => node.addEventListener('click', () => copyDialog.close()));
  [editor, copyDialog].forEach(dialog => dialog.addEventListener('close', () => {
    if (!editor.open && !copyDialog.open) document.body.classList.remove('armory-dialog-open');
    if (dialog === editor && !editorOpener?.isConnected) {
      all('[data-build-id]').find(node => node.dataset.buildId === selectedId)?.focus({preventScroll: true});
    }
  }));
  [editor, copyDialog].forEach(dialog => dialog.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const focusable = [...dialog.querySelectorAll('button, input, select, textarea, a[href], summary, [tabindex]')]
      .filter(node => !node.disabled && node.tabIndex >= 0 && node.getClientRects().length);
    const first = focusable[0], last = focusable.at(-1);
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }));
  form.addEventListener('submit', saveForm);
  form.elements.weaponId.addEventListener('change', updateCustomWeapon);
  $('#loadout-search').addEventListener('input', () => { if (ready) renderCollection(); });
  $('#loadout-mode').addEventListener('change', () => { if (ready) renderCollection(); });
  all('[data-scope]').forEach(node => node.addEventListener('click', () => {
    scope = node.dataset.scope;
    resetFilters();
    replaceHash(selectedId);
  }));
  for (const [selector, event] of [['#attachment-slot', 'change'], ['#attachment-search', 'input']]) {
    $(selector).addEventListener(event, () => { pickerLimit = 20; renderPicker(); });
  }
  $('[data-more-attachments]').addEventListener('click', () => { pickerLimit += 20; renderPicker(); });
  $('[data-add-custom]').addEventListener('click', addCustomPart);
  $('#custom-attachment-name').addEventListener('keydown', event => {
    if (event.key === 'Enter') { event.preventDefault(); addCustomPart(); }
  });
  $('[data-import]').addEventListener('click', () => $('[data-import-file]').click());
  $('[data-import-file]').addEventListener('change', event => importFile(event.target.files[0]));
  $('[data-export]').addEventListener('click', exportBuilds);
  $('[data-toast-undo]').addEventListener('click', undoDelete);
  $('[data-retry]').addEventListener('click', initialize);
  window.addEventListener('hashchange', () => { if (ready) selectFromHash(); });
  window.addEventListener('storage', event => {
    if (event.key !== KEY || !ready || recoveryRaw !== null) return;
    try {
      drafts = event.newValue ? readEnvelope(JSON.parse(event.newValue)) : [];
      renderCollection();
    } catch { /* Keep this tab's valid drafts and leave the changed source intact. */ }
  });
  document.addEventListener('site:languagechange', translatePage);
  reducedMotion.addEventListener('change', () => { if (reducedMotion.matches) detailAnimation?.cancel(); });
  document.addEventListener('visibilitychange', () => { if (document.hidden) detailAnimation?.cancel(); });
  translatePage();
  initialize();
})();
