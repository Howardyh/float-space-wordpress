<?php
/** Run only in an isolated disposable WordPress install; inserts fictional test content. */
require_once '/wordpress/wp-load.php';

$float_checks = 0;
function float_test($condition, $label) {
    global $float_checks;
    if (!$condition) throw new RuntimeException('Integration failed: ' . $label);
    $float_checks++;
}

// Check the running interpreter, not the versions printed by the CLI banner.
if (defined('FLOAT_TEST_EXPECTED_PHP') || defined('FLOAT_TEST_EXPECTED_WP')) {
    float_test(defined('FLOAT_TEST_EXPECTED_PHP') && defined('FLOAT_TEST_EXPECTED_WP'), 'runtime expectations configured together');
    float_test(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION === FLOAT_TEST_EXPECTED_PHP, 'actual PHP version matches requested ' . FLOAT_TEST_EXPECTED_PHP . ' (running ' . PHP_VERSION . ')');
    if (FLOAT_TEST_EXPECTED_WP !== 'latest') {
        float_test((bool) preg_match('/^' . preg_quote(FLOAT_TEST_EXPECTED_WP, '/') . '(?:\\.|$)/', get_bloginfo('version')), 'actual WordPress version matches requested ' . FLOAT_TEST_EXPECTED_WP . ' (running ' . get_bloginfo('version') . ')');
    }
}

function float_test_navigation_html($location = 'primary') {
    ob_start();
    float_theme_nav($location);
    return ob_get_clean();
}

function float_test_navigation_semantics($html, $expected_submenus, $label) {
    float_test(strpos($html, '<ul') === 0 && strpos($html, '<li') !== false, $label . ': native list structure');
    float_test(substr_count($html, '<ul') === substr_count($html, '</ul>') && substr_count($html, '<li') === substr_count($html, '</li>'), $label . ': balanced lists');
    float_test(!preg_match('/role=["\'](?:menu|menuitem)["\']/', $html), $label . ': navigation does not impersonate an application menu');
    $processor = new WP_HTML_Tag_Processor($html);
    $controls = array();
    while ($processor->next_tag(array('tag_name' => 'BUTTON'))) {
        $id = $processor->get_attribute('aria-controls');
        float_test(is_string($id) && $id !== '' && $processor->get_attribute('type') === 'button' && $processor->get_attribute('aria-expanded') === 'false', $label . ': disclosure has native button state');
        $controls[] = $id;
        float_test(substr_count($html, 'id="' . esc_attr($id) . '"') === 1, $label . ': disclosure references one submenu');
    }
    float_test(count($controls) === $expected_submenus && count(array_unique($controls)) === count($controls), $label . ': unique submenu controls');
}

function float_test_navigation_fields($menu_id, $item_id, $values, $nonce = null) {
    $_POST = array('float_nav_nonce' => array($item_id => $nonce === null ? wp_create_nonce('float_save_navigation_' . $item_id) : $nonce));
    foreach ($values as $key => $value) $_POST['float_nav_' . $key] = array($item_id => wp_slash($value));
    do_action('wp_update_nav_menu_item', $menu_id, $item_id, array());
    $_POST = array();
}

$admins = get_users(array('role' => 'administrator', 'number' => 1));
float_test(count($admins) === 1, 'isolated admin available');
float_test(post_type_exists('float_project'), 'project type registered');
float_test(post_type_exists('float_loadout'), 'collection type registered');
float_test((int) wp_count_posts('float_project')->publish === 0 && (int) wp_count_posts('float_project')->draft === 0, 'activation imports no projects');
float_test((int) wp_count_posts('float_loadout')->publish === 0, 'activation publishes no collection');
float_test(float_site_link('githubUrl') === '' && float_site_link('contactEmail') === '', 'contact defaults blank');
float_test(float_site_footer_value('icpLabel') === '' && float_site_footer_value('policeLabel') === '', 'registration defaults blank');
float_test(get_page_by_path('home') === null && get_page_by_path('notes') === null, 'activation creates no pages');

wp_set_current_user(0);
float_test(is_wp_error(float_space_setup_pages(true, true, true)), 'anonymous setup denied');
float_test(get_page_by_path('home') === null, 'denied setup makes no content');
wp_set_current_user($admins[0]->ID);
$initial_name = get_option('blogname');
$initial_users = count_users()['total_users'];
$setup = float_space_setup_pages(true, true, true);
float_test(!is_wp_error($setup) && $setup['created'] === 3, 'explicit page setup');
float_test(get_option('show_on_front') === 'page' && (int) get_option('page_for_posts') === $setup['pages']['notes'], 'reading setup explicitly applied');
float_test(get_option('blogname') === $initial_name && count_users()['total_users'] === $initial_users, 'setup preserves site identity and users');
float_test((int) wp_count_posts('float_project')->draft === 1 && (int) wp_count_posts('float_project')->publish === 0, 'example project remains draft');
$again = float_space_setup_pages(true, true, true);
float_test($again['created'] === 0 && !$again['exampleCreated'], 'setup idempotent');

$example = get_posts(array('post_type' => 'float_project', 'post_status' => 'draft', 'numberposts' => 1))[0];
$_POST = array('_float_title_en' => 'Unauthorized replacement');
wp_update_post(array('ID' => $example->ID, 'post_title' => $example->post_title));
float_test(get_post_meta($example->ID, '_float_title_en', true) === 'Demo project', 'missing nonce blocks metadata writes');
$_POST = array('float_content_nonce' => wp_create_nonce('float_save_content'), '_float_title_en' => 'Demo workspace', '_float_content_en' => '<p>Safe content</p><script>void(0)</script>');
wp_update_post(array('ID' => $example->ID, 'post_title' => '演示工作区'));
float_test(get_post_meta($example->ID, '_float_title_en', true) === 'Demo workspace', 'authorized bilingual metadata save');
float_test(strpos(get_post_meta($example->ID, '_float_content_en', true), '<script') === false, 'stored rich text sanitized');
$_POST = array();

$valid = array('name' => '虚构配置', 'code' => 'DEMO-ONLY-001', 'weaponId' => 'demo-rifle', 'mode' => 'operations', 'attachments' => array(array('id' => 'demo-optic', 'name' => '示例瞄具', 'slot' => 'optic')));
float_test(!is_wp_error(float_space_normalize_loadout($valid)), 'fictional catalog validates');
float_test(is_wp_error(float_space_normalize_loadout(array_merge($valid, array('code' => "bad\ncode")))), 'multiline codes denied');
float_test(is_wp_error(float_space_normalize_loadout(array_merge($valid, array('mode' => 'unknown')))), 'unknown mode denied');
$invalid = wp_insert_post(array('post_type' => 'float_loadout', 'post_title' => '不完整配置', 'post_status' => 'publish'));
float_test(get_post_status($invalid) === 'draft', 'invalid direct publication becomes draft');
$draft = wp_insert_post(array('post_type' => 'float_loadout', 'post_title' => $valid['name'], 'post_status' => 'draft', 'meta_input' => array('_float_loadout_data' => $valid)));
float_test(count(float_space_public_loadouts()['builds']) === 0, 'draft collection private');
wp_update_post(array('ID' => $draft, 'post_status' => 'publish'));
float_test(get_post_status($draft) === 'publish' && count(float_space_public_loadouts()['builds']) === 1, 'valid publication appears');
$duplicate = wp_insert_post(array('post_type' => 'float_loadout', 'post_title' => '重复配置', 'post_status' => 'draft', 'meta_input' => array('_float_loadout_data' => $valid)));
wp_update_post(array('ID' => $duplicate, 'post_status' => 'publish'));
float_test(get_post_status($duplicate) === 'draft', 'duplicate direct publication becomes draft');
wp_update_post(array('ID' => $draft, 'post_password' => wp_generate_password(24, false)));
float_test(count(float_space_public_loadouts()['builds']) === 0, 'password protected collection private');
wp_update_post(array('ID' => $draft, 'post_password' => '', 'post_status' => 'draft'));

wp_set_current_user(0);
$get = rest_do_request(new WP_REST_Request('GET', '/float-space/v1/loadouts'));
float_test($get->get_status() === 200 && $get->get_data() === array('version' => 1, 'builds' => array()), 'anonymous read returns empty public schema');
$post = rest_do_request(new WP_REST_Request('POST', '/float-space/v1/loadouts'));
float_test($post->get_status() >= 400, 'anonymous API write denied');
wp_set_current_user($admins[0]->ID);

// Fictional UI fixtures exist only in this disposable test install.
foreach (array('monitor' => array('演示监控屏', 'Demo Monitor'), 'schedule' => array('演示课表', 'Demo Schedule')) as $kind => $titles) {
    wp_insert_post(array('post_type' => 'float_project', 'post_status' => 'publish', 'post_name' => 'demo-' . $kind, 'post_title' => $titles[0], 'post_excerpt' => '这是用于展示界面的虚构项目。', 'post_content' => '<p>这个项目只用于测试主题布局。</p>', 'meta_input' => array('_float_title_en' => $titles[1], '_float_excerpt_en' => 'A fictional project for theme layout testing.', '_float_content_en' => '<p>This project tests the theme layout.</p>', '_float_number' => $kind === 'monitor' ? '01' : '02', '_float_short' => $titles[0], '_float_short_en' => $titles[1], '_float_demo' => $kind, '_float_stack' => "HTML\nCSS\nJavaScript")));
}
$native_note = wp_insert_post(array('post_type' => 'post', 'post_status' => 'publish', 'post_name' => 'demo-note', 'post_title' => '演示笔记', 'post_content' => '<p>记录一个简单的想法。</p>', 'meta_input' => array('_float_title_en' => 'Demo Note', '_float_content_en' => '<p>A simple idea worth writing down.</p>')));
$saved_posts_page = get_option('page_for_posts');
$saved_front_mode = get_option('show_on_front');
$saved_global_post = $GLOBALS['post'] ?? null;
update_option('page_for_posts', 0);
update_option('show_on_front', 'posts');
$GLOBALS['post'] = get_post($native_note);
float_test(float_theme_notes_url() === home_url('/'), 'native blog URL does not inherit current loop post');
update_option('page_for_posts', $saved_posts_page);
update_option('show_on_front', $saved_front_mode);
$GLOBALS['post'] = $saved_global_post;
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();

// Navigation fixtures and writes below target only this disposable installation.
$navigation_checks_start = $float_checks;
float_test(function_exists('float_navigation_defaults') && class_exists('Float_Space_Menu_Walker'), 'navigation module loaded');
$saved_locations = get_theme_mod('nav_menu_locations', array());
$saved_query = $GLOBALS['wp_query'];
$saved_main_query = $GLOBALS['wp_the_query'];
$saved_navigation_post = $GLOBALS['post'] ?? null;
set_theme_mod('nav_menu_locations', array());
$published_projects = get_posts(array('post_type' => 'float_project', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC'));
float_test(count($published_projects) === 2, 'fictional public projects available for navigation');
$project_id = $published_projects[0]->ID;
$excluded_projects = array();
foreach (array('draft', 'private', 'password') as $visibility) {
    $excluded_projects[] = wp_insert_post(array(
        'post_type' => 'float_project',
        'post_status' => $visibility === 'password' ? 'publish' : $visibility,
        'post_password' => $visibility === 'password' ? wp_generate_password(24, false) : '',
        'post_name' => 'nav-fixture-' . $visibility,
        'post_title' => 'NAV-FIXTURE-' . strtoupper($visibility) . '-EXCLUDED',
        'post_excerpt' => 'NAV-FIXTURE-EXCERPT-' . strtoupper($visibility),
        'meta_input' => array('_float_title_en' => 'NAV-FIXTURE-EN-' . strtoupper($visibility)),
    ));
}
$default_html = float_test_navigation_html();
float_test_navigation_semantics($default_html, 1, 'default navigation');
foreach ($published_projects as $project) {
    float_test(strpos($default_html, esc_url(get_permalink($project))) !== false && strpos($default_html, esc_html($project->post_title)) !== false, 'published project linked with actual title');
    float_test(strpos($default_html, esc_attr(get_post_meta($project->ID, '_float_title_en', true))) !== false, 'published project English label available');
}
foreach ($excluded_projects as $excluded_id) {
    $excluded = get_post($excluded_id);
    float_test(strpos($default_html, $excluded->post_title) === false && strpos($default_html, $excluded->post_excerpt) === false && strpos($default_html, get_post_meta($excluded_id, '_float_title_en', true)) === false && strpos($default_html, esc_url(get_permalink($excluded_id))) === false, 'nonpublic project does not leak through automatic navigation');
}
$default_items = float_navigation_defaults();
$project_parents = array_values(array_filter($default_items, function ($item) { return ($item['en'] ?? '') === 'Projects'; }));
float_test(count($project_parents) === 1 && count($project_parents[0]['children']) === 3, 'default project branch includes public projects and view-all link');
float_test(strpos($default_html, 'data-wp-en="View all projects"') !== false && strpos($default_html, 'class="nav-description"') !== false, 'default project branch includes bilingual descriptions');
$default_footer = float_test_navigation_html('footer');
float_test_navigation_semantics($default_footer, 0, 'default footer');
float_test(strpos($default_footer, 'class="footer-menu"') !== false && strpos($default_footer, 'nav-submenu') === false && strpos($default_footer, 'nav-row') === false, 'default footer remains a flat link list');

try {
    $GLOBALS['wp_query'] = new WP_Query(array('p' => $project_id, 'post_type' => 'float_project', 'post_status' => 'publish'));
    $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
    $GLOBALS['post'] = get_post($project_id);
    $detail_html = float_test_navigation_html();
    float_test(strpos($detail_html, 'nav-item has-children is-ancestor') !== false, 'default projects parent highlighted on project detail');
    float_test(preg_match('~<a[^>]*href="' . preg_quote(esc_url(get_permalink($project_id)), '~') . '"[^>]*aria-current="page"~', $detail_html) === 1, 'project detail marks the current child link');
    $GLOBALS['wp_query'] = new WP_Query(array('post_type' => 'float_project'));
    $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
    $archive_html = float_test_navigation_html();
    float_test(strpos($archive_html, 'nav-item has-children is-active') !== false, 'default projects parent highlighted on archive');

    // Save hooks receive the same per-item fields and nonce as the menu editor.
    $normal_menu = wp_create_nav_menu('Fictional navigation QA');
    float_test(!is_wp_error($normal_menu), 'fictional custom menu created');
    $parent_item = wp_update_nav_menu_item($normal_menu, 0, array('menu-item-title' => '项目', 'menu-item-type' => 'post_type_archive', 'menu-item-object' => 'float_project', 'menu-item-status' => 'publish'));
    $child_item = wp_update_nav_menu_item($normal_menu, 0, array('menu-item-title' => '演示监控屏', 'menu-item-type' => 'post_type', 'menu-item-object' => 'float_project', 'menu-item-object-id' => $project_id, 'menu-item-parent-id' => $parent_item, 'menu-item-status' => 'publish'));
    $second_child = wp_update_nav_menu_item($normal_menu, 0, array('menu-item-title' => '演示课表', 'menu-item-type' => 'post_type', 'menu-item-object' => 'float_project', 'menu-item-object-id' => $published_projects[1]->ID, 'menu-item-parent-id' => $parent_item, 'menu-item-status' => 'publish'));
    $grandchild_item = wp_update_nav_menu_item($normal_menu, 0, array('menu-item-title' => '三级示例', 'menu-item-url' => home_url('/#main'), 'menu-item-type' => 'custom', 'menu-item-parent-id' => $child_item, 'menu-item-status' => 'publish'));
    $notes_item = wp_update_nav_menu_item($normal_menu, 0, array('menu-item-title' => '笔记', 'menu-item-url' => float_theme_notes_url(), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish'));
    $about_item = wp_update_nav_menu_item($normal_menu, 0, array('menu-item-title' => '关于', 'menu-item-url' => home_url('/#about'), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish'));
    float_test(!is_wp_error($parent_item) && !is_wp_error($child_item) && !is_wp_error($second_child) && !is_wp_error($grandchild_item) && !is_wp_error($notes_item) && !is_wp_error($about_item), 'parent child and third-level menu items created');

    float_test_navigation_fields($normal_menu, $child_item, array(
        'title_en' => "Example 'quoted' <b>menu</b><script>discard()</script>",
        'description_zh' => "<strong>中文简介</strong>\n 第二行",
        'description_en' => str_repeat('N', 230),
    ));
    float_test(get_post_meta($child_item, '_float_nav_title_en', true) === "Example 'quoted' menu", 'authorized menu English field is unslashed and HTML sanitized');
    float_test(get_post_meta($child_item, '_float_nav_description_zh', true) === '中文简介 第二行', 'menu description is plain single-line text');
    float_test(strlen(get_post_meta($child_item, '_float_nav_description_en', true)) === 200, 'menu description storage is bounded');
    $baseline_title = get_post_meta($child_item, '_float_nav_title_en', true);
    $_POST = array('float_nav_title_en' => array($child_item => 'No nonce replacement'));
    do_action('wp_update_nav_menu_item', $normal_menu, $child_item, array());
    $_POST = array();
    float_test(get_post_meta($child_item, '_float_nav_title_en', true) === $baseline_title && strlen(get_post_meta($child_item, '_float_nav_description_en', true)) === 200, 'missing menu nonce preserves every existing field');
    float_test_navigation_fields($normal_menu, $child_item, array('title_en' => 'Wrong item replacement'), wp_create_nonce('float_save_navigation_' . $parent_item));
    float_test(get_post_meta($child_item, '_float_nav_title_en', true) === $baseline_title, 'nonce is bound to the menu item');
    float_test_navigation_fields($normal_menu, $child_item, array('title_en' => 'Malformed nonce replacement'), array('invalid'));
    float_test(get_post_meta($child_item, '_float_nav_title_en', true) === $baseline_title, 'malformed menu nonce is rejected');

    $subscriber_id = wp_insert_user(array('user_login' => 'float-navigation-test-subscriber', 'user_email' => 'navigation-test@example.org', 'user_pass' => wp_generate_password(32, true), 'role' => 'subscriber'));
    float_test(!is_wp_error($subscriber_id), 'fictional low-privilege user created');
    wp_set_current_user($subscriber_id);
    $subscriber_nonce = wp_create_nonce('float_save_navigation_' . $child_item);
    float_test(wp_verify_nonce($subscriber_nonce, 'float_save_navigation_' . $child_item) !== false && !current_user_can('edit_theme_options'), 'subscriber test uses a valid nonce without menu-edit capability');
    float_test_navigation_fields($normal_menu, $child_item, array('title_en' => 'Subscriber replacement'), $subscriber_nonce);
    float_test(get_post_meta($child_item, '_float_nav_title_en', true) === $baseline_title, 'valid nonce cannot bypass menu capability');
    wp_set_current_user($admins[0]->ID);
    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user($subscriber_id);
    float_test(get_userdata($subscriber_id) === false, 'fictional low-privilege user removed');

    update_post_meta($native_note, '_float_nav_title_en', 'Non-menu sentinel');
    float_test_navigation_fields($normal_menu, $native_note, array('title_en' => 'Non-menu replacement'));
    float_test(get_post_meta($native_note, '_float_nav_title_en', true) === 'Non-menu sentinel', 'navigation save hook cannot write other post types');
    delete_post_meta($native_note, '_float_nav_title_en');
    float_test_navigation_fields($normal_menu, $child_item, array('title_en' => '', 'description_zh' => '', 'description_en' => ''));
    float_test(!metadata_exists('post', $child_item, '_float_nav_title_en') && !metadata_exists('post', $child_item, '_float_nav_description_zh') && !metadata_exists('post', $child_item, '_float_nav_description_en'), 'blank menu fields delete stored metadata');

    float_test_navigation_fields($normal_menu, $parent_item, array('title_en' => 'Projects', 'description_zh' => '', 'description_en' => ''));
    float_test_navigation_fields($normal_menu, $child_item, array('title_en' => 'Demo Monitor', 'description_zh' => '虚构监控项目的菜单说明', 'description_en' => 'A fictional monitor project for navigation testing.'));
    float_test_navigation_fields($normal_menu, $second_child, array('title_en' => 'Demo Schedule', 'description_zh' => '虚构课表示例', 'description_en' => 'A fictional schedule example.'));
    float_test_navigation_fields($normal_menu, $grandchild_item, array('title_en' => 'Third-level example', 'description_zh' => '用于检查递归列表与键盘操作', 'description_en' => 'Checks recursive lists and keyboard disclosure.'));
    ob_start();
    do_action('wp_nav_menu_item_custom_fields', $child_item, wp_setup_nav_menu_item(get_post($child_item)), 0, null, 0);
    $editor_html = ob_get_clean();
    float_test(strpos($editor_html, 'name="float_nav_nonce[' . $child_item . ']"') !== false && strpos($editor_html, 'name="float_nav_title_en[' . $child_item . ']"') !== false, 'menu editor includes item nonce and bilingual field');
    float_test(strpos($editor_html, 'value="Demo Monitor"') !== false && substr_count($editor_html, 'maxlength="200"') === 3, 'menu editor displays saved values and bounds all fields');

    set_theme_mod('nav_menu_locations', array('primary' => $normal_menu, 'footer' => $normal_menu));
    $GLOBALS['wp_query'] = new WP_Query(array('p' => $project_id, 'post_type' => 'float_project', 'post_status' => 'publish'));
    $GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
    $GLOBALS['post'] = get_post($project_id);
    $custom_html = float_test_navigation_html();
    float_test_navigation_semantics($custom_html, 2, 'custom three-level navigation');
    float_test(strpos($custom_html, 'float-primary-item-' . $grandchild_item) !== false && strpos($custom_html, 'data-wp-en="Third-level example"') !== false, 'custom navigation preserves the third level and English label');
    float_test(strpos($custom_html, 'data-wp-zh="虚构监控项目的菜单说明"') !== false && strpos($custom_html, 'data-wp-en="A fictional monitor project for navigation testing."') !== false, 'custom submenu descriptions are bilingual');
    float_test(preg_match('~<li id="float-primary-item-' . $parent_item . '" class="[^"]*is-ancestor~', $custom_html) === 1, 'custom project archive parent highlighted on project detail');
    float_test(preg_match('~<li id="float-primary-item-' . $child_item . '" class="[^"]*is-active~', $custom_html) === 1 && preg_match('~<a[^>]*href="' . preg_quote(esc_url(get_permalink($project_id)), '~') . '"[^>]*aria-current="page"~', $custom_html) === 1, 'custom current project item marked for assistive technology');
    $custom_footer = float_test_navigation_html('footer');
    float_test_navigation_semantics($custom_footer, 0, 'custom footer');
    float_test(strpos($custom_footer, 'float-footer-item-' . $parent_item) !== false && strpos($custom_footer, 'float-footer-item-' . $child_item) === false && strpos($custom_footer, 'float-footer-item-' . $grandchild_item) === false, 'custom footer renders only top-level links');
    float_test(strpos($custom_footer, 'nav-submenu') === false && strpos($custom_footer, 'nav-description') === false && strpos($custom_footer, 'nav-row') === false, 'custom footer remains flat without disclosure controls');

    // Keep unassigned long-label fixtures available for isolated browser QA.
    $stress_menu = wp_create_nav_menu('Fictional navigation overflow QA');
    float_test(!is_wp_error($stress_menu), 'fictional long-menu fixture created');
    $stress_items = array();
    for ($index = 1; $index <= 12; $index++) {
        $stress_item = wp_update_nav_menu_item($stress_menu, 0, array('menu-item-title' => '布局压力示例栏目 ' . $index, 'menu-item-url' => home_url('/#main'), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish'));
        float_test(!is_wp_error($stress_item), 'long-menu top-level fixture created');
        $stress_items[] = $stress_item;
        float_test_navigation_fields($stress_menu, $stress_item, array('title_en' => 'A deliberately long navigation label for layout example ' . $index, 'description_zh' => '', 'description_en' => ''));
        if ($index === 1) {
            for ($child_index = 1; $child_index <= 18; $child_index++) {
                $stress_child = wp_update_nav_menu_item($stress_menu, 0, array('menu-item-title' => '滚动面板示例 ' . $child_index, 'menu-item-url' => home_url('/#main'), 'menu-item-type' => 'custom', 'menu-item-parent-id' => $stress_item, 'menu-item-status' => 'publish'));
                float_test(!is_wp_error($stress_child), 'scroll-panel child fixture created');
                float_test_navigation_fields($stress_menu, $stress_child, array('title_en' => 'Scrollable panel example ' . $child_index, 'description_zh' => '用于检查小屏换行和独立滚动的虚构说明。', 'description_en' => 'Fictional description for checking wrapping and independent scrolling.'));
            }
        }
    }
    set_theme_mod('nav_menu_locations', array('primary' => $stress_menu));
    $stress_html = float_test_navigation_html();
    float_test_navigation_semantics($stress_html, 1, 'long-label navigation fixture');
    float_test(strpos($stress_html, 'A deliberately long navigation label for layout example 12') !== false && strpos($stress_html, 'Scrollable panel example 18') !== false, 'stress fixture includes long labels and enough children to scroll');
} finally {
    $_POST = array();
    wp_set_current_user($admins[0]->ID);
    set_theme_mod('nav_menu_locations', $saved_locations);
    $GLOBALS['wp_query'] = $saved_query;
    $GLOBALS['wp_the_query'] = $saved_main_query;
    $GLOBALS['post'] = $saved_navigation_post;
}
float_test(get_theme_mod('nav_menu_locations', array()) === $saved_locations, 'navigation tests restore original menu assignments');
foreach ($excluded_projects as $excluded_id) wp_delete_post($excluded_id, true);
float_test(count(float_theme_projects()) === 2 && count_users()['total_users'] === $initial_users, 'navigation tests remove privacy fixtures and preserve users');
$navigation_fixtures = array('normalMenuId' => $normal_menu, 'stressMenuId' => $stress_menu, 'parentItemId' => $parent_item, 'childItemId' => $child_item, 'grandchildItemId' => $grandchild_item, 'stressRootIds' => $stress_items, 'defaultAssignments' => $saved_locations, 'note' => 'Fictional menus are unassigned; use only in this disposable installation.');
$summary = array('passed' => $float_checks, 'navigationPassed' => $float_checks - $navigation_checks_start, 'navigationFixtures' => $navigation_fixtures, 'wordpress' => get_bloginfo('version'), 'php' => PHP_VERSION);
file_put_contents(ABSPATH . 'wp-content/float-test-results.json', wp_json_encode($summary));
echo wp_json_encode($summary) . "\n";
