<h1>PCA Store Dashboard</h1>

<style>
    .pca-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .pca-kpi-card {
        background: #fff;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        text-align: center;
    }
    .pca-kpi-card h3 {
        margin: 0;
        font-size: 14px;
        color: #666;
    }
    .pca-kpi-card .value {
        font-size: 28px;
        font-weight: bold;
        margin-top: 10px;
    }
    .pca-section {
        margin-top: 40px;
    }
</style>

<!-- KPI CARDS -->
<div class="pca-kpi-grid">

    <div class="pca-kpi-card">
        <h3>Today's Sales</h3>
        <div class="value">₦0.00</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Items Sold Today</h3>
        <div class="value">0</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Low Stock Items</h3>
        <div class="value">0</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Total Stock Value</h3>
        <div class="value">₦0.00</div>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="pca-section">
    <h2>Quick Actions</h2>

    <p>
        <a href="?page=pca-store-sales" class="button button-primary">Record Sale</a>
        <a href="?page=pca-store-stock" class="button">Add Stock</a>
        <a href="?page=pca-store-items" class="button">Manage Items</a>
        <a href="?page=pca-store-suppliers" class="button">Suppliers</a>
    </p>
</div>

<!-- RECENT SALES -->
<div class="pca-section">
    <h2>Recent Sales</h2>

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
            <?php
            global $wpdb;
            $sales = $wpdb->prefix . 'pca_store_sales';

            $rows = $wpdb->get_results("
                SELECT * FROM $sales
                ORDER BY sale_date DESC
                LIMIT 10
            ");

            if ($rows) {
                foreach ($rows as $s) {
                    echo "<tr>
                            <td>{$s->sale_date}</td>
                            <td>{$s->receipt_no}</td>
                            <td>{$s->department}</td>
                            <td>₦" . number_format($s->total_amount, 2) . "</td>
                            <td>{$s->sold_by}</td>
                          </tr>";
                }
            } else {
                echo '<tr><td colspan="5">No recent sales found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- LOW STOCK SNAPSHOT -->
<div class="pca-section">
    <h2>Low Stock Snapshot</h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Department</th>
                <th>Stock</th>
                <th>Reorder Level</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $items = $wpdb->prefix . 'pca_store_items';

            $low = $wpdb->get_results("
                SELECT name, department, current_stock, reorder_level
                FROM $items
                WHERE current_stock <= reorder_level
                ORDER BY current_stock ASC
                LIMIT 10
            ");

            if ($low) {
                foreach ($low as $i) {
                    echo "<tr>
                            <td>{$i->name}</td>
                            <td>{$i->department}</td>
                            <td>{$i->current_stock}</td>
                            <td>{$i->reorder_level}</td>
                          </tr>";
                }
            } else {
                echo '<tr><td colspan="4">No low stock items.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
