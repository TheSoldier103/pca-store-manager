<h2 class="title">Record Pack Sale</h2>

<table class="form-table">

    <tr>
        <th><label>Select Pack</label></th>
        <td>
            <select id="pca-sale-item">
                <option value="">Select Pack</option>
                <?php
                global $wpdb;
                $items = $wpdb->prefix . 'pca_store_items';

                $packs = $wpdb->get_results("
                    SELECT id, name, selling_price
                    FROM $items
                    WHERE department='books' AND item_type='pack' AND status='active'
                    ORDER BY name ASC
                ");

                foreach ($packs as $p) {
                    echo "<option value='{$p->id}' data-price='{$p->selling_price}'>
                            {$p->name}
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

<input type="hidden" id="pca-sale-department" value="books">

<p>
    <button class="button button-primary" id="pca-record-sale-btn">Record Sale</button>
</p>
