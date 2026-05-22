<h2 class="title">Stationery Items</h2>

<button class="button button-primary" id="pca-add-stationery-btn">Add Stationery Item</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Type</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th width="120">Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_items';

        $items = $wpdb->get_results("
            SELECT * FROM $table
            WHERE department = 'stationery'
            ORDER BY name ASC
        ");

        if ($items) {
            foreach ($items as $item) {
                echo '<tr>';
                echo '<td>' . esc_html($item->name) . '</td>';
                echo '<td>' . esc_html($item->item_type) . '</td>';
                echo '<td>₦' . number_format($item->selling_price, 2) . '</td>';
                echo '<td>' . intval($item->current_stock) . '</td>';
                echo '<td>' . esc_html($item->status) . '</td>';
                echo '<td>
                        <a href="#" class="button pca-edit-stationery" data-id="' . $item->id . '">Edit</a>
                        <a href="#" class="button button-danger pca-delete-stationery" data-id="' . $item->id . '">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6">No stationery items found.</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php include __DIR__ . '/add-stationery-modal.php'; ?>
