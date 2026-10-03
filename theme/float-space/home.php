<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main" tabindex="-1"><section class="notes-page container">
  <p class="eyebrow" data-i18n="t31e1b4ccf173">一本正在准备的笔记。</p><h1><span data-i18n="t8a7525b1492f">笔记</span><span class="hero-period">.</span></h1>
  <p class="notes-page-lead"><span data-i18n="ta5bff3dc13b7">想法、实验，</span><br><span data-i18n="tc84cb75097c5">和值得记下来的东西。</span></p>
  <?php if (have_posts()) : ?><div class="wp-notes-list"><?php while (have_posts()) : the_post(); ?>
  <article class="wp-note-card"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time><h2><a href="<?php the_permalink(); ?>"><?php float_theme_text(get_the_title(), post_password_required() ? '' : get_post_meta(get_the_ID(), '_float_title_en', true)); ?></a></h2><?php if (post_password_required()) : float_theme_text('此内容受密码保护。', 'This content is password protected.', 'p'); else : float_theme_text(get_the_excerpt(), get_post_meta(get_the_ID(), '_float_excerpt_en', true), 'p'); endif; ?><a class="text-link" href="<?php the_permalink(); ?>"><span data-wp-zh="阅读笔记" data-wp-en="Read note">阅读笔记</span> →</a></article>
  <?php endwhile; ?></div><div class="wp-pagination"><?php the_posts_pagination(['mid_size' => 1, 'prev_text' => '←', 'next_text' => '→']); ?></div>
  <?php else : ?><div class="coming-soon"><span class="status-dot"></span><span data-i18n="t9e3090de7470">正在准备。</span></div><p class="notes-page-note" data-i18n="t9004633d6ca4">先让这些项目，讲讲这里的故事。</p><?php if (post_type_exists('float_project') && get_post_type_archive_link('float_project')) : ?><a class="text-link" href="<?php echo esc_url(get_post_type_archive_link('float_project')); ?>"><span data-i18n="t8633f988c53e">看看这些项目</span> →</a><?php endif; ?><?php endif; ?>
</section></main>
<?php get_footer(); ?>
