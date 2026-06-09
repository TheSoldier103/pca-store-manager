<?php
global $wpdb;

// Department lookup
$departments    = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}pca_store_departments WHERE is_active = 1");
$dept_map       = array_column($departments, 'name', 'id');

$fixed_campus    = PCA_Store_Helpers::get_user_campus();
$selected_campus = intval($_GET['campus_id'] ?? 0);
$campus_id       = $fixed_campus ?: $selected_campus;

$where = "WHERE 1=1";
if ($campus_id) {
    $where .= $wpdb->prepare(" AND s.campus_id = %d", $campus_id);
}

$rows = $wpdb->get_results("
    SELECT s.*
    FROM {$wpdb->prefix}pca_store_sales s
    $where
    ORDER BY s.sale_date DESC
    LIMIT 50
");

if ($rows) {
    foreach ($rows as $s) {
        $user      = get_userdata($s->sold_by);
        $cashier   = $user ? esc_html($user->display_name) : 'Unknown';
        $dept_name = esc_html($dept_map[$s->department_id] ?? '—');

        echo "<tr>
                <td>" . esc_html($s->sale_date)                  . "</td>
                <td>" . esc_html($s->receipt_no)                 . "</td>
                <td>{$dept_name}</td>
                <td>₦" . number_format($s->total_amount, 2)      . "</td>
                <td>₦" . number_format($s->amount_paid, 2)       . "</td>
                <td>₦" . number_format($s->discount, 2)          . "</td>
                <td>₦" . number_format($s->balance, 2)           . "</td>
                <td>" . esc_html($s->payment_method)             . "</td>
                <td>{$cashier}</td>
              </tr>";
    }
} else {
    echo '<tr><td colspan="9">No recent sales found.</td></tr>';
}
?>