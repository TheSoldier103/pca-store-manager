<div id="pca-add-supplier-modal" style="display:none;">

    <h2>Add Supplier</h2>

    <table class="form-table">

        <tr>
            <th><label>Supplier Name</label></th>
            <td><input type="text" id="pca-supplier-name" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Department</label></th>
            <td>
                <select id="pca-supplier-department">
                    <option value="books">Books</option>
                    <option value="uniforms">Uniforms</option>
                </select>
            </td>
        </tr>

        <tr>
            <th><label>Contact Person</label></th>
            <td><input type="text" id="pca-supplier-contact" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Phone</label></th>
            <td><input type="text" id="pca-supplier-phone" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Email</label></th>
            <td><input type="email" id="pca-supplier-email" class="regular-text"></td>
        </tr>

        <tr>
            <th><label>Address</label></th>
            <td><textarea id="pca-supplier-address" class="large-text"></textarea></td>
        </tr>

        <tr>
            <th><label>Notes</label></th>
            <td><textarea id="pca-supplier-notes" class="large-text"></textarea></td>
        </tr>

    </table>

    <p>
        <button class="button button-primary" id="pca-save-supplier">Save Supplier</button>
        <button class="button" id="pca-close-supplier-modal">Cancel</button>
    </p>

</div>
