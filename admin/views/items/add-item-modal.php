<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$dept_table      = $wpdb->prefix . 'pca_store_departments';
$suppliers_table = $wpdb->prefix . 'pca_store_suppliers';

$departments = $wpdb->get_results("SELECT id, name FROM $dept_table WHERE is_active = 1 ORDER BY name ASC");
$suppliers   = $wpdb->get_results("SELECT id, name FROM $suppliers_table WHERE is_active = 1 ORDER BY name ASC");
?>

<div id="pca-add-item-modal" style="display:none;">

    <h2 id="pca-item-modal-title">Add Item</h2>

    <table class="form-table">

        <tr>
            <th><label>Item Name</label></th>
            <td><input type="text" id="pca-item-name" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Department</label></th>
            <td>
                <select id="pca-item-department">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?php echo esc_attr($d->id); ?>">
                            <?php echo esc_html($d->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Supplier</label></th>
            <td>
                <select id="pca-item-supplier">
                    <option value="">Select Supplier</option>
                </select>

            </td>
        </tr>

        <tr>
            <th><label>Selling Price</label></th>
            <td><input type="number" id="pca-item-price" class="regular-text" step="0.01"></td>
        </tr>

        <tr>
            <th><label>Reorder Level</label></th>
            <td><input type="number" id="pca-item-reorder" class="regular-text"></td>
        </tr>

        <!-- BOOK FIELDS -->
        <tr class="pca-book-field" style="display:none;">
            <th><label>Class Level</label></th>
            <td><input type="text" id="pca-item-class" class="regular-text"></td>
        </tr>

        <tr class="pca-book-field" style="display:none;">
            <th><label>Subject</label></th>
            <td><input type="text" id="pca-item-subject" class="regular-text"></td>
        </tr>

        <!-- UNIFORM FIELDS -->
        <tr class="pca-uniform-field" style="display:none;">
            <th><label>Size (optional)</label></th>
            <td><input type="text" id="pca-item-size" class="regular-text"></td>
        </tr>

        <tr class="pca-uniform-field" style="display:none;">
            <th><label>Gender (optional)</label></th>
            <td>
                <select id="pca-item-gender">
                    <option value="">None</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="unisex">Unisex</option>
                </select>
            </td>
        </tr>

        <tr class="pca-uniform-field" style="display:none;">
            <th><label>Color (optional)</label></th>
            <td><input type="text" id="pca-item-color" class="regular-text"></td>
        </tr>

    </table>

    <input type="hidden" id="pca-item-id" value="">
    <input type="hidden" id="pca-item-type" value="single">

    <p>
        <button class="button button-primary" id="pca-save-item">Save Item</button>
        <button class="button" id="pca-close-item-modal">Cancel</button>
    </p>

</div>

<div id="pca-import-books-modal" style="display:none;">

    <h2>Import Books from CSV</h2>

    <p>Select a CSV file with the following columns:</p>

    <pre>name,selling_price,class_level,subject,reorder_level,stock_ughelli,stock_okuokoko</pre>

    <input type="file" id="pca-books-csv-file" accept=".csv">

    <p>
        <button class="button button-primary" id="pca-upload-books-csv">Upload</button>
        <button class="button" id="pca-close-import-books">Cancel</button>
    </p>

    <div id="pca-import-books-result"></div>

</div>

<div id="pca-import-stationery-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:24px; z-index:9999; border:1px solid #ccd0d4; box-shadow:0 4px 20px rgba(0,0,0,.15); min-width:340px;">
    <h3>Import Stationery CSV</h3>
    <p style="color:#666; font-size:13px;">
        Required columns: <code>name</code>, <code>selling_price</code>, <code>reorder_level</code>,
        <code>stock_ughelli</code>, <code>stock_okuokoko</code>
    </p>
    <input type="file" id="pca-stationery-csv-file" accept=".csv"><br><br>
    <button class="button button-primary" id="pca-upload-stationery-csv">Import</button>
    <button class="button" id="pca-close-import-stationery">Cancel</button>
    <div id="pca-import-stationery-result" style="margin-top:12px;"></div>
</div>