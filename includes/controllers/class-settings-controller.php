<?php

class PCA_Store_Settings_Controller {

    public static function init() {
        add_action('wp_ajax_pca_settings_save_roles', [__CLASS__, 'save_roles_settings']);
        // other settings actions...
    }

    public static function save_roles_settings() {

        $can_edit_stock      = !empty($_POST['can_edit_stock']) ? 1 : 0;
        $can_view_reports    = !empty($_POST['can_view_reports']) ? 1 : 0;
        $can_manage_settings = !empty($_POST['can_manage_settings']) ? 1 : 0;

        update_option('pca_roles_can_edit_stock', $can_edit_stock);
        update_option('pca_roles_can_view_reports', $can_view_reports);
        update_option('pca_roles_can_manage_settings', $can_manage_settings);

        wp_send_json_success(['message' => 'Roles & permissions saved']);
    }
}
