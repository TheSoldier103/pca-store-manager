<?php if (!get_option('pca_roles_can_edit_stock')): ?>

    <div class="notice notice-error">
        <p>You do not have permission to edit stock.</p>
    </div>

    <?php return; ?>

<?php endif; ?>


<h2 class="title">Add Stock</h2>

<div class="pca-stock-form">

    <table class="form-table">

        <tr>
            <th><label>Select Item</label></th>
            <td>
                <select id="pca-stock-item" class="regular-text">
                    <option value="">Select Item</option>
                    <?php
                    global $wpdb;
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
            <th><label>Supplier</label></th>
            <td>
                <select id="pca-stock-supplier">
                    <option value="">Select Supplier</option>
                    <?php
                    $suppliers = $wpdb->prefix . 'pca_store_suppliers';
                    $rows = $wpdb->get_results("SELECT id, name FROM $suppliers ORDER BY name ASC");

                    foreach ($rows as $s) {
                        echo "<option value='{$s->id}'>{$s->name}</option>";
                    }
                    ?>
                </select>
            </td>
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
