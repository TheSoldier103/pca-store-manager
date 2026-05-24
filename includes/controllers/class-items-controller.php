<?php

class PCA_Store_Items_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_save_item', [__CLASS__, 'save_item']);
        add_action('wp_ajax_pca_store_delete_item', [__CLASS__, 'delete_item']);
        add_action('wp_ajax_pca_store_get_pack_items', [__CLASS__, 'get_pack_items']);
        add_action('wp_ajax_pca_store_get_item', [__CLASS__, 'get_item']);
        add_action('wp_ajax_pca_store_get_books_for_pack', [__CLASS__, 'get_books_for_pack']);


    }

    public static function get_books_for_pack() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $dept_table  = $wpdb->prefix . 'pca_store_departments';

        $class = sanitize_text_field($_GET['class'] ?? '');
        $subject = sanitize_text_field($_GET['subject'] ?? '');

        // Get Books department_id
        $books_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table 
            WHERE LOWER(name) = 'books'
            LIMIT 1
        ");

        $where = ["department_id = $books_dept_id", "item_type = 'single'"];

        if ($class) {
            $where[] = $wpdb->prepare("class_level = %s", $class);
        }

        if ($subject) {
            $where[] = $wpdb->prepare("subject LIKE %s", "%$subject%");
        }

        $where_sql = implode(" AND ", $where);

        $books = $wpdb->get_results("
            SELECT id, name, class_level, subject 
            FROM $items_table
            WHERE $where_sql
            ORDER BY name ASC
        ");

        wp_send_json_success(['books' => $books]);
    }


    public static function get_item() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';

        $id = intval($_GET['id']);

        $item = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $items_table WHERE id = %d LIMIT 1
        ", $id));

        if (!$item) {
            wp_send_json_error(['message' => 'Item not found']);
        }

        // If pack, load children
        $children = [];
        if ($item->item_type === 'pack') {
            $children = $wpdb->get_results($wpdb->prepare("
                SELECT child_item_id, quantity 
                FROM $packs_table 
                WHERE pack_id = %d
            ", $id));
        }

        wp_send_json_success([
            'item' => $item,
            'children' => $children
        ]);
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


        $id = intval($_POST['id']);

        if ($id > 0) {
            // UPDATE
            $wpdb->update($items_table, [
                'name'           => $name,
                'department_id'  => $department_id,
                'supplier_id'    => $supplier_id ?: null,
                'selling_price'  => $selling_price,
                'reorder_level'  => $reorder_level,
                'class_level'    => $class_level,
                'subject'        => $subject,
                'size'           => $size,
                'gender'         => $gender,
                'color'          => $color,
                'updated_at'     => current_time('mysql'),
            ], ['id' => $id]);

            $item_id = $id;

        } else {
            // INSERT
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
        }


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

        if (!$id) {
            wp_send_json_error(['message' => 'Invalid item ID']);
        }

        $wpdb->update($items_table, [
            'status' => 'deleted',
            'updated_at' => current_time('mysql')
        ], ['id' => $id]);

        wp_send_json_success(['message' => 'Item deleted successfully']);
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
