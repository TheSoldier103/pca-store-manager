<h1>PCA Store Dashboard</h1>

<!-- BOOKS KPI CARDS -->
<h2>📘 Books Overview</h2>
<div class="pca-kpi-grid">

    <div class="pca-kpi-card">
        <h3>Today's Book Sales</h3>
        <div class="value">₦0.00</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Books Sold Today</h3>
        <div class="value">0</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Packs Sold Today</h3>
        <div class="value">0</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Low Stock (Books)</h3>
        <div class="value">0</div>
    </div>

</div>

<!-- UNIFORMS KPI CARDS -->
<h2>👕 Uniforms Overview</h2>
<div class="pca-kpi-grid">

    <div class="pca-kpi-card">
        <h3>Today's Uniform Sales</h3>
        <div class="value">₦0.00</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Uniform Items Sold</h3>
        <div class="value">0</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Low Stock (Uniforms)</h3>
        <div class="value">0</div>
    </div>

    <div class="pca-kpi-card">
        <h3>Total Uniform Stock Value</h3>
        <div class="value">₦0.00</div>
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
</div>

<!-- RECENT SALES -->
<div class="pca-section">
    <h2>Recent Sales</h2>

    <h3>Books</h3>
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
            <tr><td colspan="5">No recent book sales.</td></tr>
        </tbody>
    </table>


<!-- LOW STOCK SNAPSHOT -->
<div class="pca-section">
    <h2>Low Stock Snapshot</h2>

    <h3>Books</h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Stock</th>
                <th>Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="3">No low stock books.</td></tr>
        </tbody>
    </table>

</div>
