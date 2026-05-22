<h2>Advanced Settings</h2>

<table class="form-table">

    <tr>
        <th><label>Debug Mode</label></th>
        <td>
            <label>
                <input type="checkbox" id="pca-advanced-debug"
                    <?php checked(get_option('pca_advanced_debug_mode'), 1); ?>>
                Enable debug mode (show SQL errors, raw logs, developer info)
            </label>
        </td>
    </tr>

</table>

<p>
    <button class="button button-primary" id="pca-save-advanced">Save Advanced Settings</button>
</p>
