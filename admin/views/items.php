<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'books';

$tabs = [
    'books'      => 'Books',
    'packs'      => 'Book Packs',
    'stationery' => 'Stationery',
    'lowstock'   => 'Low Stock'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'books':
        include __DIR__ . '/items/books-list.php';
        break;

    case 'packs':
        include __DIR__ . '/items/packs-list.php';
        break;

    case 'stationery':
        include __DIR__ . '/items/stationery-list.php';
        break;

    case 'lowstock':
        include __DIR__ . '/items/lowstock-list.php';
        break;
}
