<?php defined('ABSPATH') || exit; ?>
<footer class="site-footer"><div class="container">
  <div class="footer-main"><div><a class="footer-name" href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(get_bloginfo('name')); ?><span aria-hidden="true">.</span></a><p data-wp-zh="让想法找到自己的空间。" data-wp-en="A space for your ideas.">让想法找到自己的空间。</p></div>
    <nav class="footer-nav" aria-label="页脚导航" data-i18n-aria-label="t76363a64dcfa"><?php float_theme_nav('footer'); ?></nav>
  </div>
  <div class="footer-bottom"><p>© <span data-year><?php echo esc_html(wp_date('Y')); ?></span> <?php echo esc_html(float_theme_footer_value('copyrightLabel') ?: get_bloginfo('name')); ?></p><div class="filings">
  <?php foreach (['icp', 'police'] as $prefix) : $label = float_theme_footer_value($prefix . 'Label'); $url = esc_url(float_theme_footer_value($prefix . 'Url'), ['http', 'https']); if (!$label || !$url) continue; ?>
    <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($label); ?></a>
  <?php endforeach; ?>
  </div></div>
</div></footer>
<?php wp_footer(); ?>
</body>
</html>
