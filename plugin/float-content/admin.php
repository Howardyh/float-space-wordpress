<?php
defined('ABSPATH') || exit;

add_action('add_meta_boxes', function () {
    foreach (array('post', 'float_project') as $type) add_meta_box('float_english', '英文内容', 'float_space_english_box', $type, 'normal', 'high');
    add_meta_box('float_project_details', '项目资料', 'float_space_project_box', 'float_project', 'normal', 'high');
    add_meta_box('float_loadout_details', '配置资料', 'float_space_loadout_box', 'float_loadout', 'normal', 'high');
});

function float_space_field($name, $label, $value, $type = 'text', $max = 400) {
    echo '<p><label for="' . esc_attr($name) . '"><strong>' . esc_html($label) . '</strong></label><br>';
    if ($type === 'textarea') echo '<textarea class="large-text" rows="4" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" maxlength="' . (int) $max . '">' . esc_textarea($value) . '</textarea>';
    else echo '<input class="widefat" type="' . esc_attr($type) . '" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" maxlength="' . (int) $max . '">';
    echo '</p>';
}

function float_space_english_box($post) {
    wp_nonce_field('float_save_content', 'float_content_nonce');
    float_space_field('_float_title_en', '英文标题', get_post_meta($post->ID, '_float_title_en', true), 'text', 200);
    float_space_field('_float_excerpt_en', '英文摘要', get_post_meta($post->ID, '_float_excerpt_en', true), 'textarea', 2000);
    wp_editor(get_post_meta($post->ID, '_float_content_en', true), 'float_content_en', array('textarea_name' => '_float_content_en', 'textarea_rows' => 12, 'media_buttons' => true));
    echo '<p class="description">中文内容使用上方 WordPress 编辑器；英文留空时前台会显示中文内容。</p>';
}

function float_space_project_box($post) {
    foreach (float_space_project_fields() as $key => $field) {
        $value = get_post_meta($post->ID, $key, true);
        if ($field[1] === 'demo') {
            echo '<p><label><strong>互动示意</strong><br><select name="_float_demo">';
            foreach (array('none' => '不显示', 'schedule' => '课表示意', 'monitor' => '服务器监控示意') as $demo => $label) echo '<option value="' . esc_attr($demo) . '" ' . selected($value, $demo, false) . '>' . esc_html($label) . '</option>';
            echo '</select></label></p>';
        } else float_space_field($key, $field[0], $value, $field[1], $key === '_float_stack' ? 2000 : 400);
    }
}

function float_space_loadout_box($post) {
    wp_nonce_field('float_save_loadout', 'float_loadout_nonce');
    $data = get_post_meta($post->ID, '_float_loadout_data', true);
    $data = is_array($data) ? $data : array();
    echo '<p>配置名称使用上方标题。保存为草稿后可先预览，点击 WordPress 的发布按钮才会进入公开收藏。</p>';
    echo '<p><label for="float_weapon"><strong>武器</strong></label><br><select id="float_weapon" name="float_loadout[weaponId]"><option value="">其他 / 暂未收录</option>';
    foreach (float_space_catalog()['weapons'] as $weapon) echo '<option value="' . esc_attr($weapon['id']) . '" ' . selected($data['weaponId'] ?? '', $weapon['id'], false) . '>' . esc_html($weapon['name']) . '</option>';
    echo '</select></p>';
    float_space_field('float_loadout[weaponName]', '未收录的武器名称', $data['weaponName'] ?? '', 'text', 80);
    echo '<p><label><strong>配置模式</strong><br><select name="float_loadout[mode]"><option value="operations" ' . selected($data['mode'] ?? '', 'operations', false) . '>模式 A</option><option value="warfare" ' . selected($data['mode'] ?? '', 'warfare', false) . '>模式 B</option></select></label></p>';
    foreach (array('code' => array('配置代码', 512), 'season' => array('版本', 40), 'notes' => array('配置说明', 1200), 'nameEn' => array('配置名称（英文，可选）', 80), 'weaponNameEn' => array('武器名称（英文，可选）', 80), 'seasonEn' => array('版本（英文，可选）', 40), 'notesEn' => array('配置说明（英文，可选）', 1200)) as $key => $field) float_space_field('float_loadout[' . $key . ']', $field[0], $data[$key] ?? '', str_starts_with($key, 'notes') ? 'textarea' : 'text', $field[1]);
    echo '<h3>配件</h3><div id="float-parts-editor"><p class="description">目录仅含虚构的演示条目。未收录的配件可以填写名称；最多 32 个。</p><div data-float-parts></div><p><label>配件分类 <select data-float-slot>';
    foreach (float_space_catalog()['slots'] as $slot) echo '<option value="' . esc_attr($slot['id']) . '">' . esc_html($slot['name']) . '</option>';
    echo '</select></label> <label>搜索配件 <input type="search" data-float-search maxlength="80"></label></p><p><select data-float-part aria-label="选择配件"></select> <button type="button" class="button" data-float-add>添加配件</button></p><p><label>未收录配件名称 <input type="text" data-float-custom maxlength="80"></label> <button type="button" class="button" data-float-add-custom>添加未收录配件</button></p><p data-float-error role="alert" style="color:#b32d2e"></p></div>';
    echo '<textarea hidden id="float_attachments" name="float_attachments">' . esc_textarea(wp_json_encode($data['attachments'] ?? array(), JSON_UNESCAPED_UNICODE)) . '</textarea>';
    echo '<noscript><p>配件选择需要 JavaScript。已有配件会保留，其他资料仍可编辑。</p></noscript>';
    if ($post->ID && $post->post_status !== 'auto-draft') {
        $url = wp_nonce_url(admin_url('admin-post.php?action=float_preview_loadout&post_id=' . $post->ID), 'float_preview_' . $post->ID);
        echo '<p><a class="button" target="_blank" rel="noopener" href="' . esc_url($url) . '">预览已保存的配置</a></p>';
    }
}

add_action('admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'float_loadout' || $screen->base !== 'post') return;
    wp_enqueue_script('float-loadout-admin', plugins_url('assets/admin.js', __FILE__), array(), '1.0.0', true);
    wp_add_inline_script('float-loadout-admin', 'window.FloatContentCatalog = ' . wp_json_encode(float_space_catalog(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';', 'before');
});

function float_space_notice($message, $kind = 'error') {
    $user_id = get_current_user_id();
    $key = $user_id ? 'float_content_notice_' . $user_id : 'float_content_notice_admin';
    set_transient($key, array('message' => $message, 'kind' => $kind), $user_id ? 90 : 7 * DAY_IN_SECONDS);
}
add_action('admin_notices', function () {
    $key = 'float_content_notice_' . get_current_user_id(); $notice = get_transient($key);
    if (!$notice && current_user_can('manage_options')) { $key = 'float_content_notice_admin'; $notice = get_transient($key); }
    if (!$notice || !is_array($notice)) return;
    delete_transient($key);
    echo '<div class="notice notice-' . esc_attr($notice['kind']) . '"><p>' . esc_html($notice['message']) . '</p></div>';
});

add_action('save_post', function ($id, $post) {
    if (wp_is_post_revision($id) || wp_is_post_autosave($id) || !current_user_can('edit_post', $id)) return;
    if (isset($_POST['float_content_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['float_content_nonce'])), 'float_save_content') && in_array($post->post_type, array('post', 'float_project'), true)) {
        update_post_meta($id, '_float_title_en', sanitize_text_field(wp_unslash($_POST['_float_title_en'] ?? '')));
        update_post_meta($id, '_float_excerpt_en', sanitize_textarea_field(wp_unslash($_POST['_float_excerpt_en'] ?? '')));
        update_post_meta($id, '_float_content_en', wp_kses_post(wp_unslash($_POST['_float_content_en'] ?? '')));
        if ($post->post_type === 'float_project' && current_user_can('manage_options')) foreach (float_space_project_fields() as $key => $field) {
            $value = wp_unslash($_POST[$key] ?? '');
            if ($key === '_float_demo') $value = in_array($value, array('none', 'schedule', 'monitor'), true) ? $value : 'none';
            elseif ($field[1] === 'url') $value = esc_url_raw($value, array('https', 'http'));
            elseif ($field[1] === 'textarea') $value = sanitize_textarea_field($value);
            else $value = sanitize_text_field($value);
            update_post_meta($id, $key, $value);
        }
    }
    if ($post->post_type !== 'float_loadout' || !current_user_can('manage_options') || !isset($_POST['float_loadout_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['float_loadout_nonce'])), 'float_save_loadout')) return;
    static $saving = false;
    if ($saving) return;
    $raw = wp_unslash($_POST['float_loadout'] ?? array());
    $raw = is_array($raw) ? $raw : array();
    $raw['name'] = $post->post_title;
    $json = wp_unslash($_POST['float_attachments'] ?? '[]');
    $raw['attachments'] = is_string($json) && strlen($json) <= 20000 ? json_decode($json, true) : null;
    $data = float_space_normalize_loadout($raw);
    if (!is_wp_error($data) && $post->post_status === 'publish') {
        foreach (get_posts(array('post_type' => 'float_loadout', 'post_status' => 'publish', 'numberposts' => -1, 'exclude' => array($id), 'fields' => 'ids')) as $other_id) {
            $other = get_post_meta($other_id, '_float_loadout_data', true);
            if (is_array($other) && ($other['code'] ?? '') === $data['code']) { $data = new WP_Error('float_duplicate', '同一配置代码已经公开，请编辑已有配置。'); break; }
        }
        $published_count = (int) wp_count_posts('float_loadout')->publish;
        if ($published_count > 200) $data = new WP_Error('float_limit', '公开收藏最多保留 200 套配置。请先将旧配置改为草稿。');
    }
    if (is_wp_error($data)) {
        // Preserve the last valid fields and keep any attempted invalid publication private.
        if ($post->post_status === 'publish' || $post->post_status === 'future') { $saving = true; wp_update_post(array('ID' => $id, 'post_status' => 'draft')); $saving = false; }
        float_space_notice('配置未更新：' . $data->get_error_message() . ' 已保存的有效配件与资料会保留。');
        return;
    }
    update_post_meta($id, '_float_loadout_data', $data);
}, 10, 2);

/** Apply publication rules to Quick Edit, Bulk Edit, WP-CLI and scheduled posts too. */
add_action('save_post', function ($id) {
    static $checking = false;
    if ($checking || wp_is_post_revision($id) || wp_is_post_autosave($id)) return;
    // Earlier save hooks may have moved an invalid form submission back to draft.
    $post = get_post($id);
    if (!$post || $post->post_type !== 'float_loadout' || !in_array($post->post_status, array('publish', 'future'), true)) return;
    $record = float_space_loadout_record($post);
    $error = is_wp_error($record) ? $record->get_error_message() : '';
    if ($error === '') {
        $reserved = get_posts(array('post_type' => 'float_loadout', 'post_status' => array('publish', 'future'), 'numberposts' => -1, 'fields' => 'ids', 'suppress_filters' => true));
        foreach ($reserved as $other_id) {
            if ((int) $other_id === (int) $id) continue;
            $other = float_space_loadout_record($other_id);
            if (!is_wp_error($other) && $other['code'] === $record['code']) { $error = '同一配置代码已公开或已安排定时发布，请编辑已有配置。'; break; }
        }
        if ($error === '' && count($reserved) > 200) $error = '公开与定时发布的配置合计最多 200 套。请先将旧配置改为草稿。';
    }
    if ($error === '') return;
    $checking = true;
    try { wp_update_post(array('ID' => $id, 'post_status' => 'draft')); }
    finally { $checking = false; }
    // This also works during cron: a visitor still cannot publish or edit any data.
    float_space_notice('配置已退回草稿：' . $error);
}, 20);

function float_space_home_defaults() {
    $path = get_template_directory() . '/data/home-copy.json';
    $defaults = is_file($path) ? json_decode(file_get_contents($path), true) : array();
    return is_array($defaults) ? $defaults : array();
}

add_action('admin_menu', function () {
    add_options_page('FLOAT 网站文字', 'FLOAT 网站文字', 'manage_options', 'float-site', 'float_space_site_page');
    add_submenu_page('edit.php?post_type=float_loadout', '导入本机草稿', '导入本机草稿', 'manage_options', 'float-loadout-import', 'float_space_import_page');
});

function float_space_site_page() {
    if (!current_user_can('manage_options')) wp_die('无权管理网站文字。');
    $defaults = float_space_home_defaults();
    $saved = get_option('float_home_copy', array());
    $saved = is_array($saved) ? $saved : array();
    echo '<div class="wrap"><h1>FLOAT 网站文字</h1><p>首页中文与英文分别维护；空白英文会使用中文。项目内容请在「项目」中编辑，笔记请在「文章」中编辑。</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="float_save_site">';
    wp_nonce_field('float_save_site');
    float_space_field('siteName', '网站名称', get_option('blogname'), 'text', 80);
    float_space_field('githubUrl', 'GitHub 主页地址（留空不显示）', float_site_link('githubUrl'), 'url', 400);
    float_space_field('contactEmail', '联系邮箱（留空不显示）', float_site_link('contactEmail'), 'email', 254);
    echo '<h2>可选页脚信息</h2><p>默认不显示备案信息或个人联系信息。填写前请确认这些资料适合公开。</p>';
    foreach (array('copyrightLabel' => array('版权署名（留空使用网站名称）', 'text'), 'icpLabel' => array('备案显示文字（可选）', 'text'), 'icpUrl' => array('备案链接（可选，HTTPS）', 'url'), 'policeLabel' => array('登记显示文字（可选）', 'text'), 'policeUrl' => array('登记链接（可选，HTTPS）', 'url')) as $key => $field) float_space_field('footer[' . $key . ']', $field[0], float_site_footer_value($key), $field[1], 400);
    $labels = array('heroStatement1' => '首页主标题 · 第一行', 'heroStatement2' => '首页主标题 · 第二行', 'heroSubtitle1' => '首页副标题 · 第一行', 'heroSubtitle2' => '首页副标题 · 第二行', 'heroNote1' => '首页简述 · 第一行', 'heroNote2' => '首页简述 · 第二行', 'aboutLead' => '关于我 · 简介', 'aboutDescription' => '关于我 · 网站介绍', 'aboutExtra1' => '关于我 · 补充段落一', 'aboutExtra2' => '关于我 · 补充段落二', 'aboutExtra3' => '关于我 · 补充段落三', 'aboutQuestionIntro' => '关于我 · 引语介绍', 'aboutQuestion1' => '关于我 · 引语第一行', 'aboutQuestion2' => '关于我 · 引语第二行', 'heroEyebrow' => '首页 · 顶部说明', 'aboutTitle' => '关于我 · 标题', 'featuredTitle1' => '推荐项目标题 · 第一行', 'featuredTitle2' => '推荐项目标题 · 第二行', 'featuredTitle3' => '推荐项目标题 · 第三行');
    foreach ($defaults as $key => $entry) {
        if (!is_array($entry)) continue;
        echo '<h2>' . esc_html($entry['label'] ?? $labels[$key] ?? $key) . '</h2>';
        foreach (array('zh' => '中文', 'en' => 'English') as $language => $label) float_space_field('copy[' . $key . '][' . $language . ']', $label, $saved[$key][$language] ?? $entry[$language] ?? '', 'textarea', 2500);
    }
    submit_button('保存网站文字'); echo '</form>';
    float_space_setup_form();
    echo '</div>';
}

add_action('admin_post_float_save_site', function () {
    if (!current_user_can('manage_options')) wp_die('无权管理网站文字。', '', array('response' => 403));
    check_admin_referer('float_save_site');
    $input = wp_unslash($_POST['copy'] ?? array()); $copy = array();
    if (!is_array($input)) wp_die('网站文字格式不正确。');
    foreach (float_space_home_defaults() as $key => $entry) {
        if (isset($input[$key]) && !is_array($input[$key])) wp_die('网站文字格式不正确。');
        $copy[$key] = array();
        foreach (array('zh', 'en') as $language) {
            $value = float_space_text($input[$key][$language] ?? '', 2500);
            if (is_wp_error($value)) wp_die(esc_html($value->get_error_message()));
            $copy[$key][$language] = sanitize_textarea_field($value);
        }
    }
    $site_name = float_space_text(wp_unslash($_POST['siteName'] ?? ''), 80, true);
    if (is_wp_error($site_name)) wp_die('请填写网站名称。');
    $github_raw = wp_unslash($_POST['githubUrl'] ?? '');
    if (!is_string($github_raw)) wp_die('GitHub 主页地址格式不正确。');
    $github_raw = trim($github_raw); $github = esc_url_raw($github_raw, array('https'));
    if ($github_raw !== '' && ($github === '' || wp_parse_url($github, PHP_URL_HOST) !== 'github.com')) wp_die('GitHub 主页地址需使用 https://github.com/。');
    $email_raw = wp_unslash($_POST['contactEmail'] ?? '');
    if (!is_string($email_raw)) wp_die('联系邮箱格式不正确。');
    $email_raw = trim($email_raw); $email = sanitize_email($email_raw);
    if ($email_raw !== '' && (!$email || !is_email($email))) wp_die('联系邮箱格式不正确。');
    $raw_footer = wp_unslash($_POST['footer'] ?? array()); $footer = array();
    if (!is_array($raw_footer)) wp_die('页脚资料格式不正确。');
    foreach (array('copyrightLabel', 'icpLabel', 'icpUrl', 'policeLabel', 'policeUrl') as $key) {
        $value = float_space_text($raw_footer[$key] ?? '', 400);
        if (is_wp_error($value)) wp_die(esc_html($value->get_error_message()));
        if (str_ends_with($key, 'Url')) {
            $url = esc_url_raw($value, array('https'));
            if ($value !== '' && ($url === '' || wp_parse_url($url, PHP_URL_SCHEME) !== 'https' || !wp_parse_url($url, PHP_URL_HOST))) wp_die('页脚链接需要有效的 HTTPS 地址。');
            $value = $url;
        } else $value = sanitize_text_field($value);
        $footer[$key] = $value;
    }
    update_option('float_home_copy', $copy, false);
    update_option('float_site_links', array('githubUrl' => $github, 'contactEmail' => $email), false);
    update_option('float_site_footer', $footer, false);
    update_option('blogname', sanitize_text_field($site_name));
    float_space_notice('网站文字已保存。', 'success');
    wp_safe_redirect(admin_url('options-general.php?page=float-site')); exit;
});

function float_space_import_page() {
    if (!current_user_can('manage_options')) wp_die('无权导入。');
    echo '<div class="wrap"><h1>导入本机草稿</h1><p>从网站「本机草稿 → 导出配置」取得备份后，在此选择文件或粘贴内容。所有配置均导入为服务器草稿，需要逐套预览并发布。重复 ID 或配置代码会跳过，已有内容不会被覆盖。</p><form method="post" enctype="multipart/form-data" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="float_import_loadouts">';
    wp_nonce_field('float_import_loadouts');
    echo '<p><label>选择导出的 JSON 文件 <input type="file" name="loadout_file" accept="application/json,.json"></label></p><p><label for="loadout_json">或粘贴导出的配置</label></p><textarea id="loadout_json" name="loadout_json" class="large-text code" rows="12" maxlength="524288"></textarea>';
    submit_button('导入为草稿'); echo '</form></div>';
}

function float_space_import_envelope($json) {
    if (!is_string($json) || strlen($json) > 524288) return new WP_Error('float_import', '文件最多 512 KB。');
    $envelope = json_decode($json, true);
    if (!is_array($envelope) || ($envelope['version'] ?? null) !== 1 || !isset($envelope['builds']) || !is_array($envelope['builds']) || !array_is_list($envelope['builds']) || count($envelope['builds']) > 200) return new WP_Error('float_import', '请选择网站导出的有效配置文件，最多 200 套。');
    $items = array(); $ids = array(); $codes = array();
    foreach ($envelope['builds'] as $raw) {
        $id = is_array($raw) ? ($raw['id'] ?? '') : '';
        if (!is_string($id) || !preg_match('/^[a-zA-Z0-9_-]{1,90}$/D', $id) || isset($ids[$id])) return new WP_Error('float_import', '导入文件含有缺失、无效或重复的配置 ID。');
        $item = float_space_normalize_loadout($raw);
        if (is_wp_error($item)) return $item;
        if (isset($codes[$item['code']])) return new WP_Error('float_import', '导入文件含有重复配置代码。');
        $item['importId'] = $id; $items[] = $item; $ids[$id] = true; $codes[$item['code']] = true;
    }
    // Validate the entire file before the first insertion.
    return $items;
}

add_action('admin_post_float_import_loadouts', function () {
    if (!current_user_can('manage_options')) wp_die('无权导入。', '', array('response' => 403));
    check_admin_referer('float_import_loadouts');
    $json = wp_unslash($_POST['loadout_json'] ?? '');
    if (isset($_FILES['loadout_file']) && ($_FILES['loadout_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['loadout_file'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 524288 || !is_uploaded_file($file['tmp_name'])) wp_die('文件上传失败或超过 512 KB。');
        $json = file_get_contents($file['tmp_name']);
    }
    $items = float_space_import_envelope($json);
    if (is_wp_error($items)) wp_die(esc_html($items->get_error_message()));
    $existing = get_posts(array('post_type' => 'float_loadout', 'post_status' => array('publish', 'draft', 'pending', 'private', 'future', 'trash'), 'numberposts' => -1));
    $ids = array(); $codes = array();
    foreach ($existing as $post) {
        $ids[(string) get_post_meta($post->ID, '_float_import_id', true)] = true;
        $data = get_post_meta($post->ID, '_float_loadout_data', true);
        if (is_array($data) && isset($data['code'])) $codes[$data['code']] = true;
    }
    $created = 0; $skipped = 0;
    foreach ($items as $item) {
        $import_id = $item['importId']; unset($item['importId']);
        if (isset($ids[$import_id]) || isset($codes[$item['code']])) { $skipped++; continue; }
        $id = wp_insert_post(wp_slash(array('post_type' => 'float_loadout', 'post_status' => 'draft', 'post_title' => $item['name'], 'meta_input' => array('_float_loadout_data' => $item, '_float_import_id' => $import_id))), true);
        if (is_wp_error($id)) wp_die(esc_html('部分导入失败。已导入的草稿已保留；重新导入会跳过它们。' . $id->get_error_message()));
        $created++; $ids[$import_id] = true; $codes[$item['code']] = true;
    }
    float_space_notice('已导入 ' . $created . ' 套草稿，跳过 ' . $skipped . ' 套重复配置。尚未公开任何新配置。', 'success');
    wp_safe_redirect(admin_url('edit.php?post_type=float_loadout')); exit;
});
