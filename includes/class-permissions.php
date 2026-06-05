<?php

class PCA_Store_Permissions {

    protected static $caps = [
        'pca_store_view_dashboard',
        'pca_store_record_sales',
        'pca_store_manage_items',
        'pca_store_manage_stock',
        'pca_store_manage_suppliers',
        'pca_store_view_reports',
        'pca_store_view_audit_log',
        'pca_store_manage_schools',
        'pca_store_manage_campuses',
        'pca_store_manage_settings',
        'pca_store_campus_ugh',
        'pca_store_campus_oku',
    ];

    public static function init() {
        add_action( 'init', [ __CLASS__, 'add_caps' ] );
    }

    public static function add_caps() {
        $roles = [
            'administrator' => self::$caps,
        ];

        foreach ( $roles as $role_key => $caps ) {
            $role = get_role( $role_key );
            if ( ! $role ) {
                continue;
            }
            foreach ( $caps as $cap ) {
                if ( ! $role->has_cap( $cap ) ) {
                    $role->add_cap( $cap );
                }
            }
        }
    }
}
