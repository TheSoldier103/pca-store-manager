<h2 class="title">Book Packs</h2>

<button class="button button-primary" id="pca-add-pack-btn">Add New Pack</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Pack Name</th>
            <th>Class</th>
            <th>Books</th>
            <th>Price</th>
            <th>Virtual Stock</th>
            <th width="120">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $wpdb;
        $items = $wpdb->prefix . 'pca_store_items';
        $packs = $wpdb->prefix . 'pca_store_item_packs';

        $pack_items = $wpdb->get_results("
            SELECT i.*, COUNT(p.child_item_id) AS book_count
            FROM $items i
            LEFT JOIN $packs p ON p.pack_id = i.id
            WHERE department_id = X
            AND i.item_type = 'pack'
            GROUP BY i.id
            ORDER BY i.name ASC
        ");

        if ($pack_items) {
            foreach ($pack_items as $pack) {

                // Calculate virtual stock
                $child_items = $wpdb->get_results("
                    SELECT child_item_id, quantity
                    FROM $packs
                    WHERE pack_id = $pack->id
                ");

                $virtual_stock = 999999;
                foreach ($child_items as $child) {
                    $stock = $wpdb->get_var("
                        SELECT current_stock FROM $items
                        WHERE id = $child->child_item_id
                    ");
                    $possible = floor($stock / $child->quantity);
                    $virtual_stock = min($virtual_stock, $possible);
                }

                echo '<tr>';
                echo '<td>' . esc_html($pack->name) . '</td>';
                echo '<td>' . esc_html($pack->class_level) . '</td>';
                echo '<td>' . intval($pack->book_count) . '</td>';
                echo '<td>₦' . number_format($pack->selling_price, 2) . '</td>';
                echo '<td>' . intval($virtual_stock) . '</td>';
                echo '<td>
                        <a href="#" class="button">Edit</a>
                        <a href="#" class="button button-danger">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6">No packs found.</td></tr>';
        }
        ?>
    </tbody>
</table>
