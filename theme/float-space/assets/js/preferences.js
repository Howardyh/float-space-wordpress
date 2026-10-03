'use strict';

// Runs in the head so the saved theme is applied before the stylesheet paints.
(() => {
  const messages = {"t1d1833565142":{"en":"(opens in a new tab)","zh-CN":"（在新标签页打开）"},"t516305e6efcb":{"en":"01 / OVERVIEW","zh-CN":"01 / 项目概览"},"ta1515e4a121e":{"zh-CN":"持续探索","en":"ONGOING EXPERIMENTS"},"t6ea1587a6f87":{"en":"02 CLASSES","zh-CN":"02 节课"},"t5052a8e03ad3":{"en":"2 classes","zh-CN":"2 节课"},"t31e1b4ccf173":{"en":"A NOTEBOOK, IN THE MAKING.","zh-CN":"一本正在准备的笔记。"},"t0fa8ca5ff109":{"en":"A SMALL CORNER OF THE INTERNET.","zh-CN":"互联网里的一小块空间。"},"tf6cdd15dc7d5":{"en":"A SMALL HISTORY. A QUICK GLANCE.","zh-CN":"一小段历史，一眼便知。"},"t848502bcd03b":{"en":"A small terminal introduction","zh-CN":"一小段终端式自我介绍"},"t72b70ae3032d":{"en":"ABOUT","zh-CN":"关于我"},"t4efca0d10c5f":{"en":"About","zh-CN":"关于"},"t48a6e01d3e0d":{"en":"CPU / FICTIONAL SAMPLE","zh-CN":"CPU / 虚构示例"},"ta8d93ffb16e3":{"en":"CPU HISTORY / SAMPLE","zh-CN":"CPU 历史 / 示例"},"t72ec80986277":{"en":"CURRENT PROJECT","zh-CN":"正在做的项目"},"tefb0f82d885b":{"en":"Change the sample day and open a lesson. Saturday shows the empty-day state.","zh-CN":"切换示例日期，展开一节课程。周六展示没有课程时的状态。"},"t4f7d64017689":{"en":"Coming soon","zh-CN":"正在准备"},"t9e3090de7470":{"en":"Coming soon.","zh-CN":"正在准备。"},"t8267b044f191":{"en":"DISK","zh-CN":"磁盘"},"t340e0cf3bfa8":{"en":"Disk","zh-CN":"磁盘"},"t4f32763629e3":{"en":"Explore Project","zh-CN":"了解项目"},"t95e83f6af248":{"en":"Explore Projects","zh-CN":"探索项目"},"t8633f988c53e":{"en":"Explore the projects","zh-CN":"看看这些项目"},"td992f7d647ec":{"en":"FRI","zh-CN":"周五"},"tbf9d9bf7fc75":{"en":"FRIDAY","zh-CN":"星期五"},"t325e84939c64":{"en":"Fallback","zh-CN":"后备连接"},"t624542a30c04":{"en":"First build → rethink → rebuild.","zh-CN":"开始尝试 → 重新思考 → 再次构建。"},"td96e859e4028":{"zh-CN":"原创设备插画 · 屏幕内容为虚构示例","en":"Original device illustration · fictional screen"},"t76363a64dcfa":{"en":"Footer navigation","zh-CN":"页脚导航"},"t9004633d6ca4":{"en":"For now, the projects tell the story.","zh-CN":"先让这些项目，讲讲这里的故事。"},"t3801bbeb457c":{"en":"Hardware and illustrative interface","zh-CN":"硬件与界面示意"},"t8d6117950fdd":{"en":"IDEAS → SOMETHING REAL","zh-CN":"想法 → 做出来"},"ta5581af0a18f":{"en":"Illustrative CPU history, fictional sample values","zh-CN":"CPU 历史示意图，数值为虚构示例"},"t9064f7baa04d":{"en":"Illustrative web interface with fictional data. Actual firmware UI may differ.","zh-CN":"网页界面为示意，数据均为虚构。实际固件界面可能不同。"},"tbe4e41df43db":{"en":"Interactive concept · fictional lessons","zh-CN":"交互概念演示 · 虚构课程"},"t8a59409d7630":{"en":"Interface concept · fictional sample values","zh-CN":"界面概念演示 · 虚构数值"},"t82633d5e145d":{"en":"Lab · 205","zh-CN":"实验室 · 205"},"tbb9183849cb1":{"en":"Lab · 205. A fictional lesson about writing a first program.","zh-CN":"实验室 · 205。虚构示例课程：编写第一个程序。"},"teb355944b92d":{"en":"Main navigation","zh-CN":"主导航"},"tcdba6dc33e22":{"en":"Mathematics","zh-CN":"数学"},"tc3963aedaac6":{"en":"Memory","zh-CN":"内存"},"t99af6606ff9d":{"en":"Menu","zh-CN":"菜单"},"tf41260ab1fd6":{"en":"Monitor sample pages","zh-CN":"监控示例视图"},"t7bfab6c61f77":{"en":"NOTES","zh-CN":"笔记"},"t1744b96470b5":{"en":"Network","zh-CN":"网络"},"t88704df33c7b":{"en":"Next sample day","zh-CN":"下一个示例日期"},"ta01b7858b534":{"en":"Next sample →","zh-CN":"下一组示例 →"},"t8a7525b1492f":{"en":"Notes","zh-CN":"笔记"},"t5c3ce8d496e2":{"en":"OFFLINE","zh-CN":"离线"},"ta9a212035c80":{"zh-CN":"从构想，到实践。","en":"FROM IDEA TO PRACTICE."},"t709276c8392f":{"en":"OVERVIEW","zh-CN":"概览"},"td4b1ea5708dd":{"en":"Overview","zh-CN":"概览"},"ted3378998d31":{"en":"DEMO DEVICE / MONITOR","zh-CN":"DEMO DEVICE / 监控"},"t1d641f1d3100":{"en":"DEMO DEVICE / SCHEDULE","zh-CN":"DEMO DEVICE / 课表"},"t1a746087b6e0":{"zh-CN":"项目选集","en":"SELECTED WORK"},"t442aded87a55":{"en":"Performance","zh-CN":"性能"},"t57914e1b9ad5":{"en":"Previous sample day","zh-CN":"上一个示例日期"},"te6aba2bd5a6b":{"en":"Programming","zh-CN":"编程"},"t1598fee022ac":{"en":"Project technologies","zh-CN":"项目技术栈"},"t04e2a9728af7":{"en":"Projects","zh-CN":"项目"},"tbac9d15ad9f1":{"en":"Receive","zh-CN":"接收"},"t0840cdb50da6":{"en":"Reset day ↺","zh-CN":"重置日期 ↺"},"tbf8eba2c6366":{"en":"Room A · 302","zh-CN":"A 楼 · 302 教室"},"t224066476f3d":{"en":"Room A · 302. A fictional lesson about calculus.","zh-CN":"A 楼 · 302 教室。虚构示例课程：微积分。"},"t27c57438d2cc":{"zh-CN":"每个项目，一个新问题。","en":"EVERY PROJECT, A NEW QUESTION."},"teb13583bcda6":{"en":"SAMPLE DATA","zh-CN":"示例数据"},"tf541ab1317c9":{"en":"SAT","zh-CN":"周六"},"t1dfe446d312e":{"en":"SEP 18 / SAMPLE","zh-CN":"9 月 18 日 / 示例"},"t4e24f12de769":{"en":"SIGNAL → STATE → SCREEN","zh-CN":"信号 → 状态 → 屏幕"},"tc6573ec96f1a":{"en":"STACK","zh-CN":"技术栈"},"t8c2e4a035f5f":{"en":"STATUS","zh-CN":"状态"},"td2829985b90d":{"en":"Sample 01 / 03","zh-CN":"示例 01 / 03"},"t4995fce6c17d":{"en":"Sample dates","zh-CN":"示例日期"},"t291873e31ccb":{"en":"Scroll","zh-CN":"向下探索"},"t747132704a73":{"en":"Selected projects","zh-CN":"精选项目"},"t11534228bde2":{"en":"Sep 18","zh-CN":"9 月 18 日"},"tac576a66d456":{"en":"Skip to content","zh-CN":"跳转到正文"},"t453e754427ce":{"en":"Switch between three concept views, or step through the fictional metric samples.","zh-CN":"切换三种概念视图，或逐组查看虚构的指标示例。"},"te62f1af47505":{"en":"THE NEXT EXPERIMENT","zh-CN":"下一个实验"},"t74d1052f85c3":{"en":"THU","zh-CN":"周四"},"tef6e30a6ce20":{"en":"Things I've built, rebuilt,","zh-CN":"动手做过，推倒重来，"},"t05b4a73a3dae":{"en":"Things I've built, rebuilt, and kept improving.","zh-CN":"动手做过，推倒重来，还在继续改进的东西。"},"t55df6b1aa5e6":{"en":"Things worth","zh-CN":"值得"},"ta5bff3dc13b7":{"en":"Thoughts, experiments and","zh-CN":"想法、实验，"},"t609923e5de56":{"en":"Thoughts, experiments and things worth writing down.","zh-CN":"想法、实验，和那些值得记下来的东西。"},"t43b981c9c6d5":{"en":"Transmit","zh-CN":"发送"},"taaead4abf5d0":{"en":"Transport","zh-CN":"传输协议"},"t9d208c723c21":{"zh-CN":"在这里动手、学习，再重新开始。","en":"A place to build, learn, and start again."},"td0290f531641":{"en":"View Project","zh-CN":"查看项目"},"t75e96614e5a8":{"en":"View source on GitHub","zh-CN":"在 GitHub 查看源码"},"t9cce2c39dbb6":{"en":"WSS / SAMPLE","zh-CN":"WSS / 示例"},"t940dba58d1bf":{"en":"YEAR","zh-CN":"年份"},"t9a82eb6c1833":{"en":"and kept improving.","zh-CN":"也一直在改进。"},"t2577c0f557b2":{"en":"projects","zh-CN":"项目"},"tc84cb75097c5":{"en":"things worth writing down.","zh-CN":"和值得记下来的东西。"},"t47fad0f097ea":{"en":"writing down","zh-CN":"记下来的东西"},"t027351de5625":{"en":"← All projects","zh-CN":"← 全部项目"},"t88854c40fd5f":{"en":"THURSDAY","zh-CN":"星期四"},"taf9e96e49814":{"en":"SATURDAY","zh-CN":"星期六"},"tba118bf7fc9c":{"en":"English","zh-CN":"英语"},"t2e0b924981b4":{"en":"Linear algebra","zh-CN":"线性代数"},"t99095cceccc2":{"en":"Room B · 201. A fictional lesson about reading and expression.","zh-CN":"B 楼 · 201 教室。虚构示例课程：阅读与表达。"},"tfb393bff7ad0":{"en":"Room A · 406. A fictional lesson about matrices.","zh-CN":"A 楼 · 406 教室。虚构示例课程：矩阵。"},"t81cce4c399d2":{"en":"Nothing scheduled.","zh-CN":"这一天没有课程。"},"td08c5555f17b":{"en":"A little room for curiosity.","zh-CN":"给好奇心，留点空白。"},"te356c5117ce1":{"en":"Display preferences","zh-CN":"语言与外观设置"},"t7d9eb7acb13e":{"en":"Close","zh-CN":"关闭"}};
  const keyByEnglish = new Map(Object.entries(messages).map(([key, value]) => [value.en, key]));
  const root = document.documentElement;
  const read = key => { try { return localStorage.getItem(key); } catch { return null; } };
  const save = (key, value) => { try { localStorage.setItem(key, value); } catch { /* Preferences still work in this tab. */ } };
  const browserLanguage = navigator.language.toLowerCase().startsWith('zh') ? 'zh-CN' : 'en';
  const storedLanguage = read('float-space-language');
  let language = ['zh-CN', 'en'].includes(storedLanguage) ? storedLanguage : browserLanguage;
  let theme = read('float-space-theme') === 'light' ? 'light' : 'dark';
  root.dataset.theme = theme;
  root.classList.add('js');

  function translate(text) {
    const key = keyByEnglish.get(text);
    return key ? messages[key][language] : text;
  }

  function setText(element, english) {
    const key = keyByEnglish.get(english);
    if (key) element.dataset.i18n = key;
    else delete element.dataset.i18n;
    element.textContent = translate(english);
  }

  function updateControls() {
    const languageButton = document.querySelector('[data-language-toggle]');
    const themeButton = document.querySelector('[data-theme-toggle]');
    const chinese = language === 'zh-CN';
    if (languageButton) {
      const label = languageButton.querySelector('[data-language-label]');
      label.textContent = chinese ? 'English' : '中文';
      label.lang = chinese ? 'en' : 'zh-CN';
      languageButton.setAttribute('aria-label', chinese ? '切换到英文' : 'Switch to Simplified Chinese');
      languageButton.title = chinese ? '切换到英文' : 'Switch to Simplified Chinese';
    }
    if (themeButton) {
      const label = themeButton.querySelector('[data-theme-label]');
      label.textContent = theme === 'dark' ? (chinese ? '日间' : 'Light') : (chinese ? '夜间' : 'Dark');
      themeButton.setAttribute('aria-label', theme === 'dark' ? (chinese ? '切换到日间模式' : 'Switch to light mode') : (chinese ? '切换到夜间模式' : 'Switch to dark mode'));
      // aria-pressed always describes the same choice: light mode enabled.
      themeButton.setAttribute('aria-pressed', String(theme === 'light'));
      themeButton.title = themeButton.getAttribute('aria-label');
    }
  }

  function applyLanguage(announce = false) {
    document.querySelectorAll('[data-i18n]').forEach(element => {
      const message = messages[element.dataset.i18n];
      if (message) element.textContent = message[language];
    });
    for (const attr of ['aria-label', 'alt', 'title', 'content']) {
      document.querySelectorAll(`[data-i18n-${attr}]`).forEach(element => {
        const message = messages[element.getAttribute(`data-i18n-${attr}`)];
        if (message) element.setAttribute(attr, message[language]);
      });
    }
    document.querySelectorAll('[data-wp-zh][data-wp-en]').forEach(element => {
      const value = element.getAttribute(language === 'en' ? 'data-wp-en' : 'data-wp-zh');
      element.textContent = value || element.getAttribute('data-wp-zh') || '';
    });
    const metadata = window.FloatDocumentMeta;
    if (metadata) {
      const key = language === 'en' ? 'en' : 'zh';
      document.title = metadata.title[key];
      document.querySelectorAll('meta[property="og:title"], meta[name="twitter:title"]').forEach(meta => { meta.content = metadata.title[key]; });
      document.querySelectorAll('meta[name="description"], meta[property="og:description"], meta[name="twitter:description"]').forEach(meta => { meta.content = metadata.description[key]; });
    }
    root.lang = language;
    updateControls();
    document.dispatchEvent(new CustomEvent('site:languagechange', {detail: {language}}));
    if (announce) document.querySelector('[data-preference-status]').textContent = language === 'zh-CN' ? '语言已切换为简体中文。' : 'Language switched to English.';
  }

  function applyTheme(announce = false) {
    root.dataset.theme = theme;
    document.querySelector('meta[name="theme-color"]').content = theme === 'light' ? '#f7f7f5' : '#050505';
    updateControls();
    if (announce) document.querySelector('[data-preference-status]').textContent = language === 'zh-CN' ? (theme === 'light' ? '已切换为日间模式。' : '已切换为夜间模式。') : (theme === 'light' ? 'Light mode enabled.' : 'Dark mode enabled.');
  }

  let initialized = false;
  function initialize() {
    if (initialized) return;
    initialized = true;
    applyLanguage();
    applyTheme();
    document.querySelector('[data-language-toggle]').addEventListener('click', () => {
      language = language === 'zh-CN' ? 'en' : 'zh-CN';
      save('float-space-language', language);
      applyLanguage(true);
    });
    document.querySelector('[data-theme-toggle]').addEventListener('click', () => {
      theme = theme === 'dark' ? 'light' : 'dark';
      save('float-space-theme', theme);
      applyTheme(true);
    });
  }

  window.SitePreferences = {translate, setText, initialize, get language() { return language; }};
  // Other pages/tabs observe the same preference without navigating or polling.
  window.addEventListener('storage', event => {
    if (event.key === 'float-space-language') {
      language = ['zh-CN', 'en'].includes(event.newValue) ? event.newValue : browserLanguage;
      if (initialized) applyLanguage();
    }
    if (event.key === 'float-space-theme') {
      theme = event.newValue === 'light' ? 'light' : 'dark';
      if (initialized) applyTheme(); else root.dataset.theme = theme;
    }
  });
  document.addEventListener('DOMContentLoaded', initialize, {once: true});
})();
