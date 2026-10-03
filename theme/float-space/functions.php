<?php
/** Presentation helpers; content features are optional and live in FLOAT Content. */
defined('ABSPATH') || exit;
add_action('after_setup_theme', function () {
    foreach (['title-tag', 'post-thumbnails', 'automatic-feed-links', 'responsive-embeds'] as $support) add_theme_support($support);
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 120, 'width' => 120, 'flex-height' => true, 'flex-width' => true]);
    register_nav_menus(['primary' => 'Primary navigation', 'footer' => 'Footer navigation']);
});
function float_theme_asset($path) { return get_template_directory_uri() . '/assets/' . ltrim($path, '/'); }
function float_theme_version($path) {
    $file = get_template_directory() . '/assets/' . ltrim($path, '/');
    return is_file($file) ? (string) filemtime($file) : '1.0.0';
}
function float_theme_is_loadouts() { return is_page('loadouts') || is_page_template('page-loadouts.php'); }
function float_theme_loadouts_page() {
    $page = get_page_by_path('loadouts');
    if (!$page || $page->post_status !== 'publish' || $page->post_password !== '') {
        $pages = get_posts(['post_type' => 'page', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-loadouts.php']);
        $page = $pages[0] ?? null;
    }
    return $page;
}
function float_theme_notes_url() {
    $page_id = (int) get_option('page_for_posts');
    $page = $page_id > 0 ? get_post($page_id) : null;
    if ($page && $page->post_status === 'publish' && $page->post_password === '') return get_permalink($page);
    return get_option('show_on_front') === 'posts' ? home_url('/') : '';
}
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('float-preferences', float_theme_asset('js/preferences.js'), [], float_theme_version('js/preferences.js'), false);
    foreach (['main', 'interactions', 'wordpress'] as $name) wp_enqueue_style('float-' . $name, float_theme_asset('css/' . $name . '.css'), [], float_theme_version('css/' . $name . '.css'));
    foreach (['main', 'interactions'] as $name) wp_enqueue_script('float-' . $name, float_theme_asset('js/' . $name . '.js'), ['float-preferences'], float_theme_version('js/' . $name . '.js'), true);
    if (is_front_page()) {
        wp_enqueue_style('float-motion', float_theme_asset('css/motion.css'), ['float-main'], float_theme_version('css/motion.css'));
        wp_enqueue_script('float-motion', float_theme_asset('js/motion.js'), ['float-main'], float_theme_version('js/motion.js'), true);
    }
    if (is_front_page() || is_post_type_archive('float_project') || is_singular('float_project')) wp_enqueue_script('float-projects', float_theme_asset('js/projects.js'), ['float-main'], float_theme_version('js/projects.js'), true);
    if (float_theme_is_loadouts() && !post_password_required()) {
        wp_enqueue_style('float-delta', float_theme_asset('css/delta.css'), ['float-main'], float_theme_version('css/delta.css'));
        wp_enqueue_script('float-delta', float_theme_asset('js/delta.js'), ['float-main'], float_theme_version('js/delta.js'), true);
        wp_add_inline_script('float-delta', 'window.FloatArmory=' . wp_json_encode([
            'catalogUrl' => float_theme_asset('data/demo-catalog.json'),
            'loadoutsUrl' => function_exists('float_space_public_loadouts') ? rest_url('float-space/v1/loadouts') : null,
            'assetsUrl' => float_theme_asset(''), 'pageUrl' => get_permalink(),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';', 'before');
    }
    wp_enqueue_style('float-navigation', float_theme_asset('css/navigation.css'), ['float-wordpress'], float_theme_version('css/navigation.css'));
    wp_enqueue_script('float-navigation', float_theme_asset('js/navigation.js'), ['float-main'], float_theme_version('js/navigation.js'), true);
});
function float_theme_text($zh, $en = '', $tag = 'span', $class = '') {
    $tag = in_array($tag, ['span', 'p', 'h1', 'h2', 'h3', 'strong', 'small', 'a', 'div'], true) ? $tag : 'span';
    $en = $en === '' ? $zh : $en;
    printf('<%1$s%2$s data-wp-zh="%3$s" data-wp-en="%4$s">%5$s</%1$s>', $tag, $class ? ' class="' . esc_attr($class) . '"' : '', esc_attr($zh), esc_attr($en), esc_html($zh));
}
function float_theme_home($key, $zh = '', $en = '', $tag = 'span', $class = '') {
    static $defaults = null;
    if ($defaults === null) $defaults = json_decode(file_get_contents(get_template_directory() . '/data/home-copy.json'), true) ?: [];
    $zh = $zh ?: ($defaults[$key]['zh'] ?? ''); $en = $en ?: ($defaults[$key]['en'] ?? '');
    $value = function_exists('float_home_text') ? float_home_text($key, $zh, $en) : ['zh' => $zh, 'en' => $en];
    float_theme_text($value['zh'] ?? $zh, $value['en'] ?? $en, $tag, $class);
}
function float_theme_setting($key, $default = '') {
    if ($key === 'siteName') return get_bloginfo('name') ?: $default;
    $value = function_exists('float_site_link') ? float_site_link($key, $default) : get_theme_mod('float_' . $key, $default);
    return is_scalar($value) ? (string) $value : $default;
}
function float_theme_github() { return esc_url(float_theme_setting('githubUrl'), ['http', 'https']); }
function float_theme_logo() {
    $custom = get_theme_mod('custom_logo'); $url = $custom ? wp_get_attachment_image_url($custom, 'full') : false;
    return $url ?: float_theme_asset('logo.svg');
}
function float_theme_footer_value($key) {
    $value = function_exists('float_site_footer_value') ? float_site_footer_value($key) : get_theme_mod('float_footer_' . $key, '');
    return is_scalar($value) ? (string) $value : '';
}
add_action('customize_register', function ($customizer) {
    $customizer->add_section('float_space_options', ['title' => 'FLOAT Space', 'priority' => 160]);
    $fields = ['githubUrl' => ['GitHub URL', 'url', 'esc_url_raw'], 'contactEmail' => ['Public contact email', 'email', 'sanitize_email']];
    foreach ($fields as $key => [$label, $type, $sanitize]) {
        $customizer->add_setting('float_' . $key, ['default' => '', 'sanitize_callback' => $sanitize]);
        $customizer->add_control('float_' . $key, ['section' => 'float_space_options', 'label' => $label, 'type' => $type]);
    }
    foreach (['copyrightLabel' => 'Copyright label (blank uses site title)', 'icpLabel' => 'Optional filing label', 'icpUrl' => 'Optional filing URL', 'policeLabel' => 'Additional optional filing label', 'policeUrl' => 'Additional optional filing URL'] as $key => $label) {
        $is_url = str_ends_with($key, 'Url');
        $customizer->add_setting('float_footer_' . $key, ['default' => '', 'sanitize_callback' => $is_url ? 'esc_url_raw' : 'sanitize_text_field']);
        $customizer->add_control('float_footer_' . $key, ['section' => 'float_space_options', 'label' => $label, 'type' => $is_url ? 'url' : 'text']);
    }
});
function float_theme_projects($limit = -1) {
    if (!post_type_exists('float_project')) return [];
    $posts = get_posts(['post_type' => 'float_project', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => $limit, 'orderby' => 'menu_order', 'order' => 'ASC']);
    return array_values(array_filter($posts, fn($post) => $post->post_status === 'publish' && $post->post_password === ''));
}
add_action('pre_get_posts', function ($query) { if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('float_project')) $query->set('has_password', false); });
function float_theme_project($post = null) {
    $post = get_post($post);
    if (!$post || post_password_required($post)) return [];
    if (function_exists('float_project_record')) return float_project_record($post);
    $meta = fn($key) => get_post_meta($post->ID, '_float_' . $key, true);
    return ['title' => get_the_title($post), 'titleEn' => $meta('title_en'), 'description' => get_the_excerpt($post), 'descriptionEn' => $meta('excerpt_en'), 'tagline' => $meta('tagline'), 'taglineEn' => $meta('tagline_en'), 'short' => $meta('short'), 'shortEn' => $meta('short_en'), 'number' => $meta('number'), 'stack' => preg_split('/\s*[,\n]\s*/', (string) $meta('stack'), -1, PREG_SPLIT_NO_EMPTY), 'year' => $meta('year'), 'status' => $meta('status'), 'statusEn' => $meta('status_en'), 'github' => $meta('github'), 'href' => get_permalink($post), 'demo' => $meta('demo')];
}
function float_theme_tags($stack) {
    if (!$stack) return;
    echo '<ul class="tags" aria-label="项目技术栈" data-i18n-aria-label="t1598fee022ac">';
    foreach ((array) $stack as $tag) echo '<li>' . esc_html($tag) . '</li>';
    echo '</ul>';
}
function float_theme_content($post = null) {
    $post = get_post($post); if (!$post) return;
    if (post_password_required($post)) { echo get_the_password_form($post); return; }
    $english = get_post_meta($post->ID, '_float_content_en', true); $chinese = apply_filters('the_content', $post->post_content);
    if ($english === '') { echo $chinese; return; }
    echo '<div data-wp-language="zh-CN">' . $chinese . '</div><div data-wp-language="en" lang="en">' . apply_filters('the_content', $english) . '</div>';
}
function float_theme_password_gate($post = null) {
    $post = get_post($post); if (!$post || !post_password_required($post)) return false;
    echo '<main id="main" tabindex="-1"><article class="container"><header class="detail-hero"><h1>' . esc_html(get_the_title($post)) . '</h1></header><div class="wp-content-body">' . get_the_password_form($post) . '</div></article></main>';
    return true;
}
require_once get_template_directory() . '/inc/navigation.php';
add_filter('body_class', function ($classes) { if (float_theme_is_loadouts()) $classes[] = 'armory-page'; return $classes; });
add_action('wp_head', function () {
    if (is_404()) return;
    $protected = is_singular() && post_password_required();
    $description = is_singular() && !$protected ? get_the_excerpt() : get_bloginfo('description');
    $description = trim(wp_strip_all_tags($description)) ?: 'Projects, notes, and experiments.';
    $home_description = null;
    if (is_front_page() && function_exists('float_home_text')) { $home_description = float_home_text('aboutDescription', $description, $description); $description = wp_html_excerpt(wp_strip_all_tags($home_description['zh']), 240, '…'); }
    $url = is_singular() ? get_permalink() : (is_post_type_archive('float_project') ? get_post_type_archive_link('float_project') : (is_home() ? (float_theme_notes_url() ?: home_url('/')) : home_url('/')));
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url(float_theme_asset('images/social.svg')) . '">' . "\n";
    if (!is_singular()) echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    $title = wp_get_document_title(); $title_en = $title; $description_en = $description;
    if (is_front_page()) { $title_en = get_bloginfo('name') . ' — Projects & Experiments'; $description_en = $home_description ? wp_html_excerpt(wp_strip_all_tags($home_description['en']), 240, '…') : $description; }
    elseif (is_singular() && !$protected) {
        if ($en = get_post_meta(get_queried_object_id(), '_float_title_en', true)) $title_en = $en . ' — ' . get_bloginfo('name');
        if ($en = get_post_meta(get_queried_object_id(), '_float_excerpt_en', true)) $description_en = wp_strip_all_tags($en);
    } elseif (is_post_type_archive('float_project')) $title_en = 'Projects — ' . get_bloginfo('name');
    elseif (is_home()) $title_en = 'Notes — ' . get_bloginfo('name');
    echo '<script id="float-document-meta">window.FloatDocumentMeta=' . wp_json_encode(['title' => ['zh' => $title, 'en' => $title_en], 'description' => ['zh' => $description, 'en' => $description_en]], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>' . "\n";
}, 5);
