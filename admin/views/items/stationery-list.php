<h2 class="title">Stationery Items</h2>

<button class="button button-primary" id="pca-add-stationery-btn">Add Stationery Item</button>
<button class="button" id="pca-import-stationery-btn">Import CSV</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Type</th>
            <th>Price</th>
            <th>Ughelli</th>
            <th>Okuokoko</th>
            <th>Status</th>
            <th width="120">Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_items';
        $dept_table = $wpdb->prefix . 'pca_store_departments';
        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        $stationery_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table
            WHERE LOWER(name) = 'stationery'
            LIMIT 1
        ");

        $items = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table
            WHERE department_id = %d
            AND item_type = 'single'
            AND status != 'deleted'
            ORDER BY name ASC
        ", $stationery_dept_id));

        if ($items) {
            foreach ($items as $item) {

            $ughelli_stock = intval($wpdb->get_var($wpdb->prepare("
                SELECT stock FROM $stock_table
                WHERE item_id = %d AND campus_id = 1
            ", $item->id)));

            $okuokoko_stock = intval($wpdb->get_var($wpdb->prepare("
                SELECT stock FROM $stock_table
                WHERE item_id = %d AND campus_id = 2
            ", $item->id)));

                echo '<tr>';
                echo '<td>' . esc_html($item->name) . '</td>';
                echo '<td>' . esc_html($item->item_type) . '</td>';
                echo '<td>₦' . number_format($item->selling_price, 2) . '</td>';
                echo '<td>' . $ughelli_stock . '</td>';
                echo '<td>' . $okuokoko_stock . '</td>';
                echo '<td>' . esc_html($item->status) . '</td>';
                echo '<td style="white-space: nowrap;">';
                echo '<a href="#" class="pca-edit-item" data-id="' . $item->id . '">Edit</a> | ';
                echo '<a href="#" class="button-danger pca-delete-item" data-id="' . $item->id . '">Delete</a>';                
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6">No stationery items found.</td></tr>';
        }
        ?>
    </tbody>
</table>

<div id="pca-import-stationery-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:24px; z-index:9999; border:1px solid #ccd0d4; box-shadow:0 4px 20px rgba(0,0,0,.15); min-width:340px;">
    <h3>Import Stationery CSV</h3>
    <p style="color:#666; font-size:13px;">
        Required columns: <code>name</code>, <code>selling_price</code>, <code>reorder_level</code>,
        <code>stock_ughelli</code>, <code>stock_okuokoko</code>
    </p>
    <input type="file" id="pca-stationery-csv-file" accept=".csv"><br><br>
    <button class="button button-primary" id="pca-upload-stationery-csv">Import</button>
    <button class="button" id="pca-close-import-stationery">Cancel</button>
    <div id="pca-import-stationery-result" style="margin-top:12px;"></div>
</div>