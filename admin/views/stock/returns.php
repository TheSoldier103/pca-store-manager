<h2 class="title">Record Return</h2>

<table class="form-table">

    <tr>
        <th><label>Select Item</label></th>
        <td>
            <select id="pca-return-item">
                <option value="">Select Item</option>
                <?php
                global $wpdb;
                $items = $wpdb->prefix . 'pca_store_items';
                $rows = $wpdb->get_results("SELECT id, name FROM $items ORDER BY name ASC");

                foreach ($rows as $item) {
                    echo "<option value='{$item->id}'>{$item->name}</option>";
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <th><label>Quantity</label></th>
        <td><input type="number" id="pca-return-qty"></td>
    </tr>

    <tr>
        <th><label>Reason</label></th>
        <td>
            <select id="pca-return-reason">
                <option value="customer_return">Customer Return</option>
                <option value="supplier_return">Return to Supplier</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label>Notes</label></th>
        <td><textarea id="pca-return-notes" class="large-text"></textarea></td>
    </tr>

</table>

<p>
    <button class="button button-primary" id="pca-save-return">Record Return</button>
</p>
