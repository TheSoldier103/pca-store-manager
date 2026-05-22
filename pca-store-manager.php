<?php
/**
 * Plugin Name: PCA Store Manager
 * Description: Multi-campus bookshop and uniform manager for PCA.
 * Version: 1.0.0
 * Author: PCA
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PCA_STORE_VERSION', '0.1.0' );
define( 'PCA_STORE_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'PCA_STORE_URL', plugin_dir_url( __FILE__ ) );

require_once PCA_STORE_MANAGER_PATH . 'includes/class-activator.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/class-permissions.php';
require_once PCA_STORE_MANAGER_PATH . 'admin/class-admin-menu.php';

register_activation_hook( __FILE__, [ 'PCA_Store_Activator', 'activate' ] );

add_action( 'plugins_loaded', function() {
    PCA_Store_Permissions::init();
    PCA_Store_Admin_Menu::init();
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

