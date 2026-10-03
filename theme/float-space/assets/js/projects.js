'use strict';

// Public interface concepts use fixed fictional samples. No network requests.
const translate = text => window.SitePreferences.translate(text);
const demoChanged = (demo, kind, direction = 1) => demo.dispatchEvent(new CustomEvent('site:demochange', { bubbles: true, detail: { kind, direction } }));
const sampleDays = [
  { day: 17, weekday: 'THURSDAY', courses: [
    { time: '08:00 — 09:35', name: 'English', detail: 'Room B · 201. A fictional lesson about reading and expression.' },
    { time: '14:00 — 15:35', name: 'Linear algebra', detail: 'Room A · 406. A fictional lesson about matrices.' }
  ] },
  { day: 18, weekday: 'FRIDAY', courses: [
    { time: '08:00 — 09:35', name: 'Mathematics', detail: 'Room A · 302. A fictional lesson about calculus.' },
    { time: '10:00 — 11:35', name: 'Programming', detail: 'Lab · 205. A fictional lesson about writing a first program.' }
  ] },
  { day: 19, weekday: 'SATURDAY', courses: [] }
];

document.querySelectorAll('[data-schedule-demo]').forEach(demo => {
  let currentDay = 1;
  const list = demo.querySelector('[data-course-list]');
  const previous = demo.querySelector('[data-day-prev]');
  const next = demo.querySelector('[data-day-next]');

  function renderDay(preserveOpen = false) {
    const day = sampleDays[currentDay];
    const expanded = preserveOpen ? [...list.querySelectorAll('details')].map(detail => detail.dataset.motionOpen ? detail.dataset.motionOpen === 'true' : detail.open) : [];
    window.SitePreferences.setText(demo.querySelector('[data-weekday]'), day.weekday);
    const year = document.createElement('span');
    const chinese = window.SitePreferences.language === 'zh-CN';
    year.textContent = chinese ? '，2026' : ', 2026';
    demo.querySelector('[data-date]').replaceChildren(chinese ? `9 月 ${day.day} 日` : `Sep ${day.day}`, year);
    const count = demo.querySelector('[data-course-count]');
    delete count.dataset.i18n;
    count.textContent = chinese ? `${day.courses.length} 节课` : `${day.courses.length} classes`;
    previous.disabled = currentDay === 0;
    next.disabled = currentDay === sampleDays.length - 1;
    demo.querySelector('.calendar-days').style.setProperty('--day-index', String(currentDay));
    demo.querySelectorAll('[data-day]').forEach(button => {
      button.setAttribute('aria-pressed', String(Number(button.dataset.day) === currentDay));
    });
    list.replaceChildren();

    if (!day.courses.length) {
      const empty = document.createElement('div');
      empty.className = 'empty-day';
      const title = document.createElement('strong');
      title.textContent = translate('Nothing scheduled.');
      const text = document.createElement('span');
      text.textContent = translate('A little room for curiosity.');
      empty.append(title, text);
      list.append(empty);
      return;
    }

    day.courses.forEach((course, index) => {
      const detail = document.createElement('details');
      detail.className = 'course';
      const summary = document.createElement('summary');
      const copy = document.createElement('span');
      const time = document.createElement('small');
      time.textContent = course.time;
      const name = document.createElement('strong');
      name.textContent = translate(course.name);
      const plus = document.createElement('span');
      plus.className = 'course-plus';
      plus.setAttribute('aria-hidden', 'true');
      plus.textContent = '+';
      copy.append(time, name);
      summary.append(copy, plus);
      const description = document.createElement('p');
      description.textContent = translate(course.detail);
      detail.append(summary, description);
      detail.open = Boolean(expanded[index]);
      list.append(detail);
    });
  }

  function changeDay(day) {
    if (day === currentDay) return;
    const direction = day < currentDay ? -1 : 1;
    currentDay = day;
    renderDay();
    demoChanged(demo, 'schedule', direction);
  }
  previous.addEventListener('click', () => changeDay(Math.max(0, currentDay - 1)));
  next.addEventListener('click', () => changeDay(Math.min(sampleDays.length - 1, currentDay + 1)));
  demo.querySelectorAll('[data-day]').forEach(button => {
    button.addEventListener('click', () => changeDay(Number(button.dataset.day)));
  });
  demo.querySelector('[data-day-reset]').addEventListener('click', () => {
    const direction = currentDay > 1 ? -1 : 1;
    currentDay = 1;
    renderDay();
    demoChanged(demo, 'schedule', direction);
  });
  document.addEventListener('site:languagechange', () => renderDay(true));
  renderDay();
});

const metricSamples = [
  { cpu: 28, ram: 42, disk: 61, rx: '2.4 MB/s', tx: '0.8 MB/s' },
  { cpu: 46, ram: 44, disk: 61, rx: '3.1 MB/s', tx: '1.2 MB/s' },
  { cpu: 19, ram: 39, disk: 61, rx: '1.6 MB/s', tx: '0.5 MB/s' }
];

document.querySelectorAll('[data-monitor-demo]').forEach(demo => {
  let sampleIndex = 0;
  const tabs = [...demo.querySelectorAll('[data-monitor-tab]')];
  const panels = [...demo.querySelectorAll('[role="tabpanel"]')];
  function renderSampleLabel() {
    const label = demo.querySelector('[data-sample-index]');
    delete label.dataset.i18n;
    label.textContent = `${window.SitePreferences.language === 'zh-CN' ? '示例' : 'Sample'} 0${sampleIndex + 1} / 03`;
  }
  document.addEventListener('site:languagechange', renderSampleLabel);
  renderSampleLabel();

  function selectTab(index, focus = false) {
    const changed = tabs[index].getAttribute('aria-selected') !== 'true';
    tabs.forEach((tab, i) => {
      tab.setAttribute('aria-selected', String(i === index));
      tab.tabIndex = i === index ? 0 : -1;
      panels[i].hidden = i !== index;
    });
    if (focus) tabs[index].focus();
    if (changed) demoChanged(demo, 'tab');
  }

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => selectTab(index));
    tab.addEventListener('keydown', event => {
      let target;
      if (event.key === 'ArrowRight') target = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft') target = (index + tabs.length - 1) % tabs.length;
      if (event.key === 'Home') target = 0;
      if (event.key === 'End') target = tabs.length - 1;
      if (target === undefined) return;
      event.preventDefault();
      selectTab(target, true);
    });
  });

  demo.querySelector('[data-next-sample]').addEventListener('click', () => {
    sampleIndex = (sampleIndex + 1) % metricSamples.length;
    const sample = metricSamples[sampleIndex];
    demo.querySelectorAll('[data-metric]').forEach(element => {
      const suffix = document.createElement('span');
      suffix.textContent = '%';
      element.replaceChildren(String(sample[element.dataset.metric]), suffix);
    });
    demo.querySelectorAll('[data-fill]').forEach(element => { element.style.width = `${sample[element.dataset.fill]}%`; });
    demo.querySelector('[data-performance-cpu]').textContent = `${sample.cpu}%`;
    demo.querySelector('[data-network-rx]').textContent = sample.rx;
    demo.querySelector('[data-network-tx]').textContent = sample.tx;
    renderSampleLabel();
    demo.querySelectorAll('.sample-chart > i').forEach((bar, i) => {
      bar.style.height = `${Math.max(12, Math.min(85, sample.cpu + [0, 11, -7, 17, 4, 8][i % 6]))}%`;
    });
    demoChanged(demo, 'sample');
  });
});
