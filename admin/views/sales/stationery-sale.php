<h2 class="title">Stationery Sale</h2>

<div class="pca-sale-form">

    <table class="form-table">

        <tr>
            <th><label>Select Item</label></th>
            <td>
                <select id="pca-sale-stationery">
                    <option value="">Select Item</option>
                    <?php
                    global $wpdb;
                    $items = $wpdb->prefix . 'pca_store_items';

                    $stationery = $wpdb->get_results("
                        SELECT id, name, selling_price, current_stock
                        FROM $items
                        WHERE department='stationery'
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
            <td><input type="number" id="pca-sale-stationery-qty" value="1"></td>
        </tr>

        <tr>
            <th><label>Price</label></th>
            <td><input type="number" id="pca-sale-stationery-price" readonly></td>
        </tr>

    </table>

    <p>
        <button class="button button-primary" id="pca-add-stationery-to-cart">Add to Cart</button>
    </p>

</div>

<hr>

<h3>Cart</h3>

<table class="wp-list-table widefat fixed striped" id="pca-sale-cart">
    <thead>
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Total</th>
            <th width="80">Remove</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<p>
    <button class="button button-primary" id="pca-complete-sale">Complete Sale</button>
</p>
