<?php
global $wpdb;

/* ============================================================
   CAMPUS FILTER
============================================================ */

$fixed_campus = PCA_Store_Helpers::get_user_campus();
$selected_campus = $fixed_campus ?: intval($_GET['campus'] ?? 0);

$campus_filter = $selected_campus ? "AND campus_id = $selected_campus" : "";

/* ============================================================
   KPI QUERIES — BOOKS
============================================================ */

// 1. Today's Book Sales (₦)
$kpi_today_sales = $wpdb->get_var("
    SELECT SUM(total_amount)
    FROM {$wpdb->prefix}pca_store_sales
    WHERE department = 'books'
    AND DATE(sale_date) = CURDATE()
    $campus_filter
") ?: 0;

// 2. Books Sold Today (qty)
$kpi_books_sold = $wpdb->get_var("
    SELECT SUM(si.quantity)
    FROM {$wpdb->prefix}pca_store_sale_items si
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = si.item_id
    INNER JOIN {$wpdb->prefix}pca_store_sales s ON s.id = si.sale_id
    WHERE i.department = 'books'
    AND DATE(s.sale_date) = CURDATE()
    $campus_filter
") ?: 0;


// 3. Packs Sold Today
$kpi_packs_sold = $wpdb->get_var("
    SELECT SUM(pack_qty)
    FROM {$wpdb->prefix}pca_store_sales
    WHERE sale_type = 'pack'
    AND DATE(sale_date) = CURDATE()
    $campus_filter
") ?: 0;

// 4. Low Stock (Books)
$kpi_low_stock = $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$wpdb->prefix}pca_store_item_stock st
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = st.item_id
    WHERE i.department = 'books'
    AND st.stock <= i.reorder_level
    $campus_filter
") ?: 0;

// 5. Total Book Stock Value
$kpi_stock_value = $wpdb->get_var("
    SELECT SUM(st.stock * i.selling_price)
    FROM {$wpdb->prefix}pca_store_item_stock st
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = st.item_id
    WHERE i.department = 'books'
    $campus_filter
") ?: 0;

// 6. Total Owed (Books)
$kpi_owed_books = $wpdb->get_var("
    SELECT SUM(oi.qty_owed)
    FROM {$wpdb->prefix}pca_store_owed_items oi
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = oi.item_id
    WHERE i.department = 'books'
    AND oi.status = 'pending'
    $campus_filter
") ?: 0;

/* ============================================================
   KPI QUERIES — STATIONERY
============================================================ */

// Stationery Sold Today
$kpi_stationery_sold = $wpdb->get_var("
    SELECT SUM(si.quantity)
    FROM {$wpdb->prefix}pca_store_sale_items si
    INNER JOIN {$wpdb->prefix}pca_store_sales s ON s.id = si.sale_id
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = si.item_id
    WHERE i.department = 'stationery'
    AND DATE(s.sale_date) = CURDATE()
    $campus_filter
") ?: 0;

// Low Stock (Stationery)
$kpi_stationery_low = $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$wpdb->prefix}pca_store_item_stock st
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = st.item_id
    WHERE i.department = 'stationery'
    AND st.stock <= i.reorder_level
    $campus_filter
") ?: 0;

/* ============================================================
   RECENT SALES (Books + Stationery)
============================================================ */

$recent_sales = $wpdb->get_results("
    SELECT s.*, 
        (SELECT GROUP_CONCAT(item_name SEPARATOR ', ')
         FROM {$wpdb->prefix}pca_store_sale_items 
         WHERE sale_id = s.id) AS items
    FROM {$wpdb->prefix}pca_store_sales s
    WHERE s.department IN ('books', 'stationery')
    $campus_filter
    ORDER BY s.sale_date DESC
    LIMIT 10
");

/* ============================================================
   LOW STOCK SNAPSHOT (Books Only)
============================================================ */

$low_stock_books = $wpdb->get_results("
    SELECT i.name, st.stock, i.reorder_level
    FROM {$wpdb->prefix}pca_store_item_stock st
    INNER JOIN {$wpdb->prefix}pca_store_items i ON i.id = st.item_id
    WHERE i.department = 'books'
    AND st.stock <= i.reorder_level
    $campus_filter
    ORDER BY st.stock ASC
");

?>

<h1>PCA Store Dashboard</h1>

<!-- CAMPUS FILTER -->
<div style="margin: 20px 0;">
    <label><strong>Campus:</strong></label>

    <?php if ($fixed_campus): ?>
        <input type="hidden" id="pca-dashboard-campus" value="<?php echo $fixed_campus; ?>">
        <strong><?php echo esc_html($wpdb->get_var("SELECT name FROM {$wpdb->prefix}pca_store_campuses WHERE id = $fixed_campus")); ?></strong>
    <?php else: ?>
        <form method="GET">
            <input type="hidden" name="page" value="pca-store-dashboard">
            <select name="campus" onchange="this.form.submit()">
                <option value="">All Campuses</option>
                <?php
                $campuses = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}pca_store_campuses WHERE is_active = 1 ORDER BY name ASC");
                foreach ($campuses as $c) {
                    $sel = $selected_campus == $c->id ? "selected" : "";
                    echo "<option value='{$c->id}' $sel>{$c->name}</option>";
                }
                ?>
            </select>
        </form>
    <?php endif; ?>
</div>

<!-- BOOKS KPI CARDS -->
<h2>📘 Books Overview</h2>
<div class="pca-kpi-grid">

    <div class="pca-kpi-card">
        <h3>Today's Book Sales</h3>
        <div class="value">₦<?php echo number_format($kpi_today_sales, 2); ?></div>
    </div>

    <div class="pca-kpi-card">
        <h3>Books Sold Today</h3>
        <div class="value"><?php echo $kpi_books_sold; ?></div>
    </div>

    <div class="pca-kpi-card">
        <h3>Packs Sold Today</h3>
        <div class="value"><?php echo $kpi_packs_sold; ?></div>
    </div>

    <div class="pca-kpi-card">
        <h3>Low Stock (Books)</h3>
        <div class="value"><?php echo $kpi_low_stock; ?></div>
    </div>

    <div class="pca-kpi-card">
        <h3>Total Book Stock Value</h3>
        <div class="value">₦<?php echo number_format($kpi_stock_value, 2); ?></div>
    </div>

    <div class="pca-kpi-card">
        <h3>Total Owed (Books)</h3>
        <div class="value"><?php echo $kpi_owed_books; ?></div>
    </div>

</div>

<!-- STATIONERY KPI CARDS -->
<h2>✏️ Stationery Overview</h2>
<div class="pca-kpi-grid">

    <div class="pca-kpi-card">
        <h3>Stationery Sold Today</h3>
        <div class="value"><?php echo $kpi_stationery_sold; ?></div>
    </div>

    <div class="pca-kpi-card">
        <h3>Low Stock (Stationery)</h3>
        <div class="value"><?php echo $kpi_stationery_low; ?></div>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="pca-section">
    <h2>Quick Actions</h2>

    <p>
        <strong>Books:</strong>
        <a href="?page=pca-store-sales&tab=books" class="button button-primary">Record Book Sale</a>
        <a href="?page=pca-store-stock&tab=add" class="button">Add Book Stock</a>
        <a href="?page=pca-store-items&tab=books" class="button">Manage Books</a>
    </p>

    <p>
        <strong>Stationery:</strong>
        <a href="?page=pca-store-sales&tab=stationery" class="button button-primary">Record Stationery Sale</a>
        <a href="?page=pca-store-stock&tab=add" class="button">Add Stationery Stock</a>
        <a href="?page=pca-store-items&tab=stationery" class="button">Manage Stationery</a>
    </p>
</div>

<!-- RECENT SALES -->
<div class="pca-section">
    <h2>Recent Sales (Books + Stationery)</h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Items</th>
                <th>Total</th>
                <th>Cashier</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$recent_sales): ?>
                <tr><td colspan="5">No recent sales.</td></tr>
            <?php else: ?>
                <?php foreach ($recent_sales as $s): ?>
                    <tr>
                        <td><?php echo esc_html($s->sale_date); ?></td>
                        <td><?php echo esc_html($s->receipt_no); ?></td>
                        <td><?php echo esc_html($s->items); ?></td>
                        <td>₦<?php echo number_format($s->total_amount, 2); ?></td>
                        <td><?php echo esc_html(get_userdata($s->sold_by)->display_name); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- LOW STOCK SNAPSHOT -->
<div class="pca-section">
    <h2>Low Stock Snapshot (Books)</h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Stock</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$low_stock_books): ?>
                <tr><td colspan="3">No low stock books.</td></tr>
            <?php else: ?>
                <?php foreach ($low_stock_books as $b): ?>
                    <tr>
                        <td><?php echo esc_html($b->name); ?></td>
                        <td><?php echo intval($b->stock); ?></td>
                        <td><?php echo intval($b->reorder_level); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>
