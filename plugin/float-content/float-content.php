<?php
/**
 * Plugin Name: FLOAT Content
 * Description: Optional bilingual portfolio fields, site copy, and curated demo loadout collections for FLOAT Space.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: FLOAT Space contributors
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: float-content
 */

defined('ABSPATH') || exit;

define('FLOAT_CONTENT_DIR', __DIR__);

function float_space_register_content() {
    $caps = array_fill_keys(array('edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts'), 'manage_options');
    register_post_type('float_project', array(
        'labels' => array('name' => '项目', 'singular_name' => '项目', 'add_new_item' => '新建项目', 'edit_item' => '编辑项目'),
        'public' => true, 'show_in_rest' => true, 'has_archive' => 'projects',
        'rewrite' => array('slug' => 'projects', 'with_front' => false),
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes'),
        'menu_icon' => 'dashicons-portfolio', 'capabilities' => $caps, 'map_meta_cap' => false,
    ));
    register_post_type('float_loadout', array(
        'labels' => array('name' => '配置收藏', 'singular_name' => '配置', 'add_new_item' => '新建配置', 'edit_item' => '编辑配置'),
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => false,
        'rewrite' => false, 'supports' => array('title', 'revisions'),
        'menu_icon' => 'dashicons-clipboard', 'capabilities' => $caps, 'map_meta_cap' => false,
    ));
}
add_action('init', 'float_space_register_content');
register_activation_hook(__FILE__, function () { float_space_register_content(); flush_rewrite_rules(); });
register_deactivation_hook(__FILE__, function () {
    unregister_post_type('float_project');
    unregister_post_type('float_loadout');
    flush_rewrite_rules();
});

/** These fields are intentionally protected and never exposed wholesale in REST. */
function float_space_project_fields() {
    return array(
        '_float_number' => array('显示序号', 'text'), '_float_short' => array('简称（中文）', 'text'),
        '_float_short_en' => array('简称（英文）', 'text'), '_float_tagline' => array('一句话介绍（中文）', 'text'),
        '_float_tagline_en' => array('一句话介绍（英文）', 'text'), '_float_stack' => array('技术栈（每行一项）', 'textarea'),
        '_float_year' => array('年份', 'text'), '_float_status' => array('状态（中文）', 'text'),
        '_float_status_en' => array('状态（英文）', 'text'), '_float_github' => array('源码地址', 'url'),
        '_float_demo' => array('互动示意', 'demo'),
    );
}

function float_project_record($post) {
    $post = get_post($post);
    if (!$post || $post->post_type !== 'float_project') return array();
    $meta = function ($key) use ($post) { return (string) get_post_meta($post->ID, $key, true); };
    $stack = preg_split('/[\r\n]+/', $meta('_float_stack'), -1, PREG_SPLIT_NO_EMPTY);
    return array(
        'id' => $post->ID, 'title' => $post->post_title, 'titleEn' => $meta('_float_title_en'),
        'description' => $post->post_excerpt, 'descriptionEn' => $meta('_float_excerpt_en'),
        'content' => $post->post_content, 'contentEn' => $meta('_float_content_en'),
        'number' => $meta('_float_number'), 'short' => $meta('_float_short'), 'shortEn' => $meta('_float_short_en'),
        'tagline' => $meta('_float_tagline'), 'taglineEn' => $meta('_float_tagline_en'),
        'stack' => array_values(array_map('trim', $stack ?: array())), 'year' => $meta('_float_year'),
        'status' => $meta('_float_status'), 'statusEn' => $meta('_float_status_en'),
        'github' => $meta('_float_github'), 'href' => get_permalink($post), 'demo' => $meta('_float_demo'),
    );
}

function float_space_catalog() {
    static $catalog;
    if ($catalog === null) {
        $catalog = json_decode(file_get_contents(FLOAT_CONTENT_DIR . '/data/demo-catalog.json'), true);
    }
    return is_array($catalog) ? $catalog : array('weapons' => array(), 'attachments' => array(), 'slots' => array());
}

function float_space_string_length($value) {
    $points = preg_match_all('/./us', $value);
    if ($points === false) return -1;
    // JavaScript limits strings in UTF-16 code units; astral characters count twice.
    return $points + preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $value);
}

function float_space_text($value, $max, $required = false) {
    if (!is_string($value)) return new WP_Error('float_invalid', '字段格式不正确。');
    $value = trim($value);
    $length = float_space_string_length($value);
    if ($length < 0 || $length > $max || ($required && $value === '') || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $value)) {
        return new WP_Error('float_invalid', '字段为空、过长或含有无效字符。');
    }
    return $value;
}

/** Canonical schema matches the existing browser export; unknown fields are discarded. */
function float_space_normalize_loadout($value) {
    if (!is_array($value)) return new WP_Error('float_invalid', '配置格式不正确。');
    $out = array();
    $limits = array('name' => 80, 'nameEn' => 80, 'weaponName' => 80, 'weaponNameEn' => 80, 'code' => 512, 'season' => 40, 'seasonEn' => 40, 'notes' => 1200, 'notesEn' => 1200);
    foreach ($limits as $key => $limit) {
        $out[$key] = float_space_text($value[$key] ?? '', $limit, in_array($key, array('name', 'code'), true));
        if (is_wp_error($out[$key])) return $out[$key];
    }
    if (float_space_string_length($out['code']) < 3 || preg_match('/[\r\n\t]/', $out['code'])) return new WP_Error('float_code', '配置代码需至少 3 个字符，并且只能占一行。');
    if (!in_array($value['mode'] ?? '', array('operations', 'warfare'), true)) return new WP_Error('float_mode', '请选择配置模式。');
    $catalog = float_space_catalog();
    $weapons = array_column($catalog['weapons'], null, 'id');
    $parts = array_column($catalog['attachments'], null, 'id');
    $slots = array_column($catalog['slots'], null, 'id');
    $weapon_id = is_string($value['weaponId'] ?? null) ? $value['weaponId'] : '';
    $weapon = $weapons[$weapon_id] ?? null;
    $out['weaponId'] = $weapon ? $weapon_id : null;
    $out['weaponName'] = $weapon ? $weapon['name'] : $out['weaponName'];
    if ($out['weaponName'] === '') return new WP_Error('float_weapon', '请选择武器或填写未收录武器的名称。');
    $out['mode'] = $value['mode'];
    if (!isset($value['attachments']) || !is_array($value['attachments']) || !array_is_list($value['attachments']) || count($value['attachments']) > 32) return new WP_Error('float_parts', '每套配置最多记录 32 个配件。');
    $out['attachments'] = array();
    $seen = array();
    foreach ($value['attachments'] as $part) {
        if (!is_array($part)) return new WP_Error('float_parts', '配件格式不正确。');
        $part_id = is_string($part['id'] ?? null) ? $part['id'] : '';
        $known = $parts[$part_id] ?? null;
        $name = $known ? $known['name'] : float_space_text($part['name'] ?? '', 80, true);
        $slot = $known ? $known['slot'] : ($part['slot'] ?? '');
        if (is_wp_error($name) || !is_string($slot) || !isset($slots[$slot])) return new WP_Error('float_parts', '配件名称或分类不正确。');
        $name_en = float_space_text($part['nameEn'] ?? '', 80);
        if (is_wp_error($name_en)) return $name_en;
        $key = $known ? 'id:' . $part_id : 'custom:' . $slot . ':' . $name;
        if (isset($seen[$key])) return new WP_Error('float_parts', '配置中包含重复配件。');
        $seen[$key] = true;
        $out['attachments'][] = array('id' => $known ? $part_id : null, 'name' => $name, 'nameEn' => $name_en, 'slot' => $slot);
    }
    return $out;
}

function float_space_loadout_record($post) {
    $post = get_post($post);
    if (!$post || $post->post_type !== 'float_loadout') return new WP_Error('float_invalid', '配置不存在。');
    $value = get_post_meta($post->ID, '_float_loadout_data', true);
    if (!is_array($value)) return new WP_Error('float_invalid', '配置不完整。');
    $value['name'] = $post->post_title;
    $record = float_space_normalize_loadout($value);
    if (is_wp_error($record)) return $record;
    $record['id'] = 'wp-' . $post->ID;
    $record['updatedAt'] = get_post_modified_time('c', true, $post);
    return $record;
}

function float_space_public_loadouts() {
    // Never accept status, author, password or context from the request.
    $posts = get_posts(array('post_type' => 'float_loadout', 'post_status' => 'publish', 'has_password' => false, 'numberposts' => 200, 'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'), 'suppress_filters' => false));
    $builds = array(); $codes = array();
    foreach ($posts as $post) {
        // Defensive checks also protect against filters broadening the query.
        if ($post->post_status !== 'publish' || $post->post_password !== '') continue;
        $record = float_space_loadout_record($post);
        if (is_wp_error($record) || isset($codes[$record['code']])) continue;
        $codes[$record['code']] = true;
        $builds[] = $record;
        if (count($builds) >= 200) break;
    }
    return array('version' => 1, 'builds' => $builds);
}

add_action('rest_api_init', function () {
    register_rest_route('float-space/v1', '/loadouts', array(
        'methods' => WP_REST_Server::READABLE, 'permission_callback' => '__return_true',
        'callback' => function () { $response = rest_ensure_response(float_space_public_loadouts()); $response->header('Cache-Control', 'no-cache, max-age=0'); return $response; },
    ));
});

// Keep publication privacy local to this module. Global user REST, author archives,
// and feeds remain WordPress policy decisions for the site owner and other plugins.

/** Admin-only preview returns the same safe schema without publishing it. */
add_action('admin_post_float_preview_loadout', function () {
    if (!current_user_can('manage_options')) wp_die('无权预览。', '', array('response' => 403));
    $id = absint($_GET['post_id'] ?? 0);
    check_admin_referer('float_preview_' . $id);
    $record = float_space_loadout_record($id);
    if (is_wp_error($record)) wp_die(esc_html($record->get_error_message()));
    nocache_headers();
    echo '<!doctype html><html lang="zh-CN"><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>配置预览</title><body style="max-width:800px;margin:40px auto;font:16px/1.7 system-ui;padding:0 20px"><h1>' . esc_html($record['name']) . '</h1><p>仅管理员可见；此预览不会发布配置。</p><dl><dt>武器</dt><dd>' . esc_html($record['weaponName']) . '</dd><dt>模式</dt><dd>' . ($record['mode'] === 'operations' ? '模式 A' : '模式 B') . '</dd><dt>配置代码</dt><dd><code>' . esc_html($record['code']) . '</code></dd><dt>说明</dt><dd>' . nl2br(esc_html($record['notes'])) . '</dd></dl><h2>配件</h2><ul>';
    foreach ($record['attachments'] as $part) echo '<li>' . esc_html($part['name']) . '</li>';
    echo '</ul><p><a href="' . esc_url(get_edit_post_link($id, 'raw')) . '">返回编辑</a></p></body></html>';
    exit;
});

function float_home_text($key, $zh, $en) {
    $copy = get_option('float_home_copy', array());
    $saved = is_array($copy) && isset($copy[$key]) && is_array($copy[$key]) ? $copy[$key] : array();
    $resolved_zh = (string) ($saved['zh'] ?? $zh);
    $resolved_en = (string) ($saved['en'] ?? $en);
    return array('zh' => $resolved_zh, 'en' => $resolved_en !== '' ? $resolved_en : $resolved_zh);
}

function float_site_link($key, $fallback = '') {
    $links = get_option('float_site_links', array());
    return in_array($key, array('githubUrl', 'contactEmail'), true) && is_array($links) && is_string($links[$key] ?? null) ? $links[$key] : $fallback;
}

/** Optional labels are blank on a fresh installation. Theme output must escape them. */
function float_site_footer_value($key) {
    $allowed = array('copyrightLabel', 'icpLabel', 'icpUrl', 'policeLabel', 'policeUrl');
    $footer = get_option('float_site_footer', array());
    return in_array($key, $allowed, true) && is_array($footer) && is_string($footer[$key] ?? null) ? $footer[$key] : '';
}

require_once FLOAT_CONTENT_DIR . '/admin.php';
require_once FLOAT_CONTENT_DIR . '/setup.php';
