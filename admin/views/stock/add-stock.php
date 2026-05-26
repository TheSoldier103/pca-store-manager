<h2 class="title">Add Stock</h2>

<div class="pca-stock-form">

    <table class="form-table">

        <tr>
            <th><label>Campus</label></th>
            <td>
                <select id="pca-stock-campus" class="regular-text">
                    <option value="">Select Campus</option>
                    <?php
                    global $wpdb;
                    $campuses = $wpdb->prefix . 'pca_store_campuses';
                    $rows = $wpdb->get_results("SELECT id, name FROM $campuses WHERE is_active = 1 ORDER BY name ASC");

                    foreach ($rows as $c) {
                        echo "<option value='{$c->id}'>{$c->name}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Select Item</label></th>
            <td>
                <select id="pca-stock-item" class="regular-text">
                    <option value="">Select Item</option>
                    <?php
                    $items = $wpdb->prefix . 'pca_store_items';
                    $results = $wpdb->get_results("SELECT id, name FROM $items ORDER BY name ASC");

                    foreach ($results as $item) {
                        echo "<option value='{$item->id}'>{$item->name}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Quantity</label></th>
            <td><input type="number" id="pca-stock-qty" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Notes</label></th>
            <td><textarea id="pca-stock-notes" class="large-text"></textarea></td>
        </tr>

    </table>

    <p>
        <button class="button button-primary" id="pca-save-stock">Add Stock</button>
    </p>

</div>
