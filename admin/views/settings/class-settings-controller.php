<?php

class PCA_Store_Settings_Controller {

    public static function init() {

        if (!current_user_can('manage_options')) {
            return;
        }

        add_action('wp_ajax_pca_settings_save_school', [__CLASS__, 'save_school']);
        add_action('wp_ajax_pca_settings_delete_school', [__CLASS__, 'delete_school']);

        add_action('wp_ajax_pca_settings_save_campus', [__CLASS__, 'save_campus']);
        add_action('wp_ajax_pca_settings_delete_campus', [__CLASS__, 'delete_campus']);

        add_action('wp_ajax_pca_settings_save_department', [__CLASS__, 'save_department']);
        add_action('wp_ajax_pca_settings_delete_department', [__CLASS__, 'delete_department']);

        add_action('wp_ajax_pca_settings_save_receipt', [__CLASS__, 'save_receipt_settings']);
        add_action('wp_ajax_pca_settings_save_roles', [__CLASS__, 'save_roles_settings']);
        add_action('wp_ajax_pca_settings_save_advanced', [__CLASS__, 'save_advanced_settings']);
    }


    /* ---------- SCHOOLS ---------- */

    // public static function save_school() {
    //     global $wpdb;
    //     $table = $wpdb->prefix . 'pca_store_schools';

    //     $id   = intval($_POST['id'] ?? 0);
    //     $name = sanitize_text_field($_POST['name']);

    //     if (!$name) {
    //         wp_send_json_error(['message' => 'School name is required']);
    //     }

    //     if ($id) {
    //         $wpdb->update($table, ['name' => $name], ['id' => $id]);
    //     } else {
    //         $wpdb->insert($table, ['name' => $name]);
    //         $id = $wpdb->insert_id;
    //     }

    //     wp_send_json_success(['message' => 'School saved', 'id' => $id]);
    // }


    public static function save_school() {
        $wpdb->show_errors();
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_schools';

        $id   = intval($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name']);

        if (!$name) {
            wp_send_json_error(['message' => 'School name is required']);
        }

        if ($id) {
            $wpdb->update($table, ['name' => $name], ['id' => $id]);
        } else {
            $slug = sanitize_title($name); // generates a URL-safe slug from the name

            // Handle duplicate slugs
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
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['message' => 'School saved', 'id' => $id]);
    }

    public static function delete_school() {
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_schools';
        $id    = intval($_POST['id']);

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'School deleted']);
    }

    /* ---------- CAMPUSES ---------- */

    public static function save_campus() {
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_campuses';

        $id        = intval($_POST['id'] ?? 0);
        $name      = sanitize_text_field($_POST['name']);
        $school_id = intval($_POST['school_id']);
        $status    = sanitize_text_field($_POST['status'] ?? 'active');

        if (!$name || !$school_id) {
            wp_send_json_error(['message' => 'Campus name and school are required']);
        }

        $data = [
            'name'      => $name,
            'school_id' => $school_id,
            'status'    => $status,
        ];

        if ($id) {
            $wpdb->update($table, $data, ['id' => $id]);
        } else {
            $wpdb->insert($table, $data);
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(['message' => 'Campus saved', 'id' => $id]);
    }

    public static function delete_campus() {
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_campuses';
        $id    = intval($_POST['id']);

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'Campus deleted']);
    }

    /* ---------- DEPARTMENTS ---------- */

    public static function save_department() {
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_departments';

        $id   = intval($_POST['id'] ?? 0);
        $name = sanitize_text_field($_POST['name']);
        $code = sanitize_text_field($_POST['code']);

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
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_departments';
        $id    = intval($_POST['id']);

        $wpdb->delete($table, ['id' => $id]);

        wp_send_json_success(['message' => 'Department deleted']);
    }

    /* ---------- RECEIPT FORMAT ---------- */

    public static function save_receipt_settings() {
        $prefix = sanitize_text_field($_POST['prefix']);
        $suffix = sanitize_text_field($_POST['suffix']);
        $pad    = intval($_POST['pad'] ?? 4);

        update_option('pca_receipt_prefix', $prefix);
        update_option('pca_receipt_suffix', $suffix);
        update_option('pca_receipt_pad', $pad);

        wp_send_json_success(['message' => 'Receipt settings saved']);
    }

    /* ---------- ROLES & PERMISSIONS ---------- */

    public static function save_roles_settings() {
        $can_edit_stock = !empty($_POST['can_edit_stock']) ? 1 : 0;
        $can_view_reports = !empty($_POST['can_view_reports']) ? 1 : 0;

        update_option('pca_roles_can_edit_stock', $can_edit_stock);
        update_option('pca_roles_can_view_reports', $can_view_reports);

        wp_send_json_success(['message' => 'Roles & permissions saved']);
    }

    /* ---------- ADVANCED ---------- */

    public static function save_advanced_settings() {
        $debug_mode = !empty($_POST['debug_mode']) ? 1 : 0;

        update_option('pca_advanced_debug_mode', $debug_mode);

        wp_send_json_success(['message' => 'Advanced settings saved']);
    }
}
