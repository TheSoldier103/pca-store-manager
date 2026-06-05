<?php

if (!defined('ABSPATH')) exit;

class PCA_Store_Helpers {

    public static function get_user_campus() {
        global $wpdb;

        // Get all campuses
        $campus_table = $wpdb->prefix . 'pca_store_campuses';
        $campuses = $wpdb->get_results("SELECT id, name FROM $campus_table");

        foreach ($campuses as $campus) {

            // Convert campus name to capability format
            // Example: Ughelli → ugh → pca_store_campus_ugh
            $slug = strtolower($campus->name);
            $slug = substr($slug, 0, 3); // ugh, oko, etc.

            $cap = 'pca_store_campus_' . $slug;

            if (current_user_can($cap)) {
                return intval($campus->id);
            }
        }

        return null; // Admin or unrestricted role
    }

}
