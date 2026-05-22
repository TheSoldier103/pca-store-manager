<h2 class="title">Books Sale</h2>

<div class="pca-sale-form">

    <table class="form-table">

        <tr>
            <th><label>Select Book</label></th>
            <td>
                <select id="pca-sale-book">
                    <option value="">Select Book</option>
                    <?php
                    global $wpdb;
                    $items = $wpdb->prefix . 'pca_store_items';
                    $books = $wpdb->get_results("
                        SELECT id, name, selling_price, current_stock
                        FROM $items
                        WHERE department='books' AND item_type='single'
                        ORDER BY name ASC
                    ");

                    foreach ($books as $b) {
                        echo "<option value='{$b->id}' data-price='{$b->selling_price}' data-stock='{$b->current_stock}'>
                                {$b->name} (Stock: {$b->current_stock})
                              </option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Quantity</label></th>
            <td><input type="number" id="pca-sale-book-qty" value="1"></td>
        </tr>

        <tr>
            <th><label>Price</label></th>
            <td><input type="number" id="pca-sale-book-price" readonly></td>
        </tr>

        <tr>
            <th><label>Discount (optional)</label></th>
            <td><input type="number" id="pca-sale-book-discount" value="0"></td>
        </tr>

    </table>

    <p>
        <button class="button button-primary" id="pca-add-book-to-cart">Add to Cart</button>
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
