<?php
/** Run only in an isolated disposable WordPress install; inserts fictional test content. */
require_once '/wordpress/wp-load.php';

$float_checks = 0;
function float_test($condition, $label) {
    global $float_checks;
    if (!$condition) throw new RuntimeException('Integration failed: ' . $label);
    $float_checks++;
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
wp_insert_post(array('post_type' => 'post', 'post_status' => 'publish', 'post_name' => 'demo-note', 'post_title' => '演示笔记', 'post_content' => '<p>记录一个简单的想法。</p>', 'meta_input' => array('_float_title_en' => 'Demo Note', '_float_content_en' => '<p>A simple idea worth writing down.</p>')));
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
$summary = array('passed' => $float_checks, 'wordpress' => get_bloginfo('version'), 'php' => PHP_VERSION);
file_put_contents(ABSPATH . 'wp-content/float-test-results.json', wp_json_encode($summary));
echo wp_json_encode($summary) . "\n";
