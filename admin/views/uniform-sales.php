<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'uniforms';

$tabs = [
    'uniforms'      => 'Uniform Sales',
    'uniform-packs' => 'Uniform Packs Sale',
    'recent'        => 'Recent Sales'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'uniforms':
        include __DIR__ . '/uniform-sales/uniforms.php';
        break;

    case 'packs':
        include __DIR__ . '/uniform-sales/packs.php';
        break;

    case 'recent':
        include __DIR__ . '/uniform-sales/recent-sales.php';
        break;
}
