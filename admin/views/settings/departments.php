<h2>Manage Departments</h2>

<table class="form-table">
    <tr>
        <th><label>Department Name</label></th>
        <td><input type="text" id="pca-dept-name"></td>
    </tr>

    <tr>
        <th><label>Code (optional)</label></th>
        <td><input type="text" id="pca-dept-code"></td>
    </tr>
</table>

<p>
    <button class="button button-primary" id="pca-save-department">Save Department</button>
</p>

<hr>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Code</th>
            <th width="80">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_departments';
        $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");

        if ($rows) {
            foreach ($rows as $d) {
                echo "<tr>
                        <td>{$d->name}</td>
                        <td>{$d->code}</td>
                        <td>
                            <a href='#' class='pca-delete-department' data-id='{$d->id}'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="3">No departments yet.</td></tr>';
        }
        ?>
    </tbody>
</table>
