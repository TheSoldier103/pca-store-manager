<h2 class="title">Low Stock Items</h2>

<div class="pca-filters">

    <form method="get">
        <input type="hidden" name="page" value="pca-store-items">
        <input type="hidden" name="tab" value="lowstock">

        <label>Campus:</label>
        <select name="campus">
            <option value="">All Campuses</option>
            <?php
            global $wpdb;
            $campus_table = $wpdb->prefix . 'pca_store_campuses';
            $campuses = $wpdb->get_results("SELECT id, name FROM $campus_table ORDER BY name ASC");

            foreach ($campuses as $c) {
                $selected = (isset($_GET['campus']) && $_GET['campus'] == $c->id) ? 'selected' : '';
                echo "<option value='{$c->id}' $selected>{$c->name}</option>";
            }
            ?>
        </select>

        <label>Department:</label>
        <select name="department">
            <option value="">All</option>
            <option value="books" <?php selected($_GET['department'] ?? '', 'books'); ?>>Books</option>
            <option value="stationery" <?php selected($_GET['department'] ?? '', 'stationery'); ?>>Stationery</option>
        </select>

        <label>Item Type:</label>
        <select name="type">
            <option value="">All</option>
            <option value="single" <?php selected($_GET['type'] ?? '', 'single'); ?>>Single Items</option>
            <option value="pack" <?php selected($_GET['type'] ?? '', 'pack'); ?>>Book Packs</option>
        </select>

        <button class="button">Filter</button>
    </form>

</div>

<hr>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Department</th>
            <th>Type</th>
            <th>Class</th>
            <th>Stock</th>
            <th>Reorder Level</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        <?php
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';

        // Build WHERE conditions
        $where = ["current_stock <= reorder_level"];

        if (!empty($_GET['campus'])) {
            $campus = intval($_GET['campus']);
            $where[] = "campus_id = $campus";
        }

        if (!empty($_GET['department'])) {
            $dept = sanitize_text_field($_GET['department']);
            $where[] = "department = '$dept'";
        }

        if (!empty($_GET['type'])) {
            $type = sanitize_text_field($_GET['type']);
            $where[] = "item_type = '$type'";
        }

        $where_sql = implode(" AND ", $where);

        // Fetch low stock items
        $items = $wpdb->get_results("
            SELECT * FROM $items_table
            WHERE $where_sql
            ORDER BY current_stock ASC
        ");

        if ($items) {
            foreach ($items as $item) {

                // For packs, calculate virtual stock
                if ($item->item_type === 'pack') {

                    $children = $wpdb->get_results("
                        SELECT child_item_id, quantity
                        FROM $packs_table
                        WHERE pack_id = $item->id
                    ");

                    $virtual_stock = 999999;

                    foreach ($children as $child) {
                        $child_stock = $wpdb->get_var("
                            SELECT current_stock FROM $items_table
                            WHERE id = $child->child_item_id
                        ");

                        $possible = floor($child_stock / $child->quantity);
                        $virtual_stock = min($virtual_stock, $possible);
                    }

                    $display_stock = $virtual_stock;
                } else {
                    $display_stock = $item->current_stock;
                }

                echo "<tr>
                        <td>{$item->name}</td>
                        <td>{$item->department}</td>
                        <td>{$item->item_type}</td>
                        <td>{$item->class_level}</td>
                        <td>{$display_stock}</td>
                        <td>{$item->reorder_level}</td>
                        <td>{$item->status}</td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="7">No low stock items found.</td></tr>';
        }
        ?>
    </tbody>
</table>
