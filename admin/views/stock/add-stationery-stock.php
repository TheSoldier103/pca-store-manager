<h2 class="title">Add Stationery Stock</h2>

<div class="pca-stock-form">

    <table class="form-table">

        <?php
        $fixed_campus = PCA_Store_Helpers::get_user_campus();
        global $wpdb;
        $campus_table = $wpdb->prefix . 'pca_store_campuses';
        ?>

        <?php if ($fixed_campus): ?>

            <input type="hidden" id="pca-stock-campus" value="<?php echo $fixed_campus; ?>">

            <tr>
                <th><label>Campus</label></th>
                <td>
                    <strong>
                        <?php
                        $name = $wpdb->get_var($wpdb->prepare(
                            "SELECT name FROM $campus_table WHERE id = %d",
                            $fixed_campus
                        ));
                        echo esc_html($name);
                        ?>
                    </strong>
                    <em>(auto‑assigned)</em>
                </td>
            </tr>

        <?php else: ?>

            <tr>
                <th><label>Campus</label></th>
                <td>
                    <select id="pca-stock-campus" class="regular-text">
                        <option value="">Select Campus</option>
                        <?php
                        $rows = $wpdb->get_results("SELECT id, name FROM $campus_table WHERE is_active = 1 ORDER BY name ASC");
                        foreach ($rows as $c) {
                            echo "<option value='{$c->id}'>{$c->name}</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>

        <?php endif; ?>

        <tr>
            <th><label>Select Item</label></th>
            <td>
                <select id="pca-stock-item" class="regular-text">
                    <option value="">Select Item</option>
                    <?php
                    $items_table = $wpdb->prefix . 'pca_store_items';
                    $stationery = $wpdb->get_results("
                        SELECT id, name FROM $items_table
                        WHERE department_id = (SELECT id FROM {$wpdb->prefix}pca_store_departments WHERE LOWER(name)='stationery')
                        AND status='active'
                        ORDER BY name ASC
                    ");

                    foreach ($stationery as $i) {
                        echo "<option value='{$i->id}'>{$i->name}</option>";
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
