<?php

class PCA_Store_Sales_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_record_sale', [__CLASS__, 'record_sale']);
    }

    public static function record_sale() {
        global $wpdb;

        $sales_table      = $wpdb->prefix . 'pca_store_sales';
        $sale_items_table = $wpdb->prefix . 'pca_store_sale_items';

        $item_id        = intval($_POST['item_id']);
        $qty            = intval($_POST['qty']);
        $price          = floatval($_POST['price']);
        $discount       = floatval($_POST['discount']);
        $receipt_no     = sanitize_text_field($_POST['receipt_no']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $notes          = sanitize_text_field($_POST['notes']);
        $department     = sanitize_text_field($_POST['department']);

        if (!$item_id || $qty <= 0) {
            wp_send_json_error(['message' => 'Invalid item or quantity']);
        }

        $line_total = ($price * $qty) - $discount;

        // Insert sale
        $wpdb->insert($sales_table, [
            'receipt_no'     => $receipt_no,
            'sale_date'      => current_time('mysql'),
            'department'     => $department,
            'total_amount'   => $line_total,
            'amount_paid'    => $line_total,
            'balance'        => 0,
            'payment_method' => $payment_method,
            'sold_by'        => get_current_user_id(),
            'notes'          => $notes,
            'created_at'     => current_time('mysql'),
        ]);

        $sale_id = $wpdb->insert_id;

        // Insert sale item
        $wpdb->insert($sale_items_table, [
            'sale_id'    => $sale_id,
            'item_id'    => $item_id,
            'quantity'   => $qty,
            'unit_price' => $price,
            'discount'   => $discount,
            'total'      => $line_total,
        ]);

        // Deduct stock
        PCA_Store_Stock_Controller::apply_stock_movement(
            $item_id,
            $qty,
            'sale',
            'sale',
            $sale_id,
            'Receipt ' . $receipt_no
        );

        wp_send_json_success([
            'message' => 'Sale recorded successfully',
            'sale_id' => $sale_id
        ]);
    }
}
