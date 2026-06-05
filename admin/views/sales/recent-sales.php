<?php $fixed_campus = PCA_Store_Helpers::get_user_campus(); ?>

<form method="get">
    <input type="hidden" name="page" value="pca-store-sales">

    <?php if (!$fixed_campus): ?>
        <select name="campus_id">
            <option value="">All Campuses</option>
            <?php
            global $wpdb;
            $campus_table = $wpdb->prefix . 'pca_store_campuses';
            $rows = $wpdb->get_results("SELECT id, name FROM $campus_table WHERE is_active = 1");

            foreach ($rows as $c) {
                $selected = ($_GET['campus_id'] ?? '') == $c->id ? 'selected' : '';
                echo "<option value='{$c->id}' $selected>{$c->name}</option>";
            }
            ?>
        </select>
    <?php else: ?>
        <input type="hidden" name="campus_id" value="<?php echo $fixed_campus; ?>">
        <strong>
            <?php
            $name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $campus_table WHERE id = %d", $fixed_campus));
            echo esc_html($name);
            ?>
        </strong>
        <em>(auto‑assigned)</em>
    <?php endif; ?>

    <button class="button">Filter</button>
</form>


<h2 class="title">Recent Sales</h2>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Receipt No</th>
            <th>Items</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Discount</th>
            <th>Balance</th>
            <th>Method</th>
            <th>Cashier</th>
        </tr>
    </thead>

    <tbody>
        <?php
 
        global $wpdb;
        $sales = $wpdb->prefix . 'pca_store_sales';

        $fixed_campus = PCA_Store_Helpers::get_user_campus();
        $selected_campus = intval($_GET['campus_id'] ?? 0);

        // Campus-bound users override admin selection
        $campus_id = $fixed_campus ?: $selected_campus;

        $where = "WHERE 1=1";

        if ($campus_id) {
            $where .= $wpdb->prepare(" AND campus_id = %d", $campus_id);
        }

        $rows = $wpdb->get_results("
            SELECT *
            FROM $sales
            $where
            ORDER BY sale_date DESC
            LIMIT 50
        ");


       

        if ($rows) {
            foreach ($rows as $s) {
                echo "<tr>
                        <td>{$s->sale_date}</td>
                        <td>{$s->receipt_no}</td>
                        <td>{$s->department}</td>
                        <td>₦" . number_format($s->total_amount, 2) . "</td>
                        <td>₦" . number_format($s->amount_paid, 2) . "</td>
                        <td>₦" . number_format($s->discount, 2) . "</td>
                        <td>₦" . number_format($s->balance, 2) . "</td>
                        <td>{$s->payment_method}</td>
                        $user = get_userdata($row->sold_by);
                        <td>echo esc_html($user_info->display_name);</td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="8">No recent sales found.</td></tr>';
        }
        ?>
    </tbody>
</table>
