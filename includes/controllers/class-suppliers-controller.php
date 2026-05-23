<?php

if (!defined('ABSPATH')) exit;

class PCA_Store_Suppliers_Controller {

    public static function init() {

        add_action('wp_ajax_pca_save_supplier',   [__CLASS__, 'save_supplier']);
        add_action('wp_ajax_pca_delete_supplier', [__CLASS__, 'delete_supplier']);
        add_action('wp_ajax_pca_get_supplier',    [__CLASS__, 'get_supplier']);
        add_action('wp_ajax_pca_get_suppliers_by_department', [__CLASS__, 'get_suppliers_by_department']);

    }

    public static function save_supplier() {

        if (!current_user_can('pca_store_manage_suppliers') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;

        $table_suppliers  = $wpdb->prefix . 'pca_store_suppliers';
        $table_departments = $wpdb->prefix . 'pca_store_departments';

        $id            = intval($_POST['id'] ?? 0);
        $name          = sanitize_text_field($_POST['name'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $contact       = sanitize_text_field($_POST['contact_person'] ?? '');
        $phone         = sanitize_text_field($_POST['phone'] ?? '');
        $email         = sanitize_email($_POST['email'] ?? '');
        $address       = sanitize_textarea_field($_POST['address'] ?? '');
        $notes         = sanitize_textarea_field($_POST['notes'] ?? '');
        $is_active     = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

        if (!$name) {
            wp_send_json_error(['message' => 'Supplier name is required']);
        }

        if ($department_id) {
            $exists = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM $table_departments WHERE id = %d", $department_id)
            );
            if (!$exists) {
                wp_send_json_error(['message' => 'Invalid department']);
            }
        }

        // TODO: adjust school_id / campus_id to your context
        $school_id = 1;
        $campus_id = null;

        $data = [
            'school_id'      => $school_id,
            'campus_id'      => $campus_id,
            'department_id'  => $department_id ?: null,
            'name'           => $name,
            'contact_person' => $contact,
            'phone'          => $phone,
            'email'          => $email,
            'address'        => $address,
            'notes'          => $notes,
            'is_active'      => $is_active ? 1 : 0,
        ];

        if ($id) {
            $wpdb->update($table_suppliers, $data, ['id' => $id]);
            if ($wpdb->last_error) {
                wp_send_json_error(['message' => $wpdb->last_error]);
            }
            wp_send_json_success(['message' => 'Supplier updated']);
        } else {
            $wpdb->insert($table_suppliers, $data);
            if ($wpdb->last_error) {
                wp_send_json_error(['message' => $wpdb->last_error]);
            }
            wp_send_json_success(['message' => 'Supplier created', 'id' => $wpdb->insert_id]);
        }
    }

    public static function delete_supplier() {

        if (!current_user_can('pca_store_manage_suppliers') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table_suppliers = $wpdb->prefix . 'pca_store_suppliers';

        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => 'Invalid supplier ID']);
        }

        $wpdb->delete($table_suppliers, ['id' => $id]);

        if ($wpdb->last_error) {
            wp_send_json_error(['message' => $wpdb->last_error]);
        }

        wp_send_json_success(['message' => 'Supplier deleted']);
    }

    public static function get_supplier() {

        if (!current_user_can('pca_store_manage_suppliers') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }

        global $wpdb;
        $table_suppliers = $wpdb->prefix . 'pca_store_suppliers';

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => 'Invalid supplier ID']);
        }

        $supplier = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_suppliers WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$supplier) {
            wp_send_json_error(['message' => 'Supplier not found']);
        }

        wp_send_json_success(['supplier' => $supplier]);
    }

    public static function get_suppliers_by_department() {
        global $wpdb;

        $department_id = intval($_GET['department_id'] ?? 0);
        $table = $wpdb->prefix . 'pca_store_suppliers';

        if (!$department_id) {
            wp_send_json_success(['suppliers' => []]);
        }

        $suppliers = $wpdb->get_results(
            $wpdb->prepare("
                SELECT id, name 
                FROM $table
                WHERE department_id = %d
                AND is_active = 1
                ORDER BY name ASC
            ", $department_id)
        );

        wp_send_json_success(['suppliers' => $suppliers]);
    }

}
