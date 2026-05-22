<?php
/**
 * Plugin Name: PCA Store Manager
 * Description: Multi-campus bookshop and uniform manager for PCA.
 * Version: 1.5.22
 * Author: PCA
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PCA_STORE_VERSION', '0.1.0' );
define( 'PCA_STORE_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PCA_STORE_URL', plugin_dir_url( __FILE__ ) );

require_once PCA_STORE_MANAGER_PATH . 'includes/class-activator.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/class-permissions.php';
require_once PCA_STORE_MANAGER_PATH . 'admin/class-admin-menu.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/class-admin-tabs.php';

require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-items-controller.php';


require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-stock-controller.php';


require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-reports-controller.php';


require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-settings-controller.php';



register_activation_hook( __FILE__, [ 'PCA_Store_Activator', 'activate' ] );

add_action( 'plugins_loaded', function() {
    PCA_Store_Permissions::init();
    PCA_Store_Admin_Menu::init();
});

add_action('init', function() {
    PCA_Store_Settings_Controller::init();
    PCA_Store_Items_Controller::init();
    PCA_Store_Stock_Controller::init();
    PCA_Store_Reports_Controller::init();
});


function pca_store_manager_upgrade_check() {
    $current = get_option('pca_store_db_version');

    // First install or upgrade
    if ($current !== '1.0.0') {
        PCA_Store_Activator::create_tables();
        update_option('pca_store_db_version', '1.0.0');
    }
}
add_action('plugins_loaded', 'pca_store_manager_upgrade_check');

add_action('admin_enqueue_scripts', function($hook) {

    // Load only on PCA Store pages
    if (strpos($hook, 'pca-store') === false) {
        return;
    }

    wp_enqueue_script(
        'pca-store-admin-js',
        PCA_STORE_URL . 'assets/js/admin.js',
        ['jquery'],
        PCA_STORE_VERSION,
        true
    );

    wp_localize_script(
        'pca-store-admin-js',
        'pcaStore',
        ['ajaxurl' => admin_url('admin-ajax.php')]
    );
});


add_action('wp_ajax_pca_debug_test', function() {
    wp_send_json_success([
        'settings_controller_exists' => class_exists('PCA_Store_Settings_Controller'),
        'actions_registered' => has_action('wp_ajax_pca_settings_save_school'),
    ]);
});
