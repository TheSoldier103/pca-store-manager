<?php

class PCA_Store_Sales_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_record_sale', [__CLASS__, 'record_sale']);
    }

    // public static function record_sale() {
    //     global $wpdb;

    //     $sales_table      = $wpdb->prefix . 'pca_store_sales';
    //     $sale_items_table = $wpdb->prefix . 'pca_store_sale_items';

    //     $item_id        = intval($_POST['item_id']);
    //     $qty            = intval($_POST['qty']);
    //     $price          = floatval($_POST['price']);
    //     $discount       = floatval($_POST['discount']);
    //     $receipt_no     = sanitize_text_field($_POST['receipt_no']);
    //     $payment_method = sanitize_text_field($_POST['payment_method']);
    //     $notes          = sanitize_text_field($_POST['notes']);
    //     $department     = sanitize_text_field($_POST['department']);
    //     $campus_id     = sanitize_text_field($_POST['campus_id']);

    //     if (!$item_id || $qty <= 0) {
    //         wp_send_json_error(['message' => 'Invalid item or quantity']);
    //     }

    //     $line_total = ($price * $qty) - $discount;

    //     if (empty($receipt_no)) {
    //         $receipt_no = 'RCP-' . strtoupper(wp_generate_password(8, false));
    //     }

    //     // Get available stock for this campus
    //     $campus_id = PCA_Store_Helpers::get_user_campus() ?: intval($_POST['campus_id']);

    //     $stock_table = $wpdb->prefix . 'pca_store_item_stock';

    //     $available_stock = $wpdb->get_var($wpdb->prepare("
    //         SELECT stock 
    //         FROM $stock_table
    //         WHERE item_id = %d AND campus_id = %d
    //     ", $item_id, $campus_id));

    //     if ($available_stock === null) {
    //         $available_stock = 0;
    //     }


    //     if ($available_stock < $qty) {
    //         wp_send_json_error([
    //             'message' => "Insufficient stock. Available: $available_stock"
    //         ]);
    //     }

    //     // Insert sale
    //     $wpdb->insert($sales_table, [
    //         'receipt_no'     => $receipt_no,
    //         'sale_date'      => current_time('mysql'),
    //         'department'     => $department,
    //         'total_amount'   => $line_total,
    //         'amount_paid'    => $line_total,
    //         'balance'        => 0,
    //         'payment_method' => $payment_method,
    //         'sold_by'        => get_current_user_id(),
    //         'notes'          => $notes,
    //         'campus_id'      => $campus_id,
    //         'created_at'     => current_time('mysql'),
    //     ]);

    //     $sale_id = $wpdb->insert_id;

    //     // Insert sale item
    //     $wpdb->insert($sale_items_table, [
    //         'sale_id'    => $sale_id,
    //         'item_id'    => $item_id,
    //         'quantity'   => $qty,
    //         'unit_price' => $price,
    //         'total_price' => $line_total,
    //     ]);

    //     // Deduct stock
    //     PCA_Store_Stock_Controller::apply_stock_movement(
    //         $item_id,
    //         $qty,
    //         'sale',
    //         'sale',
    //         $sale_id,
    //         'Receipt ' . $receipt_no
    //     );

    //     wp_send_json_success([
    //         'message' => 'Sale recorded successfully',
    //         'sale_id' => $sale_id
    //     ]);
    // }

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
            'school_id'      => 1, // adjust if needed
            'campus_id'      => $campus_id,
            'department_id'  => 0, // optional
            'receipt_no'     => $receipt_no,
            'payment_method' => $payment_method,
            'subtotal'       => $price * $qty_requested,
            'discount'       => $discount,
            'total_amount'   => $total_amount,
            'amount_paid'    => $total_amount,
            'balance'        => 0,
            'has_owed_items' => $qty_owed > 0 ? 1 : 0,
            'notes'          => $notes,
            'sold_by'        => get_current_user_id(),
            'created_at'     => current_time('mysql')
        ]);

        $sale_id = $wpdb->insert_id;

        // ---------------------------------------------------------
        // 4. INSERT SALE ITEM
        // ---------------------------------------------------------
        $wpdb->insert($sale_items_table, [
            'sale_id'     => $sale_id,
            'item_id'     => $item_id,
            'item_name'   => get_the_title($item_id) ?: '',
            'quantity'    => $qty_requested,
            'unit_price'  => $price,
            'total_price' => $total_amount,
            'created_at'  => current_time('mysql')
        ]);

        // ---------------------------------------------------------
        // 5. DEDUCT AVAILABLE STOCK
        // ---------------------------------------------------------
        if ($qty_to_deduct > 0) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $stock_table 
                SET stock = stock - %d 
                WHERE item_id = %d AND campus_id = %d",
                $qty_to_deduct, $item_id, $campus_id
            ));
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
                : "Sale recorded successfully"
        ]);
    }

}
