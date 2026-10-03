<?php defined('ABSPATH') || exit; get_header(); ?>
<main id="main" tabindex="-1"><section class="project-index-page container">
  <div class="page-intro"><p class="eyebrow" data-i18n="t1a746087b6e0">项目选集</p><h1><span data-i18n="t04e2a9728af7">项目</span><span class="hero-period">.</span></h1><p data-i18n="t05b4a73a3dae">动手做过，推倒重来，还在继续改进的东西。</p></div>
  <div class="project-list"><?php if (have_posts()) : while (have_posts()) : the_post(); get_template_part('template-parts/project-card', null, ['post' => get_post()]); endwhile; else : ?><p class="wp-empty-projects" data-wp-zh="项目正在准备。" data-wp-en="Projects are on the way.">项目正在准备。</p><?php endif; ?></div>
  <div class="wp-pagination"><?php the_posts_pagination(['mid_size' => 1, 'prev_text' => '←', 'next_text' => '→']); ?></div>
  <div class="index-note"><span class="micro" data-i18n="t27c57438d2cc">每个项目，一个新问题。</span><p data-i18n="t9d208c723c21">在这里动手、学习，再重新开始。</p></div>
</section></main>
<?php get_footer(); ?>
