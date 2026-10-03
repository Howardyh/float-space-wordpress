<?php
/** Optional, explicit setup. Activation never inserts content or changes site options. */
defined('ABSPATH') || exit;

function float_space_setup_form() {
    if (!current_user_can('manage_options')) return;
    echo '<hr><h2>可选页面设置</h2><p>此按钮会创建缺少的「首页」和「笔记」空白公开页面。已有页面和内容不会覆盖，账号与联系资料不会改变。</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="float_setup_pages">';
    wp_nonce_field('float_setup_pages');
    echo '<p><label><input type="checkbox" name="include_loadouts" value="1"> 同时创建示例配置收藏页面（虚构演示目录，公开收藏初始为空）</label></p>';
    echo '<p><label><input type="checkbox" name="configure_reading" value="1"> 将「首页」和「笔记」设为 WordPress 的首页与文章页面</label></p>';
    echo '<p><label><input type="checkbox" name="example_project" value="1"> 创建一个虚构的项目示例草稿，供我自行编辑和发布</label></p>';
    submit_button('创建缺少的页面');
    echo '</form>';
}

/** Public helper for authorized admin tools; existing records are never overwritten. */
function float_space_setup_pages($include_loadouts = false, $configure_reading = false, $example_project = false) {
    if (!current_user_can('manage_options')) return new WP_Error('float_setup_forbidden', '无权设置页面。', array('status' => 403));
    $definitions = array(
        'home' => array('zh' => '首页', 'en' => 'Home'),
        'notes' => array('zh' => '笔记', 'en' => 'Notes'),
    );
    if ($include_loadouts) $definitions['loadouts'] = array('zh' => '示例配置收藏', 'en' => 'Demo loadouts');
    $ids = array(); $created = 0;
    foreach ($definitions as $slug => $title) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        if ($existing) { $ids[$slug] = $existing->ID; continue; }
        $meta = array('_float_title_en' => $title['en']);
        if ($slug === 'loadouts' && is_file(get_template_directory() . '/page-loadouts.php')) $meta['_wp_page_template'] = 'page-loadouts.php';
        $id = wp_insert_post(wp_slash(array(
            'post_type' => 'page', 'post_status' => 'publish',
            'post_name' => $slug, 'post_title' => $title['zh'],
            'post_content' => '', 'post_author' => get_current_user_id(),
            'meta_input' => $meta,
        )), true);
        if (is_wp_error($id)) return $id;
        $ids[$slug] = $id; $created++;
    }
    $reading_changed = false;
    // Do not expose an existing private/draft page by making it the front page.
    if ($configure_reading && get_post_status($ids['home']) === 'publish' && get_post_status($ids['notes']) === 'publish') {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $ids['home']);
        update_option('page_for_posts', $ids['notes']);
        $reading_changed = true;
    }
    $example_created = false;
    if ($example_project) {
        $examples = get_posts(array(
            'post_type' => 'float_project',
            'post_status' => array('publish', 'draft', 'pending', 'private', 'future', 'trash'),
            'meta_key' => '_float_example_id', 'meta_value' => 'demo-project-v1',
            'numberposts' => 1, 'fields' => 'ids', 'suppress_filters' => true,
        ));
        if (!$examples) {
            $id = wp_insert_post(wp_slash(array(
                'post_type' => 'float_project', 'post_status' => 'draft',
                'post_title' => '示例项目', 'post_name' => 'demo-project',
                'post_excerpt' => '这是虚构的示例草稿。请替换成你自己的项目资料后再发布。',
                'post_content' => '<p>在这里介绍你自己的项目、设计目标和完成过程。</p>',
                'post_author' => get_current_user_id(),
                'meta_input' => array(
                    '_float_example_id' => 'demo-project-v1',
                    '_float_title_en' => 'Demo project',
                    '_float_excerpt_en' => 'A fictional draft. Replace it with your own project details before publishing.',
                    '_float_content_en' => '<p>Describe your own project, its goals, and the work behind it.</p>',
                    '_float_number' => '01', '_float_short' => '示例', '_float_short_en' => 'Demo',
                    '_float_status' => '草稿', '_float_status_en' => 'Draft',
                    '_float_stack' => "HTML\nCSS\nJavaScript", '_float_demo' => 'none',
                ),
            )), true);
            if (is_wp_error($id)) return $id;
            $example_created = true;
        }
    }
    return array('pages' => $ids, 'created' => $created, 'readingChanged' => $reading_changed, 'exampleCreated' => $example_created);
}

add_action('admin_post_float_setup_pages', function () {
    if (!current_user_can('manage_options')) wp_die('无权设置页面。', '', array('response' => 403));
    check_admin_referer('float_setup_pages');
    $result = float_space_setup_pages(
        ($_POST['include_loadouts'] ?? '') === '1',
        ($_POST['configure_reading'] ?? '') === '1',
        ($_POST['example_project'] ?? '') === '1'
    );
    if (is_wp_error($result)) wp_die(esc_html($result->get_error_message()));
    $message = '已创建 ' . $result['created'] . ' 个缺少的页面。已有页面保持原样。';
    if ($result['readingChanged']) $message .= ' 首页与文章页面设置已更新。';
    if ($result['exampleCreated']) $message .= ' 已创建一个项目示例草稿，请自行审核后发布。';
    float_space_notice($message, 'success');
    wp_safe_redirect(admin_url('options-general.php?page=float-site'));
    exit;
});
