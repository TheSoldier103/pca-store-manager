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
