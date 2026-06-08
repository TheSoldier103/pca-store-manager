<h2 class="title">Stock Movement History</h2>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Item</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Before</th>
            <th>After</th>
            <th>Reference</th>
            <th>User</th>
        </tr>
    </thead>

    <tbody>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_stock_movements';
        $items = $wpdb->prefix . 'pca_store_items';

        $rows = $wpdb->get_results("
            SELECT m.*, i.name AS item_name
            FROM $table m
            LEFT JOIN $items i ON i.id = m.item_id
            ORDER BY m.created_at DESC
            LIMIT 200
        ");

        if ($rows) {
            foreach ($rows as $m) {
                $user = get_userdata($s->sold_by);
                $username = $user ? esc_html($user->display_name) : 'Unknown';
                
                echo "<tr>
                        <td>{$m->created_at}</td>
                        <td>{$m->item_name}</td>
                        <td>{$m->movement_type}</td>
                        <td>{$m->quantity}</td>
                        <td>{$m->stock_before}</td>
                        <td>{$m->stock_after}</td>
                        <td>{$m->reference_type} #{$m->reference_id}</td>
                        <td>{$username}</td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="8">No stock movements found.</td></tr>';
        }
        ?>
    </tbody>
</table>
