<?php
global $wpdb;

// Department lookup
$departments = $wpdb->get_results("
    SELECT id, name FROM {$wpdb->prefix}pca_store_departments 
    WHERE is_active = 1 ORDER BY name ASC
");
$dept_map = array_column($departments, 'name', 'id'); // [id => name]
?>

<h2 class="title">Low Stock Items</h2>

<div class="pca-filters">
    <form method="get">
        <input type="hidden" name="page" value="pca-store-items">
        <input type="hidden" name="tab" value="lowstock">

        <label>Campus:</label>
        <select name="campus">
            <option value="">All Campuses</option>
            <?php
            $campuses = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}pca_store_campuses ORDER BY name ASC");
            foreach ($campuses as $c) {
                $selected = (isset($_GET['campus']) && $_GET['campus'] == $c->id) ? 'selected' : '';
                echo "<option value='{$c->id}' $selected>{$c->name}</option>";
            }
            ?>
        </select>

        <label>Department:</label>
        <select name="department_id">
            <option value="">All</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?php echo $d->id; ?>" <?php selected($_GET['department_id'] ?? '', $d->id); ?>>
                    <?php echo esc_html($d->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Item Type:</label>
        <select name="type">
            <option value="">All</option>
            <option value="single" <?php selected($_GET['type'] ?? '', 'single'); ?>>Single Items</option>
            <option value="pack"   <?php selected($_GET['type'] ?? '', 'pack'); ?>>Book Packs</option>
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
        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';

        $where = ["current_stock <= reorder_level"];

        if (!empty($_GET['campus'])) {
            $where[] = $wpdb->prepare("campus_id = %d", intval($_GET['campus']));
        }

        if (!empty($_GET['department_id'])) {
            $where[] = $wpdb->prepare("department_id = %d", intval($_GET['department_id']));
        }

        if (!empty($_GET['type'])) {
            $where[] = $wpdb->prepare("item_type = %s", sanitize_text_field($_GET['type']));
        }

        $where_sql = implode(" AND ", $where);

        $items = $wpdb->get_results("
            SELECT * FROM $items_table
            WHERE $where_sql
            ORDER BY current_stock ASC
        ");

        if ($items) {
            foreach ($items as $item) {
                if ($item->item_type === 'pack') {
                    $children = $wpdb->get_results("
                        SELECT child_item_id, quantity
                        FROM $packs_table
                        WHERE pack_id = {$item->id}
                    ");

                    $virtual_stock = 999999;
                    foreach ($children as $child) {
                        $child_stock = $wpdb->get_var("
                            SELECT current_stock FROM $items_table
                            WHERE id = {$child->child_item_id}
                        ");
                        $virtual_stock = min($virtual_stock, floor($child_stock / $child->quantity));
                    }
                    $display_stock = $virtual_stock;
                } else {
                    $display_stock = $item->current_stock;
                }

                $dept_name = esc_html($dept_map[$item->department_id] ?? '—');

                echo "<tr>
                        <td>" . esc_html($item->name)      . "</td>
                        <td>{$dept_name}</td>
                        <td>" . esc_html($item->item_type)  . "</td>
                        <td>" . esc_html($item->class_level). "</td>
                        <td>{$display_stock}</td>
                        <td>" . intval($item->reorder_level) . "</td>
                        <td>" . esc_html($item->status)     . "</td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="7">No low stock items found.</td></tr>';
        }
        ?>
    </tbody>
</table>