<?php

class PCA_Store_Stock_Controller {

    public static function init() {
        add_action('wp_ajax_pca_store_add_stock', [__CLASS__, 'add_stock']);
        add_action('wp_ajax_pca_store_apply_stock_movement', [__CLASS__, 'apply_stock_movement']);
        add_action('wp_ajax_pca_store_damage_stock', [__CLASS__, 'damage_stock']);
        add_action('wp_ajax_pca_store_correct_stock', [__CLASS__, 'correct_stock']);
        add_action('wp_ajax_pca_store_return_stock', [__CLASS__, 'return_stock']);
        add_action('wp_ajax_pca_store_get_item_stock', [__CLASS__, 'pca_store_get_item_stock']);
    }


    public static function pca_store_get_item_stock() {
        global $wpdb;

        $item_id   = intval($_POST['item_id'] ?? 0);
        $campus_id = intval($_POST['campus_id'] ?? 0);

        if ($item_id <= 0 || $campus_id <= 0) {
            wp_send_json_error(['message' => 'Invalid parameters']);
        }

        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        $stock = $wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM $stock_table WHERE item_id = %d AND campus_id = %d",
            $item_id,
            $campus_id
        ));

        wp_send_json_success([
            'stock' => intval($stock)
        ]);
    }


    /**
     * Core stock movement engine
     */
    public static function apply_stock_movement($item_id, $qty, $campus_id, $movement_type, $reference_type, $reference_id, $notes = ''){
        global $wpdb;

        $stock_table    = $wpdb->prefix . 'pca_store_item_stock';
        $movement_table = $wpdb->prefix . 'pca_store_stock_movements';

        // Determine campus
        // $campus_id = PCA_Store_Helpers::get_user_campus() ?: intval($_POST['campus_id']);
        $campus_id = intval($campus_id);


        // Get current stock for this campus
        $current_stock = $wpdb->get_var($wpdb->prepare("
            SELECT stock FROM $stock_table
            WHERE item_id = %d AND campus_id = %d
        ", $item_id, $campus_id));

        if ($current_stock === null) {
            $current_stock = 0;
        }

        $stock_before = $current_stock;

        // Apply movement
        if ($movement_type === 'add' || $movement_type === 'return') {
            $new_stock = $current_stock + $qty;
        } elseif ($movement_type === 'fulfill_owed') {
            $new_stock = $current_stock - $qty;
        } elseif ($movement_type === 'damage' || $movement_type === 'sale') {
            $new_stock = $current_stock - $qty;
        } elseif ($movement_type === 'correction') {
            $new_stock = $qty;
        } else {
            return false;
        }

        // Update per-campus stock
        $wpdb->update(
            $stock_table,
            ['stock' => $new_stock],
            ['item_id' => $item_id, 'campus_id' => $campus_id]
        );

        // Log movement
        $wpdb->insert($movement_table, [
            'item_id'        => $item_id,
            'campus_id'      => $campus_id,
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

        $campus_id = PCA_Store_Helpers::get_user_campus() ?: intval($_POST['campus_id']);
        $item_id   = intval($_POST['item_id']);
        $qty       = intval($_POST['qty']);

        if (!$campus_id || !$item_id || $qty <= 0) {
            wp_send_json_error(['message' => 'Invalid stock data']);
        }

        // Ensure stock row exists
        $existing = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM $stock_table
            WHERE item_id = %d AND campus_id = %d
        ", $item_id, $campus_id));

        if (!$existing) {
            // Create stock row with 0 so movement engine can update it
            $wpdb->insert($stock_table, [
                'item_id'   => $item_id,
                'campus_id' => $campus_id,
                'stock'     => 0
            ]);
        }

        // Apply movement (this updates stock + logs movement)
        PCA_Store_Stock_Controller::apply_stock_movement(
            $item_id,
            $qty,
            $campus_id,
            'add',
            'stock_update',
            0,
            'Manual stock addition'
        );

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
