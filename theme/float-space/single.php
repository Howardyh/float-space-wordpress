<?php defined('ABSPATH') || exit; get_header(); if (float_theme_password_gate()) { get_footer(); return; } while (have_posts()) : the_post(); ?>
<main id="main" tabindex="-1"><article class="container">
  <header class="detail-hero"><a class="breadcrumb" href="<?php echo esc_url(float_theme_notes_url() ?: home_url('/')); ?>"><span data-wp-zh="← 全部笔记" data-wp-en="← All notes">← 全部笔记</span></a><p class="eyebrow"><?php echo esc_html(get_the_date('Y.m.d')); ?></p><h1><?php float_theme_text(get_the_title(), get_post_meta(get_the_ID(), '_float_title_en', true)); ?><span class="hero-period">.</span></h1></header>
  <?php if (has_post_thumbnail()) the_post_thumbnail('large', ['class' => 'wp-project-image']); ?>
  <div class="wp-content-body"><?php float_theme_content(); ?></div>
</article></main>
<?php endwhile; get_footer(); ?>
