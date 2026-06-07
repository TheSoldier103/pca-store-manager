<h2 class="title">Uniform Packs</h2>

<button class="button button-primary" id="pca-add-uniform-pack-btn">Add Uniform Pack</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Pack Name</th>
            <th>Items</th>
            <th>Price</th>
            <th>Status</th>
            <th width="120">Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $dept_table  = $wpdb->prefix . 'pca_store_departments';

        $uniforms_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table
            WHERE LOWER(name) = 'uniforms'
            LIMIT 1
        ");

        $packs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $items_table
            WHERE department_id = %d
            AND item_type = 'pack'
            AND status != 'deleted'
            ORDER BY name ASC
        ", $uniforms_dept_id));

        if ($packs) {
            foreach ($packs as $pack) {
                echo '<tr>';
                echo '<td>' . esc_html($pack->name) . '</td>';
                echo '<td>' . esc_html($pack->child_items) . '</td>';
                echo '<td>₦' . number_format($pack->selling_price) . '</td>';
                echo '<td>' . esc_html($pack->status) . '</td>';
                echo '<td style="white-space: nowrap;">
                        <a href="#" class="pca-edit-pack" data-id="' . $pack->id . '">Edit</a> |
                        <a href="#" class="button-danger pca-delete-pack" data-id="' . $pack->id . '">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">No uniform packs found.</td></tr>';
        }
        ?>
    </tbody>
</table>
