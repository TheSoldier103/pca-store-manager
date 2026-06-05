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

        $rows = $wpdb->get_results("
            SELECT * FROM $sales
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
                        <td>{$s->sold_by}</td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="8">No recent sales found.</td></tr>';
        }
        ?>
    </tbody>
</table>
