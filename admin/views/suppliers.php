<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'books';

$tabs = [
    'books'    => 'Books Suppliers',
    'uniforms' => 'Uniform Suppliers',
    'all'      => 'All Suppliers'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'books':
        include __DIR__ . '/suppliers/books-suppliers.php';
        break;

    case 'uniforms':
        include __DIR__ . '/suppliers/uniform-suppliers.php';
        break;

    case 'all':
        include __DIR__ . '/suppliers/all-suppliers.php';
        break;
}
