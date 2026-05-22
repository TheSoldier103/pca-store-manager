<?php

class PCA_Store_Admin_Menu {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
    }

    public static function register_menu() {
        add_menu_page(
            'PCA Store Manager',
            'PCA Store',
            'pca_store_view_dashboard',
            'pca-store-dashboard',
            [ __CLASS__, 'render_dashboard' ],
            'dashicons-store',
            26
        );

        add_submenu_page(
            'pca-store-dashboard',
            'Record Sale',
            'Record Sale',
            'pca_store_record_sales',
            'pca-store-sales',
            [ __CLASS__, 'render_sales_form' ]
        );

        add_submenu_page(
            'pca-store-dashboard',
            'Stock Update',
            'Stock Update',
            'pca_store_manage_stock',
            'pca-store-stock',
            [ __CLASS__, 'render_stock_update' ]
        );

        // Items, Suppliers, Reports, Audit Log, Settings...
    }

    public static function render_dashboard() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/dashboard.php';
    }

    public static function render_sales_form() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/record-sale.php';
    }

    public static function render_stock_update() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/stock-update.php';
    }
}
