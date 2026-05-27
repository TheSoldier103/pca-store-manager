<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'books';

// Base tabs for all users
$tabs = [
    'books'      => 'Books',
    'packs'      => 'Book Packs',
    'stationery' => 'Stationery',
    'uniforms'   => 'Uniforms',
    'uniformpacks' => 'Uniform Packs'
];

// Only users with report access can see Low Stock
if ( current_user_can('pca_store_view_reports') ) {
    $tabs['lowstock'] = 'Low Stock';
}

// Validate active tab BEFORE rendering or switching
$allowed_tabs = array_keys($tabs);

if (!in_array($active_tab, $allowed_tabs)) {
    $active_tab = 'books';
}

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

    case 'uniforms':
        include __DIR__ . '/items/uniforms-list.php';
        break;

    case 'uniformpacks':
        include __DIR__ . '/items/uniformpacks-list.php';
        break;

    case 'lowstock':
        include __DIR__ . '/items/lowstock-list.php';
        break;
}

include __DIR__ . '/items/add-item-modal.php';
include __DIR__ . '/items/add-pack-modal.php';
// include __DIR__ . '/items/add-stationery-to-pack-modal.php';
