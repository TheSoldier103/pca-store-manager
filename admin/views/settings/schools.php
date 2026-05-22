<h2>Manage Schools</h2>

<table class="form-table">
    <tr>
        <th><label>School Name</label></th>
        <td><input type="text" id="pca-school-name"></td>
    </tr>
</table>

<p>
    <button class="button button-primary" id="pca-save-school">Save School</button>
</p>

<hr>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>School</th>
            <th width="80">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'pca_store_schools';
        $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");

        if ($rows) {
            foreach ($rows as $s) {
                echo "<tr>
                        <td>{$s->name}</td>
                        <td>
                            <a href='#' class='pca-delete-school' data-id='{$s->id}'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="2">No schools yet.</td></tr>';
        }
        ?>
    </tbody>
</table>
