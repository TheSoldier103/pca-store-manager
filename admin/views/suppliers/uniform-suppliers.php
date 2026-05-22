<h2 class="title">Uniform Suppliers</h2>

<button class="button button-primary" id="pca-add-supplier-btn">Add Supplier</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Contact Person</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Address</th>
            <th>Status</th>
            <th width="120">Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_suppliers';

        $suppliers = $wpdb->get_results("
            SELECT * FROM $table
            WHERE department = 'uniforms'
            ORDER BY name ASC
        ");

        if ($suppliers) {
            foreach ($suppliers as $s) {
                echo '<tr>';
                echo '<td>' . esc_html($s->name) . '</td>';
                echo '<td>' . esc_html($s->contact_person) . '</td>';
                echo '<td>' . esc_html($s->phone) . '</td>';
                echo '<td>' . esc_html($s->email) . '</td>';
                echo '<td>' . esc_html($s->address) . '</td>';
                echo '<td>' . ($s->is_active ? 'Active' : 'Inactive') . '</td>';
                echo '<td>
                        <a href="#" class="button pca-edit-supplier" data-id="' . $s->id . '">Edit</a>
                        <a href="#" class="button button-danger pca-delete-supplier" data-id="' . $s->id . '">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">No suppliers found.</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php include __DIR__ . '/add-supplier-modal.php'; ?>
