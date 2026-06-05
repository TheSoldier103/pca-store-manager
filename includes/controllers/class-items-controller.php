<?php

class PCA_Store_Items_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_save_item', [__CLASS__, 'save_item']);
        add_action('wp_ajax_pca_store_delete_item', [__CLASS__, 'delete_item']);
        add_action('wp_ajax_pca_store_get_pack_items', [__CLASS__, 'get_pack_items']);
        add_action('wp_ajax_pca_store_get_item', [__CLASS__, 'get_item']);
        add_action('wp_ajax_pca_store_get_books_for_pack', [__CLASS__, 'get_books_for_pack']);
        add_action('wp_ajax_pca_store_get_pack', [__CLASS__, 'get_pack']);
        add_action('wp_ajax_pca_store_delete_pack', [__CLASS__, 'delete_pack']);
        add_action('wp_ajax_pca_store_import_books', [__CLASS__, 'import_books']);
        add_action('wp_ajax_pca_store_get_filtered_items', [__CLASS__, 'get_filtered_items']);
        add_action('wp_ajax_pca_store_add_stationery_to_pack', [__CLASS__, 'add_stationery_to_pack']);
        add_action('wp_ajax_pca_store_get_stationery_items', [__CLASS__, 'get_stationery_items']);

    }

    public static function get_stationery_items() {
        global $wpdb;

        $table      = $wpdb->prefix . 'pca_store_items';
        $dept_table = $wpdb->prefix . 'pca_store_departments';

        $stationery_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table
            WHERE LOWER(name) = 'stationery'
            LIMIT 1
        ");

        $items = $wpdb->get_results($wpdb->prepare("
            SELECT id, name, selling_price
            FROM $table
            WHERE department_id = %d
            AND item_type = 'single'
            AND status != 'deleted'
            ORDER BY name ASC
        ", $stationery_dept_id));

        if ($items === false) {
            wp_send_json_error(['message' => 'Database error']);
        }

        wp_send_json_success(['items' => $items]);
    }

    public static function add_stationery_to_pack() {
        global $wpdb;

        $packs_table = $wpdb->prefix . 'pca_store_item_packs';
        $items_table = $wpdb->prefix . 'pca_store_items';

        $pack_id = intval($_POST['pack_id']);
        $items   = $_POST['items'] ?? [];

        $added   = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $child_id = intval($item['id']);
            $qty      = max(1, intval($item['qty']));

            // FIX 1: Skip if this item is already in the pack
            $exists = $wpdb->get_var($wpdb->prepare("
                SELECT id FROM $packs_table
                WHERE pack_id = %d AND child_item_id = %d
            ", $pack_id, $child_id));

            if ($exists) {
                $skipped++;
                continue;
            }

            $wpdb->insert($packs_table, [
                'pack_id'       => $pack_id,
                'child_item_id' => $child_id,
                'quantity'      => $qty,
            ]);

            $added++;
        }

        // FIX 2: Recalculate pack price from ALL children (books + stationery)
        $new_price = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(i.selling_price * p.quantity)
            FROM $packs_table p
            INNER JOIN $items_table i ON i.id = p.child_item_id
            WHERE p.pack_id = %d
        ", $pack_id));

        $wpdb->update(
            $items_table,
            ['selling_price' => floatval($new_price)],
            ['id' => $pack_id]
        );

        $message = "$added item(s) added to pack.";
        if ($skipped > 0) {
            $message .= " $skipped duplicate(s) skipped.";
        }

        wp_send_json_success([
            'message'   => $message,
            'new_price' => floatval($new_price),
        ]);
    }

    
    public static function get_filtered_items() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';

        $class   = sanitize_text_field($_POST['class_level'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');

        $where = ["item_type = 'single'", "status != 'deleted'"];

        if ($class !== '') {
            $where[] = $wpdb->prepare("class_level = %s", $class);
        }

        if ($subject !== '') {
            $where[] = $wpdb->prepare("subject = %s", $subject);
        }

        $where_sql = implode(' AND ', $where);

        $items = $wpdb->get_results("
            SELECT id, name, selling_price
            FROM $items_table
            WHERE $where_sql
            ORDER BY name ASC
        ");

        wp_send_json_success(['items' => $items]);
    }


    public static function get_pack() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';

        $pack_id = intval($_GET['pack_id'] ?? 0);
        if ($pack_id <= 0) {
            wp_send_json_error(['message' => 'Invalid pack ID']);
        }

        // Get pack details
        $pack = $wpdb->get_row($wpdb->prepare("
            SELECT id, name, class_level, selling_price, reorder_level, department_id
            FROM $items_table
            WHERE id = %d AND item_type = 'pack'
        ", $pack_id));

        if (!$pack) {
            wp_send_json_error(['message' => 'Pack not found']);
        }

        // Get pack items
        $items = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.child_item_id AS id,
                p.quantity AS qty,
                i.name
            FROM $packs_table p
            LEFT JOIN $items_table i ON i.id = p.child_item_id
            WHERE p.pack_id = %d
        ", $pack_id));

        wp_send_json_success([
            'pack'  => $pack,
            'items' => $items
        ]);
    }

    public static function import_books() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $dept_table  = $wpdb->prefix . 'pca_store_departments';
        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        if (!isset($_FILES['csv_file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $rows = array_map('str_getcsv', file($file));

        if (count($rows) < 2) {
            wp_send_json_error(['message' => 'CSV is empty']);
        }

        // Get Books department ID
        $books_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table 
            WHERE LOWER(name) = 'books'
            LIMIT 1
        ");

        $header = array_map('trim', $rows[0]);
        $added = $skipped = $errors = 0;

        for ($i = 1; $i < count($rows); $i++) {

            $row = array_combine($header, $rows[$i]);
            if (!$row) { $errors++; continue; }

            $name  = trim($row['name'] ?? '');
            $price = floatval($row['selling_price'] ?? 0);

            if (!$name || $price <= 0) {
                $errors++;
                continue;
            }

            // Campus stock
            $stock_ughelli  = max(0, intval($row['stock_ughelli']  ?? 0));
            $stock_okuokoko = max(0, intval($row['stock_okuokoko'] ?? 0));

            // Duplicate check
            $exists = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM $items_table
                WHERE name = %s AND status != 'deleted'
            ", $name));

            if ($exists) {
                $skipped++;
                continue;
            }

            // Insert item
            $wpdb->insert($items_table, [
                'name'          => $name,
                'department_id' => $books_dept_id,
                'item_type'     => 'single',
                'selling_price' => $price,
                'class_level'   => trim($row['class_level'] ?? ''),
                'subject'       => trim($row['subject'] ?? ''),
                'reorder_level' => intval($row['reorder_level'] ?? 0),
                'status'        => 'active',
                'created_at'    => current_time('mysql'),
            ]);

            $item_id = $wpdb->insert_id; // ⭐ FIXED

            // Insert Ughelli stock
            $wpdb->insert($stock_table, [
                'item_id'   => $item_id,
                'campus_id' => 1,
                'stock'     => $stock_ughelli,
            ]);

            // Insert Okuokoko stock
            $wpdb->insert($stock_table, [
                'item_id'   => $item_id,
                'campus_id' => 2,
                'stock'     => $stock_okuokoko,
            ]);

            $added++;
        }

        wp_send_json_success([
            'added'   => $added,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    public static function delete_pack() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';

        $pack_id = intval($_POST['pack_id'] ?? 0);

        if ($pack_id <= 0) {
            wp_send_json_error(['message' => 'Invalid pack ID']);
        }

        // Soft delete the pack item
        $wpdb->update($items_table, [
            'status'     => 'deleted',
            'updated_at' => current_time('mysql')
        ], ['id' => $pack_id]);

        // Remove pack children (hard delete)
        $wpdb->delete($packs_table, ['pack_id' => $pack_id]);

        wp_send_json_success(['message' => 'Pack deleted']);
    }

    public static function get_books_for_pack() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $dept_table  = $wpdb->prefix . 'pca_store_departments';

        $class   = sanitize_text_field($_GET['class'] ?? '');
        $subject = sanitize_text_field($_GET['subject'] ?? '');

        $books_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table 
            WHERE LOWER(name) = 'books'
            LIMIT 1
        ");

        $where = [
            $wpdb->prepare("department_id = %d", $books_dept_id),
            "item_type = 'single'"
        ];

        if ($class !== '') {
            $where[] = $wpdb->prepare("class_level = %s", $class);
        }

        if ($subject !== '') {
            $where[] = $wpdb->prepare("subject = %s", $subject);
        }

        $where_sql = implode(' AND ', $where);

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

        $id             = intval($_POST['id'] ?? 0);
        $item_type      = sanitize_text_field($_POST['item_type']);
        $name           = sanitize_text_field($_POST['name']);
        $department_id  = intval($_POST['department_id']);
        $supplier_id    = intval($_POST['supplier_id'] ?? 0);
        $selling_price  = floatval($_POST['selling_price'] ?? 0);
        $reorder_level  = intval($_POST['reorder_level'] ?? 0);
        $class_level    = sanitize_text_field($_POST['class_level'] ?? '');
        $subject        = sanitize_text_field($_POST['subject'] ?? '');
        $size           = sanitize_text_field($_POST['size'] ?? '');
        $gender         = sanitize_text_field($_POST['gender'] ?? '');
        $color          = sanitize_text_field($_POST['color'] ?? '');

        // --- Validate required fields ---
        if (!$name) {
            wp_send_json_error(['message' => 'Item name is required']);
        }
        if (!$department_id) {
            wp_send_json_error(['message' => 'Department is required']);
        }

        // --- Validate pack has items BEFORE touching the DB ---
        $pack_items = [];
        if ($item_type === 'pack') {
            $raw = $_POST['pack_items'] ?? [];

            // Sanitize each child
            foreach ($raw as $child) {
                $child_id  = intval($child['id'] ?? 0);
                $child_qty = intval($child['qty'] ?? 1);
                if ($child_id > 0 && $child_qty > 0) {
                    $pack_items[] = ['id' => $child_id, 'qty' => $child_qty];
                }
            }

            if (empty($pack_items)) {
                wp_send_json_error(['message' => 'A pack must contain at least one item']);
            }
        }

        // --- Auto-calc pack price if selling_price is empty or zero ---
        if ($item_type === 'pack' && $selling_price <= 0) {

            $total_price = 0;

            foreach ($pack_items as $child) {
                $child_id = intval($child['id']);
                $qty      = intval($child['qty']);

                $price = $wpdb->get_var($wpdb->prepare("
                    SELECT selling_price FROM $items_table WHERE id = %d
                ", $child_id));

                if ($price !== null) {
                    $total_price += ($price * $qty);
                }
            }

            $selling_price = $total_price;
}


        // --- Duplicate check (exclude self when editing) ---
        $dup_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $items_table 
            WHERE name = %s 
            AND status != 'deleted'",
            $name
        );
        if ($id > 0) {
            $dup_query .= $wpdb->prepare(' AND id != %d', $id);
        }
        if ($wpdb->get_var($dup_query) > 0) {
            wp_send_json_error(['message' => 'An item with this name already exists']);
        }

        // --- Insert or Update ---
        if ($id > 0) {
            $wpdb->update($items_table, [
                'name'          => $name,
                'department_id' => $department_id,
                'supplier_id'   => $supplier_id ?: null,
                'selling_price' => $selling_price,
                'reorder_level' => $reorder_level,
                'class_level'   => $class_level,
                'subject'       => $subject,
                'size'          => $size,
                'gender'        => $gender,
                'color'         => $color,
                'updated_at'    => current_time('mysql'),
            ], ['id' => $id]);

            $item_id = $id;
        } else {
            $wpdb->insert($items_table, [
                'name'          => $name,
                'department_id' => $department_id,
                'supplier_id'   => $supplier_id ?: null,
                'selling_price' => $selling_price,
                'reorder_level' => $reorder_level,
                'item_type'     => $item_type,
                'class_level'   => $class_level,
                'subject'       => $subject,
                'size'          => $size,
                'gender'        => $gender,
                'color'         => $color,
                'status'        => 'active',
                'created_at'    => current_time('mysql'),
            ]);

            $item_id = $wpdb->insert_id;
        }

        // --- Save pack children (delete-then-insert, once, with $item_id) ---
        if ($item_type === 'pack') {
            // Always clear existing children first (handles both add and edit)
            $wpdb->delete($packs_table, ['pack_id' => $item_id]);

            foreach ($pack_items as $child) {
                $wpdb->insert($packs_table, [
                    'pack_id'       => $item_id,
                    'child_item_id' => $child['id'],
                    'quantity'      => $child['qty'],
                ]);
            }
        }

        wp_send_json_success([
            'message' => 'Item saved successfully',
            'item_id' => $item_id,
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
