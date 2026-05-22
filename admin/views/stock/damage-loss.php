<h2 class="title">Record Damage / Loss</h2>

<table class="form-table">

    <tr>
        <th><label>Select Item</label></th>
        <td>
            <select id="pca-damage-item">
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
        <td><input type="number" id="pca-damage-qty"></td>
    </tr>

    <tr>
        <th><label>Reason</label></th>
        <td>
            <select id="pca-damage-reason">
                <option value="damaged">Damaged</option>
                <option value="lost">Lost</option>
                <option value="expired">Expired</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label>Notes</label></th>
        <td><textarea id="pca-damage-notes" class="large-text"></textarea></td>
    </tr>

</table>

<p>
    <button class="button button-primary" id="pca-save-damage">Record Damage</button>
</p>
