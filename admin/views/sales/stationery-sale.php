<h2 class="title">Record Stationery Sale</h2>

<table class="form-table">

    <tr>
        <th><label>Select Item</label></th>
        <td>
            <select id="pca-sale-item">
                <option value="">Select Item</option>
                <?php
                global $wpdb;
                $items = $wpdb->prefix . 'pca_store_items';

                $stationery = $wpdb->get_results("
                    SELECT id, name, selling_price, current_stock
                    FROM $items
                    WHERE department='stationery' AND status='active'
                    ORDER BY name ASC
                ");

                foreach ($stationery as $s) {
                    echo "<option value='{$s->id}' data-price='{$s->selling_price}' data-stock='{$s->current_stock}'>
                            {$s->name} (Stock: {$s->current_stock})
                          </option>";
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <th><label>Quantity</label></th>
        <td><input type="number" id="pca-sale-qty" value="1"></td>
    </tr>

    <tr>
        <th><label>Price</label></th>
        <td><input type="number" id="pca-sale-price" readonly></td>
    </tr>

    <tr>
        <th><label>Receipt Number</label></th>
        <td><input type="text" id="pca-sale-receipt"></td>
    </tr>

    <tr>
        <th><label>Payment Method</label></th>
        <td>
            <select id="pca-sale-method">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="pos">POS</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label>Notes</label></th>
        <td><textarea id="pca-sale-notes" class="large-text"></textarea></td>
    </tr>

</table>

<input type="hidden" id="pca-sale-department" value="stationery">

<p>
    <button class="button button-primary" id="pca-record-sale-btn">Record Sale</button>
</p>
