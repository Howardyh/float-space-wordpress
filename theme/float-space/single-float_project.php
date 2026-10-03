<?php defined('ABSPATH') || exit; get_header(); if (float_theme_password_gate()) { get_footer(); return; } while (have_posts()) : the_post(); $r = float_theme_project(); ?>
<main id="main" tabindex="-1">
<section class="detail-hero container">
  <a class="breadcrumb" href="<?php echo esc_url(get_post_type_archive_link('float_project')); ?>"><span data-i18n="t027351de5625">← 全部项目</span> <span>/ <?php echo esc_html($r['number']); ?></span></a>
  <p class="eyebrow">EXPERIMENT <?php echo esc_html($r['number']); ?> / <?php float_theme_text($r['status'], $r['statusEn']); ?></p>
  <h1><?php float_theme_text($r['title'], $r['titleEn']); ?><span class="hero-period">.</span></h1>
  <?php float_theme_text($r['tagline'], $r['taglineEn'], 'p', 'detail-tagline'); ?>
  <dl class="project-meta"><div><dt data-i18n="t940dba58d1bf">年份</dt><dd><?php echo esc_html($r['year']); ?></dd></div><div><dt data-i18n="t8c2e4a035f5f">状态</dt><dd><span class="status-dot"></span><?php float_theme_text($r['status'], $r['statusEn']); ?></dd></div><div><dt data-i18n="tc6573ec96f1a">技术栈</dt><dd><?php echo esc_html(implode(' / ', (array) $r['stack'])); ?></dd></div></dl>
</section>
<?php if (in_array($r['demo'], ['schedule', 'monitor'], true)) : get_template_part('template-parts/showcase-' . $r['demo']); elseif (has_post_thumbnail()) : ?><figure class="container"><?php the_post_thumbnail('large', ['class' => 'wp-project-image']); ?></figure><?php endif; ?>
<div class="wp-project-body"><?php float_theme_content(); ?></div>
<?php if ($r['github']) : ?><div class="container detail-links wp-contact"><a class="button button-primary" href="<?php echo esc_url($r['github']); ?>" target="_blank" rel="noopener noreferrer"><span data-i18n="t75e96614e5a8">在 GitHub 查看源码</span> ↗</a></div><?php endif; ?>
<?php $public_projects = float_theme_projects(); $current_index = array_search(get_the_ID(), array_map(fn($p) => $p->ID, $public_projects), true); $next = count($public_projects) > 1 && $current_index !== false ? $public_projects[($current_index + 1) % count($public_projects)] : null; if ($next) : $n = float_theme_project($next); ?>
<aside class="next-project container"><div><span class="micro" data-i18n="te62f1af47505">下一个实验</span><a href="<?php echo esc_url($n['href']); ?>"><?php float_theme_text($n['title'], $n['titleEn']); ?> <span aria-hidden="true">↗</span></a></div><span class="next-number" aria-hidden="true"><?php echo esc_html($n['number']); ?></span></aside>
<?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>
