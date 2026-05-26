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
            <th><label>Class</label></th>
            <td>
                <select id="pca-stock-class" class="regular-text">
                    <option value="">All Classes</option>
                    <?php
                    $items_table = $wpdb->prefix . 'pca_store_items';
                    $classes = $wpdb->get_col("SELECT DISTINCT class_level FROM $items_table WHERE class_level IS NOT NULL AND class_level != '' ORDER BY class_level ASC");

                    foreach ($classes as $c) {
                        echo "<option value='{$c}'>{$c}</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Subject</label></th>
            <td>
                <select id="pca-stock-subject" class="regular-text">
                    <option value="">All Subjects</option>
                    <?php
                    $subjects = $wpdb->get_col("SELECT DISTINCT subject FROM $items_table WHERE subject IS NOT NULL AND subject != '' ORDER BY subject ASC");

                    foreach ($subjects as $s) {
                        echo "<option value='{$s}'>{$s}</option>";
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
