<?php

class PCA_Store_Activator {

    public static function activate() {
        self::create_tables();
        update_option('pca_store_db_version', '1.5.22');
    }

    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $prefix  = $wpdb->prefix . 'pca_store_';

        $tables = [];

        // ---------------------------------------------------------
        // Schools
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}schools (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            address TEXT NULL,
            phone VARCHAR(100) NULL,
            email VARCHAR(255) NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY slug (slug)
        ) $charset;";

        // ---------------------------------------------------------
        // Campuses
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}campuses (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            address TEXT NULL,
            phone VARCHAR(100) NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY school_id (school_id),
            KEY slug (slug)
        ) $charset;";

        // ---------------------------------------------------------
        // Departments
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}departments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) NOT NULL UNIQUE,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY code (code)
        ) $charset;";


        // ---------------------------------------------------------
        // Items
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NOT NULL,
            campus_id BIGINT UNSIGNED NOT NULL,
            department VARCHAR(50) NOT NULL,
            sku VARCHAR(50) NULL,
            name VARCHAR(255) NOT NULL,
            item_type VARCHAR(100) NULL,
            class_level VARCHAR(100) NULL,
            subject VARCHAR(100) NULL,
            size VARCHAR(50) NULL,
            gender VARCHAR(50) NULL,
            color VARCHAR(50) NULL,
            supplier_id BIGINT UNSIGNED NULL,
            cost_price DECIMAL(12,2) DEFAULT 0.00,
            selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            current_stock INT NOT NULL DEFAULT 0,
            reorder_level INT NOT NULL DEFAULT 0,
            status VARCHAR(30) DEFAULT 'active',
            notes TEXT NULL,
            created_by BIGINT UNSIGNED NULL,
            updated_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY school_id (school_id),
            KEY campus_id (campus_id),
            KEY department (department),
            KEY supplier_id (supplier_id),
            KEY status (status),
            KEY name (name)
        ) $charset;";


        // ---------------------------------------------------------
        // Pack Items
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}item_packs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            pack_id BIGINT UNSIGNED NOT NULL,
            child_item_id BIGINT UNSIGNED NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY pack_id (pack_id),
            KEY child_item_id (child_item_id)
        ) $charset;";


        // ---------------------------------------------------------
        // Suppliers
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}suppliers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NOT NULL,
            campus_id BIGINT UNSIGNED NULL,
            department VARCHAR(50) NULL,
            name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255) NULL,
            phone VARCHAR(100) NULL,
            email VARCHAR(255) NULL,
            address TEXT NULL,
            supplier_type VARCHAR(100) NULL,
            notes TEXT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY school_id (school_id),
            KEY campus_id (campus_id),
            KEY department (department)
        ) $charset;";

        // ---------------------------------------------------------
        // Sales
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}sales (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NOT NULL,
            campus_id BIGINT UNSIGNED NOT NULL,
            receipt_no VARCHAR(100) NOT NULL UNIQUE,
            department VARCHAR(50) NOT NULL,
            sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            payment_method VARCHAR(50) NOT NULL,
            student_name VARCHAR(255) NULL,
            student_class VARCHAR(100) NULL,
            customer_name VARCHAR(255) NULL,
            subtotal DECIMAL(12,2) DEFAULT 0.00,
            discount DECIMAL(12,2) DEFAULT 0.00,
            total_amount DECIMAL(12,2) DEFAULT 0.00,
            amount_paid DECIMAL(12,2) DEFAULT 0.00,
            balance DECIMAL(12,2) DEFAULT 0.00,
            status VARCHAR(30) DEFAULT 'completed',
            notes TEXT NULL,
            sold_by BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY school_id (school_id),
            KEY campus_id (campus_id),
            KEY department (department),
            KEY sale_date (sale_date),
            KEY sold_by (sold_by),
            KEY payment_method (payment_method),
            KEY status (status)
        ) $charset;";

        // ---------------------------------------------------------
        // Sale Items
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}sale_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            sale_id BIGINT UNSIGNED NOT NULL,
            item_id BIGINT UNSIGNED NOT NULL,
            item_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(12,2) NOT NULL,
            total_price DECIMAL(12,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sale_id (sale_id),
            KEY item_id (item_id)
        ) $charset;";

        // ---------------------------------------------------------
        // Stock Movements
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}stock_movements (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NOT NULL,
            campus_id BIGINT UNSIGNED NOT NULL,
            item_id BIGINT UNSIGNED NOT NULL,
            department VARCHAR(50) NOT NULL,
            movement_type VARCHAR(50) NOT NULL,
            quantity INT NOT NULL,
            stock_before INT NOT NULL,
            stock_after INT NOT NULL,
            reference_type VARCHAR(50) NULL,
            reference_id BIGINT UNSIGNED NULL,
            supplier_id BIGINT UNSIGNED NULL,
            reason VARCHAR(255) NULL,
            notes TEXT NULL,
            created_by BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY school_id (school_id),
            KEY campus_id (campus_id),
            KEY item_id (item_id),
            KEY movement_type (movement_type),
            KEY created_by (created_by)
        ) $charset;";

        // ---------------------------------------------------------
        // Audit Log
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}audit_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            school_id BIGINT UNSIGNED NULL,
            campus_id BIGINT UNSIGNED NULL,
            department VARCHAR(50) NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            object_type VARCHAR(100) NOT NULL,
            object_id BIGINT UNSIGNED NULL,
            old_value LONGTEXT NULL,
            new_value LONGTEXT NULL,
            ip_address VARCHAR(100) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY school_id (school_id),
            KEY campus_id (campus_id),
            KEY created_at (created_at)
        ) $charset;";

        // ---------------------------------------------------------
        // User Access
        // ---------------------------------------------------------
        $tables[] = "CREATE TABLE {$prefix}user_access (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            school_id BIGINT UNSIGNED NULL,
            campus_id BIGINT UNSIGNED NULL,
            department VARCHAR(50) NULL,
            role_key VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY school_id (school_id),
            KEY campus_id (campus_id)
        ) $charset;";

        // ---------------------------------------------------------
        // Execute all tables
        // ---------------------------------------------------------
        foreach ($tables as $sql) {
            dbDelta($sql);
        }
    }
}
