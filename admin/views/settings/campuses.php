<h2>Manage Campuses</h2>

<table class="form-table">
    <tr>
        <th><label>Campus Name</label></th>
        <td><input type="text" id="pca-campus-name"></td>
    </tr>
    <tr>
        <th><label>School</label></th>
        <td>
            <select id="pca-campus-school">
                <option value="">Select School</option>
                <?php
                global $wpdb;
                $schools_table = $wpdb->prefix . 'pca_store_schools';
                $schools = $wpdb->get_results("SELECT id, name FROM $schools_table ORDER BY name ASC");
                foreach ($schools as $s) {
                    echo "<option value='{$s->id}'>{$s->name}</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><label>Status</label></th>
        <td>
            <select id="pca-campus-status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </td>
    </tr>
</table>

<p>
    <button class="button button-primary" id="pca-save-campus">Save Campus</button>
</p>

<hr>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Campus</th>
            <th>School</th>
            <th>Status</th>
            <th width="80">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $campuses_table = $wpdb->prefix . 'pca_store_campuses';
        $rows = $wpdb->get_results("
            SELECT c.*, s.name AS school_name
            FROM $campuses_table c
            LEFT JOIN $schools_table s ON s.id = c.school_id
            ORDER BY c.name ASC
        ");

        if ($rows) {
            foreach ($rows as $c) {
                echo "<tr>
                        <td>{$c->name}</td>
                        <td>{$c->school_name}</td>
                        echo "<td>" . ($c->is_active ? 'Active' : 'Inactive') . "</td>";
                        <td>
                            <a href='#' class='pca-delete-campus' data-id='{$c->id}'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="4">No campuses yet.</td></tr>';
        }
        ?>
    </tbody>
</table>
