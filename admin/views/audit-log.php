<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'all';

$tabs = [
    'all'       => 'All Logs',
    'sales'     => 'Sales Logs',
    'stock'     => 'Stock Logs',
    'suppliers' => 'Supplier Logs',
    'users'     => 'User Activity'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'all':
        include __DIR__ . '/audit/all.php';
        break;

    case 'sales':
        include __DIR__ . '/audit/sales.php';
        break;

    case 'stock':
        include __DIR__ . '/audit/stock.php';
        break;

    case 'suppliers':
        include __DIR__ . '/audit/suppliers.php';
        break;

    case 'users':
        include __DIR__ . '/audit/users.php';
        break;
}
