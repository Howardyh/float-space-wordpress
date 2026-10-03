<?php defined('ABSPATH') || exit; ?>
<!doctype html>
<html lang="zh-CN" data-theme="dark">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#050505">
  <meta name="color-scheme" content="dark light">
  <link rel="icon" href="<?php echo esc_url(get_template_directory_uri() . '/favicon.svg'); ?>" type="image/svg+xml">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main" data-i18n="tac576a66d456">跳转到正文</a>
<header class="site-header"><div class="container header-inner">
  <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name') . ' Home'); ?>"><img src="<?php echo esc_url(float_theme_logo()); ?>" alt="" width="24" height="24"><?php echo esc_html(float_theme_setting('siteName', 'FLOAT Space')); ?><span class="brand-dot">.</span></a>
  <div class="site-preferences" aria-label="语言与外观设置" data-i18n-aria-label="te356c5117ce1">
    <button class="preference-button language-toggle" type="button" data-language-toggle aria-label="Switch to English"><span data-language-label lang="en">English</span></button>
    <button class="preference-button theme-toggle" type="button" data-theme-toggle aria-label="Switch to light mode" aria-pressed="false"><svg class="theme-sun" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"></path></svg><svg class="theme-moon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M20.4 14.1A8.5 8.5 0 0 1 9.9 3.6 8.5 8.5 0 1 0 20.4 14.1Z"></path></svg><span data-theme-label>Light</span></button>
  </div>
  <button class="menu-toggle" type="button" aria-controls="primary-nav" aria-expanded="false"><span data-i18n="t99af6606ff9d">菜单</span><span class="menu-symbol" aria-hidden="true">+</span></button>
  <nav class="primary-nav" id="primary-nav" aria-label="主导航" data-i18n-aria-label="teb355944b92d"><?php float_theme_nav('primary'); ?></nav>
</div><p class="sr-only" data-preference-status role="status" aria-live="polite"></p></header>
