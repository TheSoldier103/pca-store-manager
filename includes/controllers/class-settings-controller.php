<?php

class PCA_Store_Settings_Controller {

    public static function init() {

        add_action('wp_ajax_pca_settings_save_school',      [__CLASS__, 'save_school']);
        add_action('wp_ajax_pca_settings_delete_school',    [__CLASS__, 'delete_school']);

        add_action('wp_ajax_pca_settings_save_campus',      [__CLASS__, 'save_campus']);
        add_action('wp_ajax_pca_settings_delete_campus',    [__CLASS__, 'delete_campus']);

        add_action('wp_ajax_pca_settings_save_department',  [__CLASS__, 'save_department']);
        add_action('wp_ajax_pca_settings_delete_department',[__CLASS__, 'delete_department']);

        add_action('wp_ajax_pca_settings_save_receipt',     [__CLASS__, 'save_receipt_settings']);
        add_action('wp_ajax_pca_settings_save_roles',       [__CLASS__, 'save_roles_settings']);
        add_action('wp_ajax_pca_settings_save_advanced',    [__CLASS__, 'save_advanced_settings']);
    }


    /* ---------- ROLES & PERMISSIONS ---------- */

    public static function save_roles_settings() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        update_option('pca_roles_can_edit_stock',      !empty($_POST['can_edit_stock'])      ? 1 : 0);
        update_option('pca_roles_can_view_reports',    !empty($_POST['can_view_reports'])    ? 1 : 0);
        update_option('pca_roles_can_manage_settings', !empty($_POST['can_manage_settings']) ? 1 : 0);

        wp_send_json_success(['message' => 'Roles & permissions saved']);
    }


    /* ---------- ADVANCED ---------- */

    public static function save_advanced_settings() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        update_option('pca_advanced_debug_mode', !empty($_POST['debug_mode']) ? 1 : 0);

        wp_send_json_success(['message' => 'Advanced settings saved']);
    }

    // public static function save_roles_settings() {

    //     $can_edit_stock      = !empty($_POST['can_edit_stock']) ? 1 : 0;
    //     $can_view_reports    = !empty($_POST['can_view_reports']) ? 1 : 0;
    //     $can_manage_settings = !empty($_POST['can_manage_settings']) ? 1 : 0;

    //     update_option('pca_roles_can_edit_stock', $can_edit_stock);
    //     update_option('pca_roles_can_view_reports', $can_view_reports);
    //     update_option('pca_roles_can_manage_settings', $can_manage_settings);

    //     wp_send_json_success(['message' => 'Roles & permissions saved']);
    // }

    /* ---------- SCHOOLS ---------- */

    public static function save_school() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_schools';

        $id   = intval($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');

        if (!$name) {
            wp_send_json_error(['message' => 'School name is required']);
        }

        if ($id) {
            $wpdb->update($table, ['name' => $name], ['id' => $id]);
        } else {
            $slug = sanitize_title($name);

            $existing = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE slug = %s", $slug)
            );

            if ($existing) {
                $slug = $slug . '-' . time();
            }

            $wpdb->insert($table, [
                'name' => $name,
                'slug' => $slug,
            ]);

            if ($wpdb->last_error) {
                wp_send_json_error(['message' => $wpdb->last_error]);
            }

            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['message' => 'School saved', 'id' => $id]);
    }

    public static function delete_school() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_schools';
        $id    = intval($_POST['id'] ?? 0);

        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'School deleted']);
    }


    /* ---------- CAMPUSES ---------- */

    public static function save_campus() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_campuses';

        $id        = intval($_POST['id'] ?? 0);
        $name      = sanitize_text_field($_POST['name'] ?? '');
        $school_id = intval($_POST['school_id'] ?? 0);
        $status    = sanitize_text_field($_POST['status'] ?? 'active');

        if (!$name || !$school_id) {
            wp_send_json_error(['message' => 'Campus name and school are required']);
        }

        $data = [
            'name'      => $name,
            'school_id' => $school_id,
            'is_active' => $status === 'active' ? 1 : 0,
        ];

        if ($id) {
            $wpdb->update($table, $data, ['id' => $id]);
        } else {
            $slug = sanitize_title($name);
            $data['slug'] = $slug;
            $wpdb->insert($table, $data);
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['message' => 'Campus saved', 'id' => $id]);
    }

    public static function delete_campus() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_campuses';
        $id    = intval($_POST['id'] ?? 0);

        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'Campus deleted']);
    }


    /* ---------- DEPARTMENTS ---------- */

    public static function save_department() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_departments';

        $id   = intval($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $code = sanitize_text_field($_POST['code'] ?? '');

        if (!$name) {
            wp_send_json_error(['message' => 'Department name is required']);
        }

        $data = [
            'name' => $name,
            'code' => $code,
        ];

        if ($id) {
            $wpdb->update($table, $data, ['id' => $id]);
        } else {
            $wpdb->insert($table, $data);
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['message' => 'Department saved', 'id' => $id]);
    }

    public static function delete_department() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_departments';
        $id    = intval($_POST['id'] ?? 0);

        if (!$id) {
            wp_send_json_error(['message' => 'Invalid ID']);
        }

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'Department deleted']);
    }


    /* ---------- RECEIPT FORMAT ---------- */

    public static function save_receipt_settings() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        update_option('pca_receipt_prefix', sanitize_text_field($_POST['prefix'] ?? ''));
        update_option('pca_receipt_suffix', sanitize_text_field($_POST['suffix'] ?? ''));
        update_option('pca_receipt_pad',    intval($_POST['pad'] ?? 4));

        wp_send_json_success(['message' => 'Receipt settings saved']);
    }

}