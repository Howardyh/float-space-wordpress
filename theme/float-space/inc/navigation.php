<?php
/** Accessible navigation and optional bilingual menu fields. */
defined('ABSPATH') || exit;

add_action('wp_nav_menu_item_custom_fields', function ($item_id, $item) {
    wp_nonce_field('float_save_navigation_' . $item_id, 'float_nav_nonce[' . $item_id . ']', false);
    foreach (['title_en' => '英文名称 / English label', 'description_zh' => '中文简介 / Chinese description', 'description_en' => '英文简介 / English description'] as $key => $label) {
        $field = 'float-nav-' . $key . '-' . $item_id;
        printf('<p class="description description-wide"><label for="%1$s">%2$s<br><input type="text" class="widefat" id="%1$s" name="float_nav_%3$s[%4$d]" value="%5$s" maxlength="200"></label></p>', esc_attr($field), esc_html($label), esc_attr($key), (int) $item_id, esc_attr(get_post_meta($item_id, '_float_nav_' . $key, true)));
    }
}, 10, 2);

add_action('wp_update_nav_menu_item', function ($menu_id, $item_id) {
    $nonce = $_POST['float_nav_nonce'][$item_id] ?? '';
    if (!current_user_can('edit_theme_options') || get_post_type($item_id) !== 'nav_menu_item' || !is_string($nonce) || !wp_verify_nonce(wp_unslash($nonce), 'float_save_navigation_' . $item_id)) return;
    foreach (['title_en', 'description_zh', 'description_en'] as $key) {
        $raw = $_POST['float_nav_' . $key][$item_id] ?? '';
        $value = is_string($raw) ? wp_html_excerpt(sanitize_text_field(wp_unslash($raw)), 200, '') : '';
        if ($value === '') delete_post_meta($item_id, '_float_nav_' . $key);
        else update_post_meta($item_id, '_float_nav_' . $key, $value);
    }
}, 10, 2);

function float_navigation_toggle($id, $label) {
    return '<button class="nav-submenu-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr($id) . '" aria-label="' . esc_attr('展开' . $label . '子菜单') . '"><svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></button>';
}

function float_navigation_label($zh, $en, $class = 'nav-label') {
    return '<span class="' . esc_attr($class) . '" data-wp-zh="' . esc_attr($zh) . '" data-wp-en="' . esc_attr($en ?: $zh) . '">' . esc_html($zh) . '</span>';
}

function float_navigation_defaults() {
    $items = [];
    $archive = post_type_exists('float_project') ? get_post_type_archive_link('float_project') : '';
    if ($archive) {
        $projects = [];
        foreach (float_theme_projects(5) as $post) {
            $record = float_theme_project($post);
            $projects[] = ['url' => get_permalink($post), 'zh' => get_the_title($post), 'en' => $record['titleEn'] ?? '', 'description_zh' => wp_html_excerpt(wp_strip_all_tags($record['description'] ?? ''), 100, '…'), 'description_en' => wp_html_excerpt(wp_strip_all_tags($record['descriptionEn'] ?? ''), 160, '…'), 'current' => is_singular('float_project') && get_queried_object_id() === $post->ID];
        }
        if ($projects) $projects[] = ['url' => $archive, 'zh' => '查看全部项目', 'en' => 'View all projects'];
        $items[] = ['url' => $archive, 'zh' => '项目', 'en' => 'Projects', 'current' => is_post_type_archive('float_project'), 'ancestor' => is_singular('float_project'), 'children' => $projects];
    }
    if ($notes = float_theme_notes_url()) $items[] = ['url' => $notes, 'zh' => '笔记', 'en' => 'Notes', 'current' => is_home(), 'ancestor' => is_singular('post')];
    $collection = null;
    $page = function_exists('float_theme_loadouts_page') ? float_theme_loadouts_page() : null;
    if ($page && function_exists('float_space_public_loadouts')) $collection = ['url' => get_permalink($page), 'zh' => '配置收藏', 'en' => 'Collections', 'current' => is_page($page->ID)];
    $collection = apply_filters('float_navigation_collection_item', $collection);
    if (is_array($collection)) $items[] = $collection;
    if (get_option('show_on_front') === 'page') $items[] = ['url' => home_url('/#about'), 'zh' => '关于', 'en' => 'About'];
    elseif (!$notes || $notes !== home_url('/')) $items[] = ['url' => home_url('/'), 'zh' => '首页', 'en' => 'Home', 'current' => is_front_page()];
    return $items;
}

function float_navigation_render_defaults($items, $location, $level = 0) {
    $footer = $location === 'footer';
    foreach ($items as $index => $item) {
        $children = !$footer ? ($item['children'] ?? []) : [];
        $id = wp_unique_id('float-' . $location . '-submenu-');
        $current = !empty($item['current']);
        $classes = 'nav-item' . ($children ? ' has-children' : '') . ($current ? ' is-active' : '') . (!empty($item['ancestor']) ? ' is-ancestor' : '');
        echo '<li class="' . esc_attr($classes) . '">' . ($footer ? '' : '<div class="nav-row">');
        $external = ($item['target'] ?? '') === '_blank';
        echo '<a class="nav-link" href="' . esc_url($item['url']) . '"' . ($external ? ' target="_blank" rel="noopener noreferrer"' : '') . ($current ? ' aria-current="page"' : '') . '>' . float_navigation_label($item['zh'], $item['en'] ?? '');
        if (!$footer && $level > 0 && (!empty($item['description_zh']) || !empty($item['description_en']))) echo float_navigation_label($item['description_zh'] ?: $item['description_en'], $item['description_en'], 'nav-description');
        if ($external) echo '<span class="sr-only" data-wp-zh="（在新窗口打开）" data-wp-en=" (opens in a new window)">（在新窗口打开）</span>';
        echo '</a>';
        if ($children) echo float_navigation_toggle($id, $item['zh']);
        if (!$footer) echo '</div>';
        if ($children) {
            echo '<ul class="nav-submenu" id="' . esc_attr($id) . '">';
            float_navigation_render_defaults($children, $location, $level + 1);
            echo '</ul>';
        }
        echo '</li>';
    }
}

function float_theme_nav($location) {
    $footer = $location === 'footer';
    if (has_nav_menu($location)) {
        wp_nav_menu(['theme_location' => $location, 'container' => false, 'menu_class' => $footer ? 'footer-menu' : 'nav-list', 'menu_id' => 'float-' . $location . '-menu', 'depth' => $footer ? 1 : 0, 'fallback_cb' => false, 'walker' => new Float_Space_Menu_Walker($location)]);
        return;
    }
    echo '<ul class="' . ($footer ? 'footer-menu' : 'nav-list') . '">';
    $items = float_navigation_defaults();
    if ($footer && float_theme_github()) $items[] = ['url' => float_theme_github(), 'zh' => 'GitHub', 'en' => 'GitHub', 'target' => '_blank'];
    float_navigation_render_defaults($items, $location);
    echo '</ul>';
}

class Float_Space_Menu_Walker extends Walker_Nav_Menu {
    private $location;
    private $submenu_ids = [];
    public function __construct($location = 'primary') { $this->location = $location; }
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<ul class="nav-submenu" id="' . esc_attr($this->submenu_ids[$depth]) . '">';
    }
    public function end_lvl(&$output, $depth = 0, $args = null) { $output .= '</ul>'; }
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $footer = $this->location === 'footer';
        $children = !$footer && !empty($this->has_children);
        $current = in_array('current-menu-item', (array) $item->classes, true);
        $ancestor = (bool) array_intersect(['current-menu-parent', 'current-menu-ancestor'], (array) $item->classes);
        if ($item->type === 'post_type_archive' && $item->object === 'float_project' && is_singular('float_project')) $ancestor = true;
        $archive = post_type_exists('float_project') ? get_post_type_archive_link('float_project') : '';
        if ($archive && untrailingslashit($item->url) === untrailingslashit($archive) && is_singular('float_project')) $ancestor = true;
        $class = 'nav-item' . ($children ? ' has-children' : '') . ($current ? ' is-active' : '') . ($ancestor ? ' is-ancestor' : '');
        $target = $item->target === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
        $translations = apply_filters('float_navigation_translations', ['项目' => 'Projects', '配置收藏' => 'Collections', '关于' => 'About', '笔记' => 'Notes', '首页' => 'Home']);
        $en = get_post_meta($item->ID, '_float_nav_title_en', true) ?: ($translations[$item->title] ?? $item->title);
        $output .= '<li id="float-' . esc_attr($this->location) . '-item-' . (int) $item->ID . '" class="' . esc_attr($class) . '">' . ($footer ? '' : '<div class="nav-row">');
        $output .= '<a class="nav-link" href="' . esc_url($item->url) . '"' . $target . ($current ? ' aria-current="page"' : '') . '>' . float_navigation_label($item->title, $en);
        $description = get_post_meta($item->ID, '_float_nav_description_zh', true) ?: wp_strip_all_tags($item->description);
        $description_en = get_post_meta($item->ID, '_float_nav_description_en', true);
        if (!$footer && $depth > 0 && ($description || $description_en)) $output .= float_navigation_label($description ?: $description_en, $description_en, 'nav-description');
        if ($target) $output .= '<span class="sr-only" data-wp-zh="（在新窗口打开）" data-wp-en=" (opens in a new window)">（在新窗口打开）</span>';
        $output .= '</a>';
        if ($children) {
            $this->submenu_ids[$depth] = 'float-' . $this->location . '-submenu-' . $item->ID;
            $output .= float_navigation_toggle($this->submenu_ids[$depth], $item->title);
        }
        if (!$footer) $output .= '</div>';
    }
    public function end_el(&$output, $item, $depth = 0, $args = null) { $output .= '</li>'; }
}
