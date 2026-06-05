<?php
/**
 * Plugin Name: PCA Store Manager
 * Description: Multi-campus bookshop and uniform manager for PCA.
 * Version: 1.6.2
 * Author: PCA
 */

if (!defined('ABSPATH')) exit;


define('PCA_STORE_VERSION', '1.6.2');
define('PCA_STORE_DB_VERSION', '1.5.23');
define('PCA_STORE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('PCA_STORE_URL', plugin_dir_url(__FILE__));


/*
|--------------------------------------------------------------------------
| LOAD REQUIRED FILES
|--------------------------------------------------------------------------
*/
require_once PCA_STORE_MANAGER_PATH . 'includes/class-activator.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/class-permissions.php';
require_once PCA_STORE_MANAGER_PATH . 'admin/class-admin-menu.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/class-admin-tabs.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/class-pca-store-helpers.php';

require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-items-controller.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-stock-controller.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-reports-controller.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-settings-controller.php';
require_once PCA_STORE_MANAGER_PATH . 'includes/controllers/class-suppliers-controller.php';


/*
|--------------------------------------------------------------------------
| ACTIVATION
|--------------------------------------------------------------------------
*/
register_activation_hook(__FILE__, ['PCA_Store_Activator', 'activate']);

/*
|--------------------------------------------------------------------------
| INITIALISE PERMISSIONS + MENU
|--------------------------------------------------------------------------
*/
add_action('plugins_loaded', function () {

    // Admin menu
    PCA_Store_Admin_Menu::init();

    // Other controller (registers AJAX actions)
    PCA_Store_Settings_Controller::init();
    PCA_Store_Items_Controller::init();
    PCA_Store_Stock_Controller::init();
    PCA_Store_Reports_Controller::init();
    PCA_Store_Suppliers_Controller::init();

    // DB upgrade
    pca_store_manager_upgrade_check();
});


/*
|--------------------------------------------------------------------------
| DATABASE UPGRADE CHECK
|--------------------------------------------------------------------------
*/
function pca_store_manager_upgrade_check()
{
    $current = get_option('pca_store_db_version');

    if ($current !== PCA_STORE_DB_VERSION) {
        PCA_Store_Activator::create_tables();
        update_option('pca_store_db_version', PCA_STORE_DB_VERSION);
    }
}


/*
|--------------------------------------------------------------------------
| ADMIN SCRIPTS
|--------------------------------------------------------------------------
*/
add_action('admin_enqueue_scripts', function ($hook) {

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

    wp_enqueue_style(
        'pca-store-admin-css',
        PCA_STORE_URL . 'assets/css/admin.css',
        [],
        PCA_STORE_VERSION
    );
});


