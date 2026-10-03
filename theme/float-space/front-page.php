<?php
defined('ABSPATH') || exit;
// Preserve WordPress's native posts homepage until a static front page is chosen.
if (is_home()) { get_template_part('home'); return; }
get_header();
if (float_theme_password_gate()) { get_footer(); return; }
$projects = float_theme_projects();
$featured = null;
foreach (array_reverse($projects) as $candidate) {
    if (get_post_meta($candidate->ID, '_float_demo', true) === 'monitor') { $featured = $candidate; break; }
}
if (!$featured && $projects) $featured = end($projects);
?>
<main id="main" tabindex="-1">
<?php get_template_part('template-parts/home-hero'); ?>
<?php if ($featured) : $record = float_theme_project($featured); ?>
<section class="section featured-section" id="featured" aria-labelledby="featured-title"><div class="container">
  <div class="section-heading"><p class="eyebrow"><span class="status-dot"></span> <span data-i18n="t72ec80986277">正在做的项目</span></p><span class="micro">EXPERIMENT <?php echo esc_html($record['number']); ?></span></div>
  <div class="featured-grid">
    <?php if ($record['demo'] === 'monitor') : get_template_part('template-parts/featured-monitor'); else : ?>
    <figure class="featured-visual"><?php if (has_post_thumbnail($featured)) echo get_the_post_thumbnail($featured, 'large', ['class' => 'wp-project-image']); else echo '<div class="wp-project-fallback"><span>' . esc_html($record['number']) . '</span></div>'; ?></figure>
    <?php endif; ?>
    <div class="featured-copy">
      <?php float_theme_text($record['title'], $record['titleEn'], 'p', 'micro'); ?>
      <h2 id="featured-title"><?php if ($record['demo'] === 'monitor') : float_theme_home('featuredTitle1'); ?><br><?php float_theme_home('featuredTitle2'); ?><br><?php float_theme_home('featuredTitle3', '', '', 'span', 'muted-word'); else : float_theme_text($record['title'], $record['titleEn']); endif; ?></h2>
      <?php float_theme_text($record['description'], $record['descriptionEn'], 'p'); ?>
      <?php float_theme_text($record['tagline'], $record['taglineEn'], 'p', 'feature-aside'); ?>
      <?php float_theme_tags($record['stack']); ?>
      <a class="button button-secondary" href="<?php echo esc_url($record['href']); ?>"><span data-i18n="t4f32763629e3">了解项目</span> <span aria-hidden="true">→</span></a>
    </div>
  </div>
</div></section>
<?php endif; ?>
<section class="section projects-section" id="projects" aria-labelledby="projects-title"><div class="container">
  <div class="projects-heading"><div><p class="eyebrow"><span data-i18n="t04e2a9728af7">项目</span> / <?php echo esc_html(wp_date('Y')); ?></p><h2 id="projects-title"><span data-i18n="t747132704a73">精选项目</span><span class="subtle-dot">.</span></h2></div><p><span data-i18n="tef6e30a6ce20">动手做过，推倒重来，</span><br><span data-i18n="t9a82eb6c1833">也一直在改进。</span></p></div>
  <div class="project-list"><?php foreach ($projects as $project) get_template_part('template-parts/project-card', null, ['post' => $project]); ?></div>
  <?php if (!$projects) : ?><p class="wp-empty-projects" data-wp-zh="项目正在准备。" data-wp-en="Projects are on the way.">项目正在准备。</p><?php endif; ?>
  <?php if ($projects) : ?>
  <div class="project-evolution"><span class="micro" data-i18n="ta9a212035c80">从构想，到实践。</span><div><?php foreach ($projects as $index => $project) : $r = float_theme_project($project); if ($index) echo '<span class="evolution-arrow" aria-hidden="true">→</span>'; ?><a href="<?php echo esc_url($r['href']); ?>"><span><?php echo esc_html($r['number']); ?></span> <?php float_theme_text($r['short'] ?: $r['title'], $r['shortEn'] ?: $r['titleEn']); ?></a><?php endforeach; ?></div><p data-i18n="t624542a30c04">开始尝试 → 重新思考 → 再次构建。</p></div>
  <?php endif; ?>
</div></section>
<?php get_template_part('template-parts/home-about'); ?>
<?php if ($notes_url = float_theme_notes_url()) : ?>
<section class="notes-teaser"><div class="container notes-teaser-inner"><div><p class="eyebrow" data-i18n="t7bfab6c61f77">笔记</p><h2><span data-i18n="t55df6b1aa5e6">值得</span><br><span data-i18n="t47fad0f097ea">记下来的东西</span><span class="subtle-dot">.</span></h2></div><div><p data-i18n="t609923e5de56">想法、实验，和那些值得记下来的东西。</p><a class="text-link" href="<?php echo esc_url($notes_url); ?>"><?php float_theme_text(wp_count_posts('post')->publish ? '阅读笔记' : '正在准备', wp_count_posts('post')->publish ? 'Read the notes' : 'Coming soon'); ?> <span aria-hidden="true">↗</span></a></div></div></section>
<?php endif; ?>
</main>
<?php get_footer(); ?>
