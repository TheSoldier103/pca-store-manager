<?php

class PCA_Store_Sales_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_record_sale', [__CLASS__, 'record_sale']);
        add_action('wp_ajax_pca_store_fulfill_single_owed_item', [__CLASS__, 'fulfill_single_owed_item']);
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

        // Deduct stock
        $wpdb->query($wpdb->prepare(
            "UPDATE $stock_table SET stock = stock - %d
            WHERE item_id = %d AND campus_id = %d",
            $owed->qty_owed, $owed->item_id, $owed->campus_id
        ));

        // Log movement
        PCA_Store_Stock_Controller::apply_stock_movement(
            $owed->item_id,
            $owed->qty_owed,
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

            // Deduct stock
            $wpdb->query($wpdb->prepare(
                "UPDATE $stock_table 
                SET stock = stock - %d 
                WHERE item_id = %d AND campus_id = %d",
                $qty_to_deduct, $item_id, $campus_id
            ));

            // Log stock movement
            PCA_Store_Stock_Controller::apply_stock_movement(
                $item_id,
                $qty_to_deduct,
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


}
