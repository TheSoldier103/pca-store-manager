<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'schools';

$tabs = [
    'schools'     => 'Schools',
    'campuses'    => 'Campuses',
    'departments' => 'Departments',
    'receipt'     => 'Receipt Format',
    'advanced'    => 'Advanced'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'schools':
        include __DIR__ . '/settings/schools.php';
        break;

    case 'campuses':
        include __DIR__ . '/settings/campuses.php';
        break;

    case 'departments':
        include __DIR__ . '/settings/departments.php';
        break;

    case 'receipt':
        include __DIR__ . '/settings/receipt.php';
        break;

    case 'advanced':
        include __DIR__ . '/settings/advanced.php';
        break;
}
