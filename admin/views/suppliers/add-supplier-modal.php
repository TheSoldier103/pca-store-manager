<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$dept_table   = $wpdb->prefix . 'pca_store_departments';
$departments  = $wpdb->get_results("SELECT id, name FROM $dept_table WHERE is_active = 1 ORDER BY name ASC");
?>

<div id="pca-add-supplier-modal" style="display:none;">

    <h2 id="pca-supplier-modal-title">Add Supplier</h2>

    <table class="form-table">

        <tr>
            <th><label for="pca-supplier-name">Supplier Name</label></th>
            <td><input type="text" id="pca-supplier-name" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="pca-supplier-department">Department</label></th>
            <td>
                <select id="pca-supplier-department">
                    <option value="">Select Department</option>
                    <?php
                    if ($departments) {
                        foreach ($departments as $d) {
                            echo "<option value='" . esc_attr($d->id) . "'>" . esc_html($d->name) . "</option>";
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="pca-supplier-contact">Contact Person</label></th>
            <td><input type="text" id="pca-supplier-contact" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="pca-supplier-phone">Phone</label></th>
            <td><input type="text" id="pca-supplier-phone" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="pca-supplier-email">Email</label></th>
            <td><input type="email" id="pca-supplier-email" class="regular-text"></td>
        </tr>

        <tr>
            <th><label for="pca-supplier-address">Address</label></th>
            <td><textarea id="pca-supplier-address" class="large-text"></textarea></td>
        </tr>

        <tr>
            <th><label for="pca-supplier-notes">Notes</label></th>
            <td><textarea id="pca-supplier-notes" class="large-text"></textarea></td>
        </tr>

        <tr>
            <th><label for="pca-supplier-status">Status</label></th>
            <td>
                <select id="pca-supplier-status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </td>
        </tr>

    </table>

    <input type="hidden" id="pca-supplier-id" value="">

    <p>
        <button class="button button-primary" id="pca-save-supplier">Save Supplier</button>
        <button class="button" id="pca-close-supplier-modal">Cancel</button>
    </p>

</div>
