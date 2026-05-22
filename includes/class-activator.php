<?php

class PCA_Store_Activator {

    public static function activate() {
        self::create_tables();
        flush_rewrite_rules();
    }

    protected static function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix . 'pca_store_';

        $sql_schools = "CREATE TABLE {$prefix}schools (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            address TEXT NULL,
            phone VARCHAR(100) NULL,
            email VARCHAR(255) NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql_campuses = "CREATE TABLE {$prefix}campuses (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            address TEXT NULL,
            phone VARCHAR(100) NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY  (id),
            KEY school_id (school_id)
        ) $charset_collate;";

        // TODO: items, suppliers, sales, sale_items, stock_movements, audit_log, user_access

        dbDelta( $sql_schools );
        dbDelta( $sql_campuses );
    }
}
