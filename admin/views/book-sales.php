<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'books';

$tabs = [
    'books'      => 'Books Sale',
    'packs'      => 'Book Packs Sale',
    'stationery' => 'Stationery Sale',
    'owed-items' => 'Owed Items',
    'recent'     => 'Recent Sales'

];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'books':
        include __DIR__ . '/book-sales/books.php';
        break;

    case 'packs':
        include __DIR__ . '/book-sales/packs.php';
        break;

    case 'stationery':
        include __DIR__ . '/book-sales/stationery-sale.php';
        break;
    
    case 'owed-items':
        include __DIR__ . '/book-sales/owed-items.php';
        break;

    case 'recent':
        include __DIR__ . '/book-sales/recent-sales.php';
        break;
}
