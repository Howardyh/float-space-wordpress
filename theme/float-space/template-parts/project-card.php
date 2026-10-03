<?php
defined('ABSPATH') || exit;
$project = get_post($args['post'] ?? null);
// Cards are always public, even if a query filter supplies a protected post.
if (!$project || $project->post_status !== 'publish' || $project->post_password !== '') return;
$r = float_theme_project($project);
?>
<article class="project-card" data-project="<?php echo esc_attr($r['demo']); ?>">
  <div class="project-card-copy">
    <p class="micro project-index"><?php echo esc_html($r['number']); ?> / <?php float_theme_text($r['status'], $r['statusEn']); ?></p>
    <h3><a href="<?php echo esc_url($r['href']); ?>"><?php float_theme_text($r['title'], $r['titleEn']); ?><span class="project-title-dot">.</span></a></h3>
    <?php float_theme_text($r['tagline'] ?: $r['description'], $r['taglineEn'] ?: $r['descriptionEn'], 'p', 'project-tagline'); ?>
    <?php float_theme_tags($r['stack']); ?>
    <a class="text-link project-link" href="<?php echo esc_url($r['href']); ?>"><span data-i18n="td0290f531641">查看项目</span> <span aria-hidden="true">→</span><span class="sr-only"> — <?php float_theme_text($r['short'] ?: $r['title'], $r['shortEn'] ?: $r['titleEn']); ?></span></a>
  </div>
  <figure class="project-card-visual">
    <?php if (in_array($r['demo'], ['schedule', 'monitor'], true)) : get_template_part('template-parts/card-visual-' . $r['demo']);
    elseif (has_post_thumbnail($project)) : echo get_the_post_thumbnail($project, 'large', ['class' => 'wp-project-image']);
    else : ?><div class="wp-project-fallback"><span><?php echo esc_html($r['number'] ?: '+'); ?></span></div><?php endif; ?>
  </figure>
</article>
