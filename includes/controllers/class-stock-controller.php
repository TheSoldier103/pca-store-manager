<?php

class PCA_Store_Stock_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_add_stock', [__CLASS__, 'add_stock']);
        add_action('wp_ajax_pca_store_damage_stock', [__CLASS__, 'damage_stock']);
        add_action('wp_ajax_pca_store_correct_stock', [__CLASS__, 'correct_stock']);
        add_action('wp_ajax_pca_store_return_stock', [__CLASS__, 'return_stock']);
    }


    /**
     * Core stock movement engine
     */
    public static function apply_stock_movement($item_id, $qty, $movement_type, $reference_type, $reference_id, $notes = '') {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $movement_table = $wpdb->prefix . 'pca_store_stock_movements';

        // Get current stock
        $current_stock = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT current_stock FROM $items_table WHERE id = %d",
            $item_id
        ));

        $stock_before = $current_stock;

        // Apply movement
        if ($movement_type === 'add' || $movement_type === 'return') {
            $new_stock = $current_stock + $qty;
        } elseif ($movement_type === 'damage' || $movement_type === 'sale') {
            $new_stock = $current_stock - $qty;
        } elseif ($movement_type === 'correction') {
            $new_stock = $qty;
        } else {
            return false;
        }

        // Update stock
        $wpdb->update($items_table, [
            'current_stock' => $new_stock
        ], ['id' => $item_id]);

        // Log movement
        $wpdb->insert($movement_table, [
            'item_id'        => $item_id,
            'movement_type'  => $movement_type,
            'quantity'       => $qty,
            'stock_before'   => $stock_before,
            'stock_after'    => $new_stock,
            'reference_type' => $reference_type,
            'reference_id'   => $reference_id,
            'notes'          => $notes,
            'created_by'     => get_current_user_id(),
            'created_at'     => current_time('mysql'),
        ]);

        return $new_stock;
    }

    /**
     * Add Stock
     */
    public static function add_stock() {
        global $wpdb;

        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        // $campus_id = intval($_POST['campus_id']);
        $campus_id = PCA_Store_Helpers::get_user_campus() ?: intval($_POST['campus_id']);
        $item_id   = intval($_POST['item_id']);
        $qty       = intval($_POST['qty']);

        if (!$campus_id || !$item_id || $qty <= 0) {
            wp_send_json_error(['message' => 'Invalid stock data']);
        }

        // Check if stock row exists
        $existing = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM $stock_table
            WHERE item_id = %d AND campus_id = %d
        ", $item_id, $campus_id));

        if ($existing) {
            // Update stock
            $wpdb->query($wpdb->prepare("
                UPDATE $stock_table
                SET stock = stock + %d
                WHERE id = %d
            ", $qty, $existing));
        } else {
            // Insert new stock row
            $wpdb->insert($stock_table, [
                'item_id'   => $item_id,
                'campus_id' => $campus_id,
                'stock'     => $qty,
            ]);
        }

        wp_send_json_success(['message' => 'Stock updated successfully']);
    }

    /**
     * Damage / Loss
     */
    public static function damage_stock() {
        $item_id = intval($_POST['item_id']);
        $qty     = intval($_POST['qty']);
        $reason  = sanitize_text_field($_POST['reason']);
        $notes   = sanitize_text_field($_POST['notes']);

        self::apply_stock_movement($item_id, $qty, 'damage', 'damage', 0, $reason . ' - ' . $notes);

        wp_send_json_success(['message' => 'Damage recorded']);
    }

    /**
     * Correction
     */
    public static function correct_stock() {
        $item_id = intval($_POST['item_id']);
        $new_qty = intval($_POST['new_qty']);
        $notes   = sanitize_text_field($_POST['notes']);

        self::apply_stock_movement($item_id, $new_qty, 'correction', 'correction', 0, $notes);

        wp_send_json_success(['message' => 'Stock corrected']);
    }

    /**
     * Returns
     */
    public static function return_stock() {
        $item_id = intval($_POST['item_id']);
        $qty     = intval($_POST['qty']);
        $reason  = sanitize_text_field($_POST['reason']);
        $notes   = sanitize_text_field($_POST['notes']);

        self::apply_stock_movement($item_id, $qty, 'return', 'return', 0, $reason . ' - ' . $notes);

        wp_send_json_success(['message' => 'Return recorded']);
    }
}
