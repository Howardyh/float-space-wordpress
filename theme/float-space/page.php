<?php defined('ABSPATH') || exit; get_header(); if (float_theme_password_gate()) { get_footer(); return; } while (have_posts()) : the_post(); ?>
<main id="main" tabindex="-1"><article class="container"><header class="detail-hero"><h1><?php float_theme_text(get_the_title(), get_post_meta(get_the_ID(), '_float_title_en', true)); ?><span class="hero-period">.</span></h1></header><div class="wp-content-body"><?php float_theme_content(); ?></div></article></main>
<?php endwhile; get_footer(); ?>
