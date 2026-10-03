<?php
/* Template Name: 示例配置收藏 */
defined('ABSPATH') || exit;
get_header(); if (float_theme_password_gate()) { get_footer(); return; }
?>
<main id="main" tabindex="-1">
  <section class="armory-hero">
    <div class="container armory-hero-inner">
      <div>
        <p class="eyebrow armory-kicker">FICTIONAL DEMO / COLLECTION</p>
        <h1><span class="armory-muted" data-delta-text="heroLine1">示例</span><br><span data-delta-text="heroLine2">配置室</span><span class="hero-period">.</span></h1>
        <p class="armory-description" data-delta-text="heroDescription">整理配置、复制代码，并保存本机草稿。此页的示例素材均为原创虚构插画。</p>
        <div class="armory-hero-actions" data-ready>
          <a class="button armory-button" href="#collection"><span data-delta-text="viewCollection">查看收藏</span><span aria-hidden="true">↓</span></a>
          <button class="armory-quiet" type="button" data-new-build><span data-delta-text="newDraft">＋ 新建本机草稿</span></button>
        </div>
      </div>
      <figure class="armory-stage">
        <span class="armory-stage-label">ARMORY / 01</span>
        <span class="armory-word" aria-hidden="true">LOADOUT</span>
        <img src="<?php echo esc_url(float_theme_asset('images/demo/demo-rifle.svg')); ?>" alt="原创虚构示例步枪插画" data-delta-alt="heroWeaponAlt" width="1200" height="600" fetchpriority="high">
        <figcaption class="armory-stage-caption"><strong>DEMO RIFLE</strong><span data-delta-text="heroWeaponCaption">原创虚构示例插画</span></figcaption>
      </figure>
    </div>
  </section>
  <section class="armory-index container" id="collection" aria-labelledby="collection-title">
    <div class="armory-section-heading"><h2 id="collection-title" data-delta-text="collectionTitle">个人配置收藏</h2><p class="micro" data-collection-count>— / LOADOUTS</p></div>
    <p class="armory-loading" data-loading data-delta-text="loading">正在读取收藏与配件素材…</p>
    <div class="armory-notice" data-load-error hidden><p class="armory-error" data-load-error-message></p><button class="armory-quiet" type="button" data-retry data-delta-text="retry">重新加载</button></div>
    <div data-ready>
      <div class="armory-toolbar">
        <div class="armory-search"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 5 5"></path></svg><label class="sr-only" for="loadout-search" data-delta-text="searchLabel">搜索个人配置</label><input class="armory-input" id="loadout-search" type="search" maxlength="120" placeholder="武器名称、配置码、备注…" data-delta-placeholder="searchPlaceholder"></div>
        <div class="armory-scopes" aria-label="收藏来源" data-delta-aria="scopeLabel"><button type="button" data-scope="published" aria-pressed="true" data-delta-text="published">公开收藏</button><button type="button" data-scope="draft" aria-pressed="false" data-delta-text="drafts">本机草稿</button></div>
        <label class="sr-only" for="loadout-mode" data-delta-text="modeFilter">按模式筛选</label><select class="armory-select armory-mode-filter" id="loadout-mode"><option value="all" data-delta-text="allModes">全部模式</option value="operations" data-delta-text="operations">模式 A</option><option value="warfare" data-delta-text="warfare">模式 B</option></select>
      </div>
      <div class="armory-management"><p data-scope-help></p><div class="armory-tools"><button class="armory-quiet" type="button" data-import data-delta-text="import">导入草稿</button><button class="armory-quiet" type="button" data-export data-delta-text="export">导出配置</button><input type="file" data-import-file accept="application/json,.json" hidden aria-label="选择配置 JSON 文件" data-delta-aria="importFile"></div></div>
      <div data-empty class="armory-empty" hidden>
        <div class="armory-empty-copy"><h3 data-empty-title></h3><p data-empty-description></p><button type="button" class="button button-secondary" data-empty-action></button></div>
        <ol class="armory-empty-guide"><li><span>01</span><p data-delta-text="step1">填入你的配置代码</p></li><li><span>02</span><p data-delta-text="step2">记录武器与实际装配的配件</p></li><li><span>03</span><p data-delta-text="step3">保存为本机草稿，导出后整理发布</p></li></ol>
      </div>
      <div class="armory-grid" data-loadouts hidden><div class="armory-list" data-build-list aria-label="武器配置列表" data-delta-aria="listLabel"></div><article class="loadout-detail" data-build-detail aria-label="配置详情" data-delta-aria="detailLabel"></article></div>
      <div class="armory-catalog-note"><div><h3 data-delta-text="iconNoteTitle">原创示例配件插画。</h3><p data-delta-text="iconNote">随主题附带的插画均为虚构示例。你可按自己的用途扩展目录；未收录的配件可以先记录名称。</p><p class="micro" data-catalog-count></p></div><div class="armory-preview" data-icon-preview aria-label="示例配件图标预览" data-delta-aria="iconPreview"></div></div>
    </div>
    <noscript><p class="armory-nojs">个人配置码正在整理中。请启用 JavaScript，以使用搜索、复制配置码和本机草稿管理。示例插画与网站导航可直接查看。</p></noscript>
  </section>
</main>
<dialog class="armory-dialog" id="loadout-editor" aria-labelledby="editor-title">
  <div class="armory-dialog-header"><div><p class="micro">COLLECTION / LOCAL DRAFT</p><h2 id="editor-title" data-delta-text="editorTitle">整理一套配置</h2></div><button class="armory-close" type="button" data-close-editor aria-label="关闭编辑器" data-delta-aria="closeEditor">×</button></div>
  <form class="armory-editor-form" id="loadout-form">
    <div class="armory-editor-body">
      <p class="armory-draft-notice" data-delta-text="draftNotice">草稿只保存在当前浏览器，不会自动发布到网站。建议导出备份，避免清理浏览器后丢失。</p>
      <div class="armory-form-grid">
        <label class="armory-field"><span data-delta-text="nameLabel">配置名称</span><input class="armory-input" name="name" maxlength="80" required placeholder="给这套配置起个名字" data-delta-placeholder="namePlaceholder" autocomplete="off"></label>
        <label class="armory-field"><span data-delta-text="weaponLabel">武器</span><select class="armory-select" name="weaponId" required></select></label>
        <label class="armory-field armory-field-full" data-custom-weapon-field hidden><span data-delta-text="customWeaponLabel">未收录的武器名称</span><input class="armory-input" name="weaponName" maxlength="80" placeholder="填写名称，图片待补齐" data-delta-placeholder="customWeaponPlaceholder" autocomplete="off"></label>
        <label class="armory-field"><span data-delta-text="modeLabel">配置模式</span><select class="armory-select" name="mode"><option value="operations" data-delta-text="operations">模式 A</option><option value="warfare" data-delta-text="warfare">模式 B</option></select></label>
        <label class="armory-field"><span data-delta-text="seasonLabel">赛季 / 版本（可选）</span><input class="armory-input" name="season" maxlength="40" placeholder="例如：当前赛季" data-delta-placeholder="seasonPlaceholder" autocomplete="off"></label>
        <label class="armory-field armory-field-full"><span data-delta-text="codeLabel">完整配置码</span><input class="armory-input" name="code" maxlength="512" required placeholder="填写完整配置代码" data-delta-placeholder="codePlaceholder" autocomplete="off" spellcheck="false"><small data-delta-text="codeHelp">代码原样保存；配件需要按实际配置选择，不会自动解析。</small></label>
        <label class="armory-field armory-field-full"><span data-delta-text="notesLabel">使用心得 / 精校说明（可选）</span><textarea class="armory-textarea" name="notes" maxlength="1200" placeholder="适用距离、操作手感、精校设置…" data-delta-placeholder="notesPlaceholder"></textarea></label>
      </div>
      <section class="armory-editor-section" aria-labelledby="attachment-picker-title">
        <div class="loadout-subheading"><h3 id="attachment-picker-title" data-delta-text="attachmentsTitle">选择实际配件</h3><span class="micro" data-selected-count></span></div>
        <div class="armory-selected" data-selected-attachments></div>
        <div class="armory-picker-toolbar"><label class="sr-only" for="attachment-slot" data-delta-text="slotFilter">配件分类</label><select class="armory-select" id="attachment-slot"></select><label class="sr-only" for="attachment-search" data-delta-text="attachmentSearchLabel">搜索配件名称</label><input class="armory-input" type="search" id="attachment-search" maxlength="100" placeholder="搜索配件名称…" data-delta-placeholder="attachmentSearchPlaceholder"></div>
        <div class="attachment-grid armory-picker" data-attachment-picker></div>
        <div class="armory-picker-foot"><p data-picker-count></p><button class="armory-quiet" type="button" data-more-attachments data-delta-text="showMore">显示更多</button></div>
        <details class="armory-custom"><summary data-delta-text="missingAttachment">找不到配件？先记录名称</summary><p data-delta-text="missingAttachmentHelp">未收录的图标显示“待补图”，不会使用其他配件图片代替。</p><div class="armory-custom-fields"><label class="sr-only" for="custom-attachment-slot" data-delta-text="slotFilter">配件分类</label><select class="armory-select" id="custom-attachment-slot"></select><label class="sr-only" for="custom-attachment-name" data-delta-text="customAttachmentName">缺失配件的名称</label><input class="armory-input" id="custom-attachment-name" maxlength="80" placeholder="配件名称" data-delta-placeholder="customAttachmentPlaceholder"><button class="button button-secondary" type="button" data-add-custom data-delta-text="add">添加</button></div></details>
      </section>
    </div>
    <div class="armory-editor-footer"><p class="armory-error" data-editor-error role="alert"></p><div><button class="armory-quiet" type="button" data-close-editor data-delta-text="cancel">取消</button><button class="button armory-button" type="submit" data-delta-text="saveDraft">保存本机草稿</button></div></div>
  </form>
</dialog>
<dialog class="armory-dialog armory-dialog-small" id="manual-copy" aria-labelledby="manual-copy-title"><div class="armory-dialog-header"><h2 id="manual-copy-title" data-delta-text="manualCopyTitle">复制配置码</h2><button class="armory-close" type="button" data-close-copy aria-label="关闭" data-delta-aria="close">×</button></div><div class="armory-manual-body"><p data-delta-text="manualCopyHelp">浏览器暂时无法自动复制。代码已选中，可长按复制或使用 Ctrl/Cmd + C。</p><label class="sr-only" for="manual-copy-value" data-delta-text="codeLabel">完整配置码</label><textarea class="armory-textarea" id="manual-copy-value" readonly spellcheck="false"></textarea><button class="button button-secondary" type="button" data-close-copy data-delta-text="done">完成</button></div></dialog>
<div class="armory-toast" data-toast hidden><p role="status" aria-live="polite" data-toast-message></p><button type="button" data-toast-undo hidden data-delta-text="undo">撤销</button></div>

<?php get_footer(); ?>
