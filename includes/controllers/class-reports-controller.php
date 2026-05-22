<?php

class PCA_Store_Reports_Controller {

    public static function init() {

        add_action('wp_ajax_pca_report_daily_payments', [__CLASS__, 'daily_payments']);
        add_action('wp_ajax_pca_report_sales', [__CLASS__, 'sales_report']);
        add_action('wp_ajax_pca_report_stock', [__CLASS__, 'stock_report']);
        add_action('wp_ajax_pca_report_debtors', [__CLASS__, 'debtors_report']);
        add_action('wp_ajax_pca_report_prospectus', [__CLASS__, 'prospectus_report']);
    }

    /**
     * DAILY PAYMENTS REPORT
     */
    public static function daily_payments() {
        global $wpdb;

        $sales_table = $wpdb->prefix . 'pca_store_sales';

        $date = sanitize_text_field($_GET['date'] ?? date('Y-m-d'));

        $rows = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM $sales_table
            WHERE DATE(sale_date) = %s
            ORDER BY sale_date DESC
        ", $date));

        wp_send_json_success($rows);
    }

    /**
     * SALES REPORT (Books + Uniforms)
     */
    public static function sales_report() {
        global $wpdb;

        $sales_table = $wpdb->prefix . 'pca_store_sales';

        $from = sanitize_text_field($_GET['from']);
        $to   = sanitize_text_field($_GET['to']);
        $dept = sanitize_text_field($_GET['department']);

        $where = ["DATE(sale_date) BETWEEN '$from' AND '$to'"];

        if (!empty($dept)) {
            $where[] = $wpdb->prepare("department = %s", $dept);
        }

        $where_sql = implode(" AND ", $where);

        $rows = $wpdb->get_results("
            SELECT *
            FROM $sales_table
            WHERE $where_sql
            ORDER BY sale_date DESC
        ");

        wp_send_json_success($rows);
    }

    /**
     * STOCK REPORT
     */
    public static function stock_report() {
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $movements   = $wpdb->prefix . 'pca_store_stock_movements';

        $rows = $wpdb->get_results("
            SELECT 
                i.id,
                i.name,
                i.department,
                i.current_stock,
                i.reorder_level,
                (
                    SELECT SUM(quantity)
                    FROM $movements
                    WHERE item_id = i.id AND movement_type = 'add'
                ) AS stock_in,

                (
                    SELECT SUM(quantity)
                    FROM $movements
                    WHERE item_id = i.id AND movement_type = 'sale'
                ) AS stock_out

            FROM $items_table i
            WHERE i.status = 'active'
            ORDER BY i.name ASC
        ");

        wp_send_json_success($rows);
    }

    /**
     * DEBTORS REPORT
     * (Students who owe money)
     */
    public static function debtors_report() {
        global $wpdb;

        $sales_table = $wpdb->prefix . 'pca_store_sales';

        $rows = $wpdb->get_results("
            SELECT *
            FROM $sales_table
            WHERE balance > 0
            ORDER BY sale_date DESC
        ");

        wp_send_json_success($rows);
    }

    /**
     * PROSPECTUS REPORT
     * (Expected fees vs paid)
     */
    public static function prospectus_report() {
        global $wpdb;

        $fees_table  = $wpdb->prefix . 'pca_generated_fees';
        $sales_table = $wpdb->prefix . 'pca_store_sales';

        $year = sanitize_text_field($_GET['year']);
        $term = sanitize_text_field($_GET['term']);

        $expected = $wpdb->get_results($wpdb->prepare("
            SELECT student_id, student_name, class, total_fee
            FROM $fees_table
            WHERE academic_year = %s AND term = %s
        ", $year, $term));

        $paid = $wpdb->get_results($wpdb->prepare("
            SELECT student_id, SUM(total_amount) AS paid
            FROM $sales_table
            WHERE academic_year = %s AND term = %s
            GROUP BY student_id
        ", $year, $term), OBJECT_K);

        // Merge expected + paid
        foreach ($expected as &$row) {
            $sid = $row->student_id;
            $row->paid = isset($paid[$sid]) ? $paid[$sid]->paid : 0;
            $row->balance = $row->total_fee - $row->paid;
        }

        wp_send_json_success($expected);
    }
}
