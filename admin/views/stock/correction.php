<?php if (!get_option('pca_roles_can_edit_stock')): ?>

    <div class="notice notice-error">
        <p>You do not have permission to edit stock.</p>
    </div>

    <?php return; ?>

<?php endif; ?>


<h2 class="title">Stock Correction</h2>

<table class="form-table">

    <tr>
        <th><label>Select Item</label></th>
        <td>
            <select id="pca-correction-item">
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
        <th><label>Correct Stock To</label></th>
        <td><input type="number" id="pca-correction-new"></td>
    </tr>

    <tr>
        <th><label>Reason</label></th>
        <td><textarea id="pca-correction-notes" class="large-text"></textarea></td>
    </tr>

</table>

<p>
    <button class="button button-primary" id="pca-save-correction">Apply Correction</button>
</p>
