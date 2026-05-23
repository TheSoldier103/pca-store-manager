<?php

class PCA_Store_Items_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_save_item', [__CLASS__, 'save_item']);
        add_action('wp_ajax_pca_store_delete_item', [__CLASS__, 'delete_item']);
        add_action('wp_ajax_pca_store_get_pack_items', [__CLASS__, 'get_pack_items']);
    }

    /**
     * Save item (single book, pack, or stationery)
     */
    public static function save_item() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';

        $item_type      = sanitize_text_field($_POST['item_type']);
        $name           = sanitize_text_field($_POST['name']);
        $department_id  = intval($_POST['department_id']);
        $supplier_id    = intval($_POST['supplier_id']);
        $selling_price  = floatval($_POST['selling_price']);
        $reorder_level  = intval($_POST['reorder_level']);

        $class_level    = sanitize_text_field($_POST['class_level'] ?? '');
        $subject        = sanitize_text_field($_POST['subject'] ?? '');
        $size           = sanitize_text_field($_POST['size'] ?? '');
        $gender         = sanitize_text_field($_POST['gender'] ?? '');
        $color          = sanitize_text_field($_POST['color'] ?? '');

        // Validate
        if (!$name) {
            wp_send_json_error(['message' => 'Item name is required']);
        }

        if (!$department_id) {
            wp_send_json_error(['message' => 'Department is required']);
        }

        // Prevent duplicates: same name + same supplier + same department
        $duplicate = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $items_table 
            WHERE name = %s 
            AND supplier_id = %d 
            AND department_id = %d 
            AND status != 'deleted'",
            $name, $supplier_id, $department_id
        ));

        if ($duplicate > 0) {
            wp_send_json_error(['message' => 'This item already exists for this supplier in this department']);
        }

        // Insert item
        $wpdb->insert($items_table, [
            'name'           => $name,
            'department_id'  => $department_id,
            'supplier_id'    => $supplier_id ?: null,
            'selling_price'  => $selling_price,
            'reorder_level'  => $reorder_level,
            'item_type'      => $item_type,
            'class_level'    => $class_level,
            'subject'        => $subject,
            'size'           => $size,
            'gender'         => $gender,
            'color'          => $color,
            'status'         => 'active',
            'created_at'     => current_time('mysql'),
        ]);

        $item_id = $wpdb->insert_id;

        // Save pack items
        if ($item_type === 'pack') {

            $pack_items = $_POST['pack_items'] ?? [];

            foreach ($pack_items as $child) {
                $wpdb->insert($packs_table, [
                    'pack_id'       => $item_id,
                    'child_item_id' => intval($child['id']),
                    'quantity'      => intval($child['qty']),
                ]);
            }
        }

        wp_send_json_success([
            'message' => 'Item saved successfully',
            'item_id' => $item_id
        ]);
    }


    /**
     * Delete item (soft delete)
     */
    public static function delete_item() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $id = intval($_POST['id']);

        $wpdb->update($items_table, [
            'status' => 'deleted'
        ], ['id' => $id]);

        wp_send_json_success(['message' => 'Item deleted']);
    }

    /**
     * Load pack composition for editing
     */
    public static function get_pack_items() {
        global $wpdb;

        $packs_table = $wpdb->prefix . 'pca_store_item_packs';
        $items_table = $wpdb->prefix . 'pca_store_items';

        $pack_id = intval($_GET['pack_id']);

        $rows = $wpdb->get_results("
            SELECT p.child_item_id, p.quantity, i.name, i.selling_price
            FROM $packs_table p
            LEFT JOIN $items_table i ON i.id = p.child_item_id
            WHERE p.pack_id = $pack_id
        ");

        wp_send_json_success($rows);
    }
}
