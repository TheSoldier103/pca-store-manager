<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'books-dashboard';

$tabs = [
    'books-dashboard'        => 'Books Dashboard',
    'uniforms-dashboard'     => 'Uniforms Dashboard'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'books-dashboard':
        include __DIR__ . '/dashboard/books-dashboard.php';
        break;

    case 'uniforms-dashboard':
        include __DIR__ . '/dashboard/uniforms-dashboard.php';
        break;
}
