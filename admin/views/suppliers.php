<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$dept_table      = $wpdb->prefix . 'pca_store_departments';
$suppliers_table = $wpdb->prefix . 'pca_store_suppliers';

// Fetch departments
$departments = $wpdb->get_results("SELECT id, name, code FROM $dept_table WHERE is_active = 1 ORDER BY name ASC");

// Build tabs: All + one per department
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'all';

$tabs = [
    'all' => 'All Suppliers',
];

foreach ($departments as $d) {
    $tabs['dept_' . $d->id] = $d->name . ' Suppliers';
}

PCA_Store_Admin_Tabs::render_tabs($tabs, $active_tab);

// Determine selected department (if any)
$selected_department_id = null;
if (strpos($active_tab, 'dept_') === 0) {
    $selected_department_id = intval(substr($active_tab, 5));
}

// Build suppliers query
if ($selected_department_id) {
    $suppliers = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s.*, d.name AS department_name
             FROM $suppliers_table s
             LEFT JOIN $dept_table d ON d.id = s.department_id
             WHERE s.department_id = %d
             ORDER BY s.name ASC",
            $selected_department_id
        )
    );
} else {
    $suppliers = $wpdb->get_results(
        "SELECT s.*, d.name AS department_name
         FROM $suppliers_table s
         LEFT JOIN $dept_table d ON d.id = s.department_id
         ORDER BY s.name ASC"
    );
}
?>

<h2 class="title">
    <?php
    if ($selected_department_id) {
        $current_dept = array_filter($departments, fn($d) => $d->id == $selected_department_id);
        $current_dept = $current_dept ? reset($current_dept) : null;
        echo $current_dept ? esc_html($current_dept->name) . ' Suppliers' : 'Suppliers';
    } else {
        echo 'All Suppliers';
    }
    ?>
</h2>

<?php if ($active_tab === 'all'): ?>
    <p>
        <button class="button button-primary" id="pca-add-supplier-btn">Add Supplier</button>
    </p>
<?php endif; ?>


<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Contact Person</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Status</th>
            <th width="140">Actions</th>
        </tr>
    </thead>
    <tbody id="pca-suppliers-table-body">
        <?php
        if ($suppliers) {
            foreach ($suppliers as $s) {
                echo '<tr data-id="' . esc_attr($s->id) . '">';
                echo '<td>' . esc_html($s->name) . '</td>';
                echo '<td>' . esc_html($s->department_name ?: '-') . '</td>';
                echo '<td>' . esc_html($s->contact_person) . '</td>';
                echo '<td>' . esc_html($s->phone) . '</td>';
                echo '<td>' . esc_html($s->email) . '</td>';
                echo '<td>' . ($s->is_active ? 'Active' : 'Inactive') . '</td>';
                echo '<td>
                        <a href="#" class="button pca-edit-supplier" data-id="' . esc_attr($s->id) . '">Edit</a>
                        <a href="#" class="button button-danger pca-delete-supplier" data-id="' . esc_attr($s->id) . '">Delete</a>
                      </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">No suppliers found.</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php include __DIR__ . '/suppliers/add-supplier-modal.php'; ?>
