<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$dept_table  = $wpdb->prefix . 'pca_store_departments';
$items_table = $wpdb->prefix . 'pca_store_items';

// Books department
$book_dept_id = $wpdb->get_var("
    SELECT id FROM $dept_table 
    WHERE LOWER(name) = 'books'
    LIMIT 1
");

// Distinct class levels
$class_levels = $wpdb->get_col($wpdb->prepare("
    SELECT DISTINCT class_level
    FROM $items_table
    WHERE department_id = %d
    AND item_type = 'single'
    AND class_level IS NOT NULL
    AND class_level != ''
    ORDER BY class_level ASC
", $book_dept_id));

// Distinct subjects
$subjects = $wpdb->get_col($wpdb->prepare("
    SELECT DISTINCT subject
    FROM $items_table
    WHERE department_id = %d
    AND item_type = 'single'
    AND subject IS NOT NULL
    AND subject != ''
    ORDER BY subject ASC
", $book_dept_id));
?>

<div id="pca-add-pack-modal" style="display:none;">

    <h2 id="pca-pack-modal-title">Add Pack</h2>

    <table class="form-table">

        <tr>
            <th><label>Pack Name</label></th>
            <td><input type="text" id="pca-pack-name" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Class Filter</label></th>
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
            <th><label>Subject Filter</label></th>
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
            <th><label>Pack Class Level</label></th>
            <td><input type="text" id="pca-pack-class" class="regular-text"></td>
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

    <h3>Available Books</h3>
    <div id="pca-pack-book-list"></div>

    <h3>Selected Books</h3>
    <table class="widefat" id="pca-selected-books-table">
        <thead>
            <tr>
                <th>Book Name</th>
                <th width="80">Qty</th>
                <th width="60">Remove</th>
            </tr>
        </thead>
        <tbody id="pca-selected-books-body">
            <!-- Filled dynamically -->
        </tbody>
    </table>



    <input type="hidden" id="pca-pack-id" value="">
    <input type="hidden" id="pca-pack-department-id" value="<?php echo $book_dept_id; ?>">
    <input type="hidden" id="pca-pack-type" value="pack">

    <p>
        <button class="button button-primary" id="pca-save-pack">Save Pack</button>
        <button class="button" id="pca-close-pack-modal">Cancel</button>
    </p>

</div>
