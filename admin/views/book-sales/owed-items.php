<?php
global $wpdb;

$owed_table   = $wpdb->prefix . 'pca_store_owed_items';
$sales_table  = $wpdb->prefix . 'pca_store_sales';
$campus_table = $wpdb->prefix . 'pca_store_campuses';

$rows = $wpdb->get_results("
    SELECT 
        o.id,
        o.item_name,
        o.qty_owed,
        o.receipt_no,
        o.status,
        s.sale_date,
        c.name AS campus_name
    FROM $owed_table o
    INNER JOIN $sales_table s ON s.id = o.sale_id
    INNER JOIN $campus_table c ON c.id = o.campus_id
    ORDER BY o.status ASC, s.sale_date DESC
");
?>

<h2 class="title">Owed Items</h2>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Item</th>
            <th>Qty Owed</th>
            <th>Receipt</th>
            <th>Sale Date</th>
            <th>Campus</th>
            <th>Status</th>
            <th>Fulfilled?</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="7">No owed items found.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo esc_html($r->item_name); ?></td>
                    <td><?php echo intval($r->qty_owed); ?></td>
                    <td><?php echo esc_html($r->receipt_no); ?></td>
                    <td><?php echo esc_html($r->sale_date); ?></td>
                    <td><?php echo esc_html($r->campus_name); ?></td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                            <span style="color:red;font-weight:bold;">Pending</span>
                        <?php else: ?>
                            <span style="color:green;font-weight:bold;">Fulfilled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                            <input type="checkbox" class="pca-fulfill-owed" data-id="<?php echo $r->id; ?>">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
