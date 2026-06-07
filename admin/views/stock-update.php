<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'add-books';

$tabs = [
    'add-books'       => 'Add Book Stock',
    'damage'    => 'Damage / Loss',
    'correction'=> 'Correction',
    'returns'   => 'Returns',
    'history'   => 'Stock History'
];

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

switch ($active_tab) {

    case 'add-books':
        include __DIR__ . '/stock/add-books.php';
        break;

    case 'add-stationery':
        include __DIR__ . '/stock/add-stationery.php';
        break;

    case 'add-uniforms':
        include __DIR__ . '/stock/add-uniforms.php';
        break;

    case 'damage':
        include __DIR__ . '/stock/damage-loss.php';
        break;

    case 'correction':
        include __DIR__ . '/stock/correction.php';
        break;

    case 'returns':
        include __DIR__ . '/stock/returns.php';
        break;

    case 'history':
        include __DIR__ . '/stock/history.php';
        break;
}
