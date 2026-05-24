<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$dept_table = $wpdb->prefix . 'pca_store_departments';
$items_table = $wpdb->prefix . 'pca_store_items';

// Only allow packs under Books for now
$book_dept_id = $wpdb->get_var("SELECT id FROM $dept_table WHERE name = 'Books' LIMIT 1");

$child_items = $wpdb->get_results("
    SELECT id, name, selling_price
    FROM $items_table
    WHERE department_id = $book_dept_id
    AND item_type = 'single'
    ORDER BY name ASC
");
?>

<div id="pca-add-pack-modal" style="display:none;">

    <h2 id="pca-pack-modal-title">Add Pack</h2>

    <table class="form-table">

        <tr>
            <th><label>Pack Name</label></th>
            <td><input type="text" id="pca-pack-name" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Class Level</label></th>
            <td>
                <select id="pca-pack-class-filter">
                    <option value="">All Classes</option>
                    <?php foreach ($class_levels as $cl): ?>
                        <option value="<?php echo esc_attr($cl); ?>">
                            <?php echo esc_html($cl); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Subject</label></th>
            <td>
                <select id="pca-pack-subject-filter">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?php echo esc_attr($sub); ?>">
                            <?php echo esc_html($sub); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>



        <tr>
            <th><label>Set Class Level</label></th>
            <td><input type="text" id="pca-pack-class" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Pack Items</label></th>
            <td>
                <div id="pca-pack-items-container">
                    <?php foreach ($child_items as $item): ?>
                        <div class="pca-pack-row" data-id="<?php echo $item->id; ?>">
                            <strong><?php echo esc_html($item->name); ?></strong>
                            (₦<?php echo number_format($item->selling_price, 2); ?>)
                            Qty:
                            <input type="number" class="pca-pack-qty" value="1" min="1">
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>

        <tr>
            <th><label>Selling Price</label></th>
            <td><input type="number" id="pca-pack-price" class="regular-text" step="0.01"></td>
        </tr>

        <tr>
            <th><label>Reorder Level</label></th>
            <td><input type="number" id="pca-pack-reorder" class="regular-text"></td>
        </tr>

    </table>

    <h3>Books in this Pack</h3>
    <div id="pca-pack-book-list"></div>
    
    <input type="hidden" id="pca-pack-id" value="">
    <input type="hidden" id="pca-pack-department-id" value="<?php echo $book_dept_id; ?>">
    <input type="hidden" id="pca-pack-type" value="pack">

    <p>
        <button class="button button-primary" id="pca-save-pack">Save Pack</button>
        <button class="button" id="pca-close-pack-modal">Cancel</button>
    </p>

</div>
