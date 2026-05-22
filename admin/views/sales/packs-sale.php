<h2 class="title">Book Packs Sale</h2>

<div class="pca-sale-form">

    <table class="form-table">

        <tr>
            <th><label>Select Pack</label></th>
            <td>
                <select id="pca-sale-pack">
                    <option value="">Select Pack</option>
                    <?php
                    global $wpdb;
                    $items = $wpdb->prefix . 'pca_store_items';

                    $packs = $wpdb->get_results("
                        SELECT id, name, selling_price
                        FROM $items
                        WHERE department='books' AND item_type='pack'
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
            <td><input type="number" id="pca-sale-pack-qty" value="1"></td>
        </tr>

    </table>

    <div id="pca-pack-contents" style="display:none;">
        <h3>Pack Contents</h3>
        <p>You may remove items (e.g., if a sibling already has the book).</p>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody id="pca-pack-items"></tbody>
        </table>
    </div>

    <p>
        <button class="button button-primary" id="pca-add-pack-to-cart">Add Pack to Cart</button>
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
