<h2 class="title">Books Inventory</h2>

<button class="button button-primary" id="pca-add-book-btn">Add New Book</button>
<button class="button" id="pca-import-books-btn">Import CSV</button>

<table class="wp-list-table widefat fixed striped pca-items-table">

    <thead>
        <tr>
            <th>Name</th>
            <th>Class</th>
            <th>Subject</th>
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
        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        $ughelli_stock = $wpdb->get_var($wpdb->prepare("
            SELECT stock FROM $stock_table
            WHERE item_id = %d AND campus_id = 1
        ", $book->id));

        $okuokoko_stock = $wpdb->get_var($wpdb->prepare("
            SELECT stock FROM $stock_table
            WHERE item_id = %d AND campus_id = 2
        ", $book->id));

        $ughelli_stock = intval($ughelli_stock);
        $okuokoko_stock = intval($okuokoko_stock);


        // Get Books department_id dynamically
        $books_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table 
            WHERE LOWER(name) = 'books' 
            LIMIT 1
        ");

        $books = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $items_table
            WHERE department_id = %d
            AND item_type = 'single'
            AND status != 'deleted'
            ORDER BY name ASC
        ", $books_dept_id));

        if ($books) {
            foreach ($books as $book) {
                echo '<tr>';
                echo '<td>' . esc_html($book->name) . '</td>';
                echo '<td>' . esc_html($book->class_level) . '</td>';
                echo '<td>' . esc_html($book->subject) . '</td>';
                echo '<td>₦' . number_format($book->selling_price) . '</td>';
                echo '<td>' . $ughelli_stock . '</td>';
                echo '<td>' . $okuokoko_stock . '</td>';
                echo '<td>' . esc_html($book->status) . '</td>';
                echo '<td style="white-space: nowrap;">';
                echo '<a href="#" class="pca-edit-item" data-id="' . $book->id . '">Edit</a> | ';
                echo '<a href="#" class="button-danger pca-delete-item" data-id="' . $book->id . '">Delete</a>';                
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">No books found.</td></tr>';
        }
        ?>
    </tbody>
</table>
