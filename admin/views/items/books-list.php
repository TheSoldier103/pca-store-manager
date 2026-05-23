<h2 class="title">Books Inventory</h2>

<button class="button button-primary" id="pca-add-book-btn">Add New Book</button>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Class</th>
            <th>Subject</th>
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

        $books = $wpdb->get_results("
            SELECT * FROM $table
            WHERE department_id = X
            AND item_type = 'single'
            ORDER BY name ASC
        ");

        if ($books) {
            foreach ($books as $book) {
                echo '<tr>';
                echo '<td>' . esc_html($book->name) . '</td>';
                echo '<td>' . esc_html($book->class_level) . '</td>';
                echo '<td>' . esc_html($book->subject) . '</td>';
                echo '<td>₦' . number_format($book->selling_price, 2) . '</td>';
                echo '<td>' . intval($book->current_stock) . '</td>';
                echo '<td>' . esc_html($book->status) . '</td>';
                echo '<td>
                        <a href="#" class="button">Edit</a>
                        <a href="#" class="button button-danger">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">No books found.</td></tr>';
        }
        ?>
    </tbody>
</table>
