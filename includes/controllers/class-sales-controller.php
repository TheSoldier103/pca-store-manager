<?php

class PCA_Store_Sales_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_record_sale', [__CLASS__, 'record_sale']);
        add_action('wp_ajax_pca_store_fulfill_single_owed_item', [__CLASS__, 'fulfill_single_owed_item']);
        add_action('wp_ajax_pca_store_record_pack_sale', [__CLASS__, 'record_pack_sale']);
        add_action('wp_ajax_pca_store_check_pack_stock', [__CLASS__, 'check_pack_stock']);
        
    }

    public static function check_pack_stock() {
        global $wpdb;

        $stock_table = $wpdb->prefix . 'pca_store_item_stock';
        $pack_id     = intval($_POST['pack_id']);
        $qty_packs   = intval($_POST['qty']);
        $campus_id   = intval($_POST['campus_id']);

        $pack_items = PCA_Store_Items_Controller::fetch_pack_items($pack_id);

        if (!$pack_items) {
            wp_send_json_error(['message' => 'Pack has no items']);
        }

        $owed_items = [];

        foreach ($pack_items as $child) {
            $required_qty = $child->quantity * $qty_packs;

            $available = intval($wpdb->get_var($wpdb->prepare(
                "SELECT stock FROM $stock_table WHERE item_id = %d AND campus_id = %d",
                $child->child_item_id, $campus_id
            )));

            $owed = max(0, $required_qty - $available);

            if ($owed > 0) {
                $owed_items[] = [
                    'name'      => $child->name,
                    'available' => $available,
                    'required'  => $required_qty,
                    'owed'      => $owed,
                ];
            }
        }

        wp_send_json_success(['owed_items' => $owed_items]);
    }


    public static function fulfill_single_owed_item() {
        global $wpdb;

        $id = intval($_POST['id']);
        $owed_table  = $wpdb->prefix . 'pca_store_owed_items';
        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        // Get owed item
        $owed = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $owed_table WHERE id = %d
        ", $id));

        if (!$owed) {
            wp_send_json_error(['message' => 'Owed item not found']);
        }

        // Check stock availability
        $available_stock = intval($wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM $stock_table WHERE item_id = %d AND campus_id = %d",
            $owed->item_id, $owed->campus_id
        )));

        if ($available_stock < $owed->qty_owed) {
            wp_send_json_error([
                'message' => "Cannot fulfill. Only $available_stock available."
            ]);
        }

        // Log movement
        PCA_Store_Stock_Controller::apply_stock_movement(
            $owed->item_id,
            $owed->qty_owed,
            $owed->campus_id,
            'fulfill_owed',
            'owed_fulfillment',
            $owed->sale_id,
            'Fulfilled owed items for receipt ' . $owed->receipt_no
        );


        // Mark fulfilled
        $wpdb->update($owed_table, [
            'status' => 'fulfilled',
            'date_fulfilled' => current_time('mysql')
        ], ['id' => $id]);

        wp_send_json_success(['message' => 'Owed item fulfilled']);
    }


    public static function record_sale() {
        global $wpdb;

        $sales_table      = $wpdb->prefix . 'pca_store_sales';
        $sale_items_table = $wpdb->prefix . 'pca_store_sale_items';
        $stock_table      = $wpdb->prefix . 'pca_store_item_stock';
        $owed_table       = $wpdb->prefix . 'pca_store_owed_items';

        $item_id        = intval($_POST['item_id']);
        $qty_requested  = intval($_POST['qty']);
        $price          = floatval($_POST['price']);
        $discount       = floatval($_POST['discount']);
        $receipt_no     = sanitize_text_field($_POST['receipt_no']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $notes          = sanitize_text_field($_POST['notes']);
        $department     = sanitize_text_field($_POST['department']);
        $campus_id      = intval($_POST['campus_id']);

        if (!$item_id || $qty_requested <= 0) {
            wp_send_json_error(['message' => 'Invalid item or quantity']);
        }

        if (empty($receipt_no)) {
            $receipt_no = 'RCP-' . strtoupper(wp_generate_password(8, false));
        }

        // ---------------------------------------------------------
        // 1. GET CURRENT STOCK
        // ---------------------------------------------------------
        $available_stock = intval($wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM $stock_table WHERE item_id = %d AND campus_id = %d",
            $item_id, $campus_id
        )));

        // ---------------------------------------------------------
        // 2. CALCULATE Owed + Deductable
        // ---------------------------------------------------------
        $qty_to_deduct = min($qty_requested, $available_stock);
        $qty_owed      = max(0, $qty_requested - $available_stock);

        // ---------------------------------------------------------
        // 3. INSERT SALE
        // ---------------------------------------------------------
        $total_amount = ($price * $qty_requested) - $discount;

        $wpdb->insert($sales_table, [
            'receipt_no'     => $receipt_no,
            'sale_date'      => current_time('mysql'),
            'department'     => $department,
            'total_amount'   => $total_amount,
            'amount_paid'    => $total_amount,
            'balance'        => 0,
            'payment_method' => $payment_method,
            'sold_by'        => get_current_user_id(),
            'notes'          => $notes,
            'sale_type'      => 'single',
            'pack_qty'       => 0,
            'campus_id'      => $campus_id,
            'has_owed_items' => $qty_owed > 0 ? 1 : 0,
            'created_at'     => current_time('mysql'),
        ]);

        $sale_id = $wpdb->insert_id;

        // ---------------------------------------------------------
        // 4. INSERT SALE ITEM
        // ---------------------------------------------------------
        $wpdb->insert($sale_items_table, [
            'sale_id'     => $sale_id,
            'item_id'     => $item_id,
            'item_name'   => $wpdb->get_var("SELECT name FROM {$wpdb->prefix}pca_store_items WHERE id = $item_id"),
            'quantity'    => $qty_requested,
            'unit_price'  => $price,
            'total_price' => $total_amount,
            'created_at'  => current_time('mysql')
        ]);

        // ---------------------------------------------------------
        // 5. DEDUCT AVAILABLE STOCK + APPLY STOCK MOVEMENT
        // ---------------------------------------------------------
        if ($qty_to_deduct > 0) {

            // Log stock movement
            PCA_Store_Stock_Controller::apply_stock_movement(
                $item_id,
                $qty_to_deduct,
                $campus_id,
                'sale',
                'sale',
                $sale_id,
                'Receipt ' . $receipt_no
            );
        }

        // ---------------------------------------------------------
        // 6. INSERT OWED ITEMS (IF ANY)
        // ---------------------------------------------------------
        if ($qty_owed > 0) {

            $item_name = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}pca_store_items WHERE id = %d",
                $item_id
            ));

            $wpdb->insert($owed_table, [
                'sale_id'     => $sale_id,
                'receipt_no'  => $receipt_no,
                'item_id'     => $item_id,
                'item_name'   => $item_name,
                'qty_owed'    => $qty_owed,
                'campus_id'   => $campus_id,
                'status'      => 'pending',
                'date_created'=> current_time('mysql')
            ]);
        }

        // ---------------------------------------------------------
        // 7. RETURN SUCCESS
        // ---------------------------------------------------------
        wp_send_json_success([
            'message' => $qty_owed > 0
                ? "Sale recorded. Owed items: $qty_owed"
                : "Sale recorded successfully",
            'sale_id' => $sale_id
        ]);
    }

    public static function record_pack_sale() {
        global $wpdb;

        $sales_table      = $wpdb->prefix . 'pca_store_sales';
        $sale_items_table = $wpdb->prefix . 'pca_store_sale_items';
        $stock_table      = $wpdb->prefix . 'pca_store_item_stock';
        $owed_table       = $wpdb->prefix . 'pca_store_owed_items';

        $pack_id        = intval($_POST['item_id']);
        $qty_packs      = intval($_POST['qty']);
        $price          = floatval($_POST['price']);
        $discount       = floatval($_POST['discount']);
        $receipt_no     = sanitize_text_field($_POST['receipt_no']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $notes          = sanitize_text_field($_POST['notes']);
        $campus_id      = intval($_POST['campus_id']);

        $pack_name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}pca_store_items WHERE id = %d",
            $pack_id
        ));

        if (!$pack_id || $qty_packs <= 0) {
            wp_send_json_error(['message' => 'Invalid pack or quantity']);
        }

        if (empty($receipt_no)) {
            $receipt_no = 'RCP-' . strtoupper(wp_generate_password(8, false));
        }

        $pack_items = PCA_Store_Items_Controller::fetch_pack_items($pack_id);

        if (!$pack_items) {
            wp_send_json_error(['message' => 'Pack has no items']);
        }

        $has_owed  = false;
        $owed_list = []; // ✅ initialized here, before the foreach

        $total_amount = ($price * $qty_packs) - $discount;

        $wpdb->insert($sales_table, [
            'receipt_no'     => $receipt_no,
            'sale_date'      => current_time('mysql'),
            'department'     => 'books',
            'total_amount'   => $total_amount,
            'amount_paid'    => $total_amount,
            'balance'        => 0,
            'payment_method' => $payment_method,
            'sold_by'        => get_current_user_id(),
            'notes'          => $notes,
            'sale_type'      => 'pack',
            'pack_qty'       => $qty_packs,
            'pack_name'      => $pack_name,
            'campus_id'      => $campus_id,
            'has_owed_items' => 0,
            'created_at'     => current_time('mysql'),
        ]);

        $sale_id = $wpdb->insert_id;

        foreach ($pack_items as $child) {

            $child_id     = $child->child_item_id;
            $required_qty = $child->quantity * $qty_packs;

            $available = intval($wpdb->get_var($wpdb->prepare(
                "SELECT stock FROM $stock_table WHERE item_id = %d AND campus_id = %d",
                $child_id, $campus_id
            )));

            $deduct = min($required_qty, $available);
            $owed   = max(0, $required_qty - $available); // ✅ $owed defined here

            if ($owed > 0) {
                $has_owed    = true;
                $owed_list[] = [             // ✅ now inside foreach, after $owed is set
                    'name' => $child->name,
                    'qty'  => $owed,
                ];
            }

            $wpdb->insert($sale_items_table, [
                'sale_id'     => $sale_id,
                'item_id'     => $child_id,
                'item_name'   => $child->name,
                'quantity'    => $required_qty,
                'unit_price'  => 0,
                'total_price' => 0,
                'created_at'  => current_time('mysql'),
            ]);

            if ($deduct > 0) {
                PCA_Store_Stock_Controller::apply_stock_movement(
                    $child_id,
                    $deduct,
                    $campus_id,
                    'sale',
                    'pack_sale',
                    $sale_id,
                    'Pack sale: ' . $receipt_no
                );
            }

            if ($owed > 0) {
                $wpdb->insert($owed_table, [
                    'sale_id'      => $sale_id,
                    'receipt_no'   => $receipt_no,
                    'item_id'      => $child_id,
                    'item_name'    => $child->name,
                    'qty_owed'     => $owed,
                    'campus_id'    => $campus_id,
                    'status'       => 'pending',
                    'date_created' => current_time('mysql'),
                ]);
            }
        }

        if ($has_owed) {
            $wpdb->update($sales_table, ['has_owed_items' => 1], ['id' => $sale_id]);
        }

        wp_send_json_success([
            'message'    => $has_owed
                ? "Pack sale recorded. Some items are owed."
                : "Pack sale recorded successfully",
            'sale_id'    => $sale_id,
            'owed_items' => $owed_list,
        ]);
    }



}
