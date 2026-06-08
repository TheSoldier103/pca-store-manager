<h2 class="title">Record Book Sale</h2>

<table class="form-table">

    <?php $fixed_campus = PCA_Store_Helpers::get_user_campus(); ?>

    <?php if ($fixed_campus): ?>

        <input type="hidden" id="pca-sale-campus" value="<?php echo $fixed_campus; ?>">

        <tr>
            <th><label>Campus</label></th>
            <td>
                <strong>
                    <?php
                    global $wpdb;
                    $campus_table = $wpdb->prefix . 'pca_store_campuses';
                    $name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $campus_table WHERE id = %d", $fixed_campus));
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
                <select id="pca-sale-campus" class="regular-text">
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

    <?php endif; ?>

    <tr>
        <th><label>Class</label></th>
        <td>
            <select id="pca-sale-class" class="regular-text">
                <option value="">All Classes</option>
                <?php
                global $wpdb;
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
            <select id="pca-sale-subject" class="regular-text">
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
        <th><label>Select Book</label></th>
        <td>
            <select id="pca-sale-item" class="regular-text">
                <option value="">Select Book</option>
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
        <th><label>Total</label></th>
        <td><input type="number" id="pca-sale-total" readonly></td>
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
<input type="hidden" id="pca-sale-type" value="single">


<p>
    <button class="button button-primary" id="pca-record-sale-btn">Record Sale</button>
</p>
