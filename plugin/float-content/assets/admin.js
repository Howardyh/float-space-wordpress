'use strict';
(() => {
  const root = document.getElementById('float-parts-editor');
  const hidden = document.getElementById('float_attachments');
  const catalog = window.FloatContentCatalog;
  if (!root || !hidden || !catalog) return;
  const get = selector => root.querySelector(selector);
  const parts = new Map(catalog.attachments.map(part => [part.id, part]));
  const slots = new Map(catalog.slots.map(slot => [slot.id, slot]));
  let selected;
  try { selected = JSON.parse(hidden.value); } catch { selected = []; }
  if (!Array.isArray(selected)) selected = [];
  const key = part => part.id || `${part.slot}:${part.name}`;
  const message = text => { get('[data-float-error]').textContent = text; };
  function render() {
    hidden.value = JSON.stringify(selected);
    const list = get('[data-float-parts]');
    list.replaceChildren();
    selected.forEach((part, index) => {
      const row = document.createElement('p');
      const text = document.createElement('span');
      text.textContent = `${slots.get(part.slot)?.name || part.slot} · ${part.name} `;
      const label = document.createElement('label');
      label.append('英文名称 ');
      const english = document.createElement('input');
      english.type = 'text'; english.maxLength = 80; english.value = part.nameEn || '';
      english.addEventListener('input', () => { part.nameEn = english.value; hidden.value = JSON.stringify(selected); });
      label.append(english);
      const remove = document.createElement('button');
      remove.type = 'button'; remove.className = 'button'; remove.textContent = '移除';
      remove.setAttribute('aria-label', `移除 ${part.name}`);
      remove.addEventListener('click', () => { selected.splice(index, 1); render(); message(''); });
      row.append(text, label, ' ', remove); list.append(row);
    });
    if (!selected.length) { const empty = document.createElement('p'); empty.textContent = '尚未选择配件。'; list.append(empty); }
  }
  function picker() {
    const list = get('[data-float-part]'); const slot = get('[data-float-slot]').value;
    const query = get('[data-float-search]').value.trim().toLocaleLowerCase();
    list.replaceChildren();
    catalog.attachments.filter(part => part.slot === slot && (!query || part.name.toLocaleLowerCase().includes(query))).forEach(part => {
      const option = document.createElement('option'); option.value = part.id; option.textContent = part.name; list.append(option);
    });
    get('[data-float-add]').disabled = !list.options.length;
  }
  function add(part) {
    if (selected.length >= 32) return message('每套配置最多记录 32 个配件。');
    if (selected.some(current => key(current) === key(part))) return message('此配件已经在装配清单中。');
    selected.push(part); render(); message('');
  }
  get('[data-float-slot]').addEventListener('change', picker);
  get('[data-float-search]').addEventListener('input', picker);
  get('[data-float-add]').addEventListener('click', () => {
    const part = parts.get(get('[data-float-part]').value);
    if (part) add({id: part.id, name: part.name, slot: part.slot, nameEn: ''});
  });
  get('[data-float-add-custom]').addEventListener('click', () => {
    const name = get('[data-float-custom]').value.trim();
    if (!name) return message('请填写未收录配件的名称。');
    add({id: null, name, slot: get('[data-float-slot]').value, nameEn: ''});
    get('[data-float-custom]').value = '';
  });
  render(); picker();
})();
