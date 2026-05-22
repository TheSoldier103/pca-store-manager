<h2 class="title">Record Book Sale</h2>

<table class="form-table">

    <tr>
        <th><label>Select Book</label></th>
        <td>
            <select id="pca-sale-item" class="regular-text">
                <option value="">Select Book</option>
                <?php
                global $wpdb;
                $items = $wpdb->prefix . 'pca_store_items';

                $books = $wpdb->get_results("
                    SELECT id, name, selling_price, current_stock
                    FROM $items
                    WHERE department='books' AND item_type='single' AND status='active'
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
        <td><input type="number" id="pca-sale-qty" value="1"></td>
    </tr>

    <tr>
        <th><label>Price</label></th>
        <td><input type="number" id="pca-sale-price" readonly></td>
    </tr>

    <tr>
        <th><label>Discount (optional)</label></th>
        <td><input type="number" id="pca-sale-discount" value="0"></td>
    </tr>

    <tr>
        <th><label>Receipt Number</label></th>
        <td><input type="text" id="pca-sale-receipt" class="regular-text"></td>
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

<input type="hidden" id="pca-sale-department" value="books">

<p>
    <button class="button button-primary" id="pca-record-sale-btn">Record Sale</button>
</p>
