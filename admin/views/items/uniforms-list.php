<h2 class="title">Uniform Items</h2>

<button class="button button-primary" id="pca-add-uniform-btn">Add Uniform Item</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Gender</th>
            <th>Color</th>
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

        $items_table = $wpdb->prefix . 'pca_store_items';
        $dept_table  = $wpdb->prefix . 'pca_store_departments';
        $stock_table = $wpdb->prefix . 'pca_item_stock';

        // Get Uniforms department ID
        $uniforms_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table
            WHERE LOWER(name) = 'uniforms'
            LIMIT 1
        ");

        $items = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $items_table
            WHERE department_id = %d
            AND item_type = 'single'
            AND status != 'deleted'
            ORDER BY name ASC
        ", $uniforms_dept_id));

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
                echo '<td>' . esc_html($item->size) . '</td>';
                echo '<td>' . esc_html($item->gender) . '</td>';
                echo '<td>' . esc_html($item->color) . '</td>';
                echo '<td>₦' . number_format($item->selling_price) . '</td>';
                echo '<td>' . $ughelli_stock . '</td>';
                echo '<td>' . $okuokoko_stock . '</td>';
                echo '<td>' . esc_html($item->status) . '</td>';
                echo '<td style="white-space: nowrap;">
                        <a href="#" class="pca-edit-item" data-id="' . $item->id . '">Edit</a> |
                        <a href="#" class="button-danger pca-delete-item" data-id="' . $item->id . '">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="9">No uniform items found.</td></tr>';
        }
        ?>
    </tbody>
</table>
