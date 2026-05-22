<?php

class PCA_Store_Admin_Menu {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
    }

    public static function register_menu() {

        // Main menu
        add_menu_page(
            'PCA Store Manager',
            'PCA Store',
            'pca_store_view_dashboard',
            'pca-store-dashboard',
            [__CLASS__, 'render_dashboard'],
            'dashicons-store',
            26
        );

        // Record Sale
        add_submenu_page(
            'pca-store-dashboard',
            'Record Sale',
            'Record Sale',
            'pca_store_record_sales',
            'pca-store-sales',
            [__CLASS__, 'render_sales_form']
        );

        // Stock Update
        add_submenu_page(
            'pca-store-dashboard',
            'Stock Update',
            'Stock Update',
            'pca_store_manage_stock',
            'pca-store-stock',
            [__CLASS__, 'render_stock_update']
        );

        // Items
        add_submenu_page(
            'pca-store-dashboard',
            'Items',
            'Items',
            'pca_store_manage_items',
            'pca-store-items',
            [__CLASS__, 'render_items']
        );

        // Suppliers
        add_submenu_page(
            'pca-store-dashboard',
            'Suppliers',
            'Suppliers',
            'pca_store_manage_suppliers',
            'pca-store-suppliers',
            [__CLASS__, 'render_suppliers']
        );

        // // Reports
        // if (get_option('pca_roles_can_view_reports')) {
        //     add_submenu_page(
        //         'pca-store',
        //         'Reports',
        //         'Reports',
        //         'manage_options',
        //         'pca-store-reports',
        //         [$this, 'render_reports_page']
        //     );
        // }

        // Reports
        add_submenu_page(
            'pca-store-dashboard',
            'Reports',
            'Reports',
            'pca_store_view_reports',
            'pca-store-reports',
            [__CLASS__, 'render_reports']
        );

        // Audit Log
        add_submenu_page(
            'pca-store-dashboard',
            'Audit Log',
            'Audit Log',
            'pca_store_view_audit_log',
            'pca-store-audit-log',
            [__CLASS__, 'render_audit_log']
        );

        // // Settings
        // if (get_option('pca_roles_can_manage_settings')) {
        //     add_submenu_page(
        //         'pca-store',
        //         'Settings',
        //         'Settings',
        //         'manage_options',
        //         'pca-store-settings',
        //         [$this, 'render_settings_page']
        //     );
        // }

        add_submenu_page(
            'pca-store-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'pca-store-settings',
            [__CLASS__, 'render_settings']
        );

    }

    // Render functions
    public static function render_dashboard() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/dashboard.php';
    }

    public static function render_sales_form() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/record-sale.php';
    }

    public static function render_stock_update() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/stock-update.php';
    }

    public static function render_items() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/items.php';
    }

    public static function render_suppliers() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/suppliers.php';
    }

    public static function render_reports() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/reports.php';
    }

    public static function render_audit_log() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/audit-log.php';
    }

    public static function render_settings() {
        include PCA_STORE_MANAGER_PATH . 'admin/views/settings.php';
    }
}
