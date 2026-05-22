<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'books';

$tabs = [
    'books'    => 'Books',
    'uniforms' => 'Uniforms',
    'lowstock' => 'Low Stock',
];

PCA_Store_Admin_Tabs::render_tabs( $tabs, $active_tab );

switch ($active_tab) {

    case 'books':
        echo '<h2>Books Inventory</h2>';
        echo '<p>Books table will go here.</p>';
        break;

    case 'uniforms':
        echo '<h2>Uniforms Inventory</h2>';
        echo '<p>Uniforms table will go here.</p>';
        break;

    case 'lowstock':
        echo '<h2>Low Stock Items</h2>';
        echo '<p>Low stock list will go here.</p>';
        break;
}
