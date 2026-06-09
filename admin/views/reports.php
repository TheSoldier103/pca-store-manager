<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'students_summary_reports';

$tabs = [
    
    'daily_sales'           => 'Daily Payments',
    'debtors_reports'          => 'Debtors',
    'prospectus_report'        => 'Prospectus Fees',
    'stock_reports'            => 'Stock Reports',
    'sales_reports'            => 'Sales Reports'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'students_summary_reports':
        include __DIR__ . '/reports/students-summary.php';
        break;

    case 'daily_payments':
        include __DIR__ . '/reports/daily-payments.php';
        break;

    case 'debtors_reports':
        include __DIR__ . '/reports/debtors.php';
        break;

    case 'prospectus_report':
        include __DIR__ . '/reports/prospectus.php';
        break;

    case 'stock_reports':
        include __DIR__ . '/reports/stock-reports.php';
        break;

    case 'sales_reports':
        include __DIR__ . '/reports/sales-reports.php';
        break;
}
