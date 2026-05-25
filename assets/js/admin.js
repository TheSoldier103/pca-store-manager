jQuery(document).ready(function($){

    $(document).on('click', '.pca-edit-item', function(e){
        e.preventDefault();

        const id = $(this).data('id');

        $.get(ajaxurl, {
            action: 'pca_store_get_item',
            id: id
        }, function(response){

            if (!response.success) {
                alert(response.data.message);
                return;
            }

            const item = response.data.item;

            resetItemModal();

            $('#pca-item-id').val(item.id);
            $('#pca-item-name').val(item.name);
            $('#pca-item-price').val(item.selling_price);
            $('#pca-item-reorder').val(item.reorder_level);

            // Set department
            $('#pca-item-department').val(item.department_id).trigger('change');

            // Set supplier
            setTimeout(() => {
                $('#pca-item-supplier').val(item.supplier_id);
            }, 300);

            // Book fields
            $('#pca-item-class').val(item.class_level);
            $('#pca-item-subject').val(item.subject);

            // Uniform fields
            $('#pca-item-size').val(item.size);
            $('#pca-item-gender').val(item.gender);
            $('#pca-item-color').val(item.color);

            $('#pca-item-modal-title').text('Edit Item');
            $('#pca-add-item-modal').show();
        });
    });

    $(document).on('click', '.pca-delete-item', function(e){
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }

        const id = $(this).data('id');

        $.post(ajaxurl, {
            action: 'pca_store_delete_item',
            id: id
        }, function(response){
            if (!response.success) {
                alert(response.data.message);
                return;
            }

            alert(response.data.message);
            location.reload();
        });
    });



    // Open modal (new supplier)
    $('#pca-add-supplier-btn').on('click', function(e){
        e.preventDefault();
        $('#pca-supplier-id').val('');
        $('#pca-supplier-name').val('');
        $('#pca-supplier-department').val('');
        $('#pca-supplier-contact').val('');
        $('#pca-supplier-phone').val('');
        $('#pca-supplier-email').val('');
        $('#pca-supplier-address').val('');
        $('#pca-supplier-notes').val('');
        $('#pca-supplier-status').val('1');
        $('#pca-supplier-modal-title').text('Add Supplier');
        $('#pca-add-supplier-modal').show();
    });

    // Close modal
    $('#pca-close-supplier-modal').on('click', function(e){
        e.preventDefault();
        $('#pca-add-supplier-modal').hide();
    });

    // Edit supplier
    $(document).on('click', '.pca-edit-supplier', function(e){
        e.preventDefault();
        const id = $(this).data('id');

        $.get(ajaxurl, {
            action: 'pca_get_supplier',
            id: id
        }, function(response){
            if (!response || !response.success) {
                alert(response && response.data && response.data.message ? response.data.message : 'Error loading supplier');
                return;
            }

            const s = response.data.supplier;

            $('#pca-supplier-id').val(s.id);
            $('#pca-supplier-name').val(s.name);
            $('#pca-supplier-department').val(s.department_id);
            $('#pca-supplier-contact').val(s.contact_person);
            $('#pca-supplier-phone').val(s.phone);
            $('#pca-supplier-email').val(s.email);
            $('#pca-supplier-address').val(s.address);
            $('#pca-supplier-notes').val(s.notes);
            $('#pca-supplier-status').val(s.is_active ? '1' : '0');
            $('#pca-supplier-modal-title').text('Edit Supplier');
            $('#pca-add-supplier-modal').show();
        });
    });

    // Save supplier
    $('#pca-save-supplier').on('click', function(e){
        e.preventDefault();

        const data = {
            action: 'pca_save_supplier',
            id: $('#pca-supplier-id').val(),
            name: $('#pca-supplier-name').val(),
            department_id: $('#pca-supplier-department').val(),
            contact_person: $('#pca-supplier-contact').val(),
            phone: $('#pca-supplier-phone').val(),
            email: $('#pca-supplier-email').val(),
            address: $('#pca-supplier-address').val(),
            notes: $('#pca-supplier-notes').val(),
            is_active: $('#pca-supplier-status').val()
        };

        $.post(ajaxurl, data, function(response){
            if (!response || !response.success) {
                alert(response && response.data && response.data.message ? response.data.message : 'Error saving supplier');
                return;
            }

            alert(response.data.message || 'Supplier saved');
            window.location.reload();
        });
    });

    // Delete supplier
    $(document).on('click', '.pca-delete-supplier', function(e){
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this supplier?')) {
            return;
        }

        const id = $(this).data('id');

        $.post(ajaxurl, {
            action: 'pca_delete_supplier',
            id: id
        }, function(response){
            if (!response || !response.success) {
                alert(response && response.data && response.data.message ? response.data.message : 'Error deleting supplier');
                return;
            }

            alert(response.data.message || 'Supplier deleted');
            window.location.reload();
        });
    });

    /* ---------------------------------------------
        LOAD SUPPLIERS WHEN DEPARTMENT CHANGES
    --------------------------------------------- */
    $('#pca-item-department').on('change', function(){

        const deptId = $(this).val();

        // Clear supplier dropdown
        $('#pca-item-supplier').html('<option value="">Loading...</option>');

        $.get(ajaxurl, {
            action: 'pca_get_suppliers_by_department',
            department_id: deptId
        }, function(response){

            let html = '<option value="">Select Supplier</option>';

            if (response.success && response.data.suppliers.length > 0) {
                response.data.suppliers.forEach(function(s){
                    html += `<option value="${s.id}">${s.name}</option>`;
                });
            }

            $('#pca-item-supplier').html(html);
        });

        // Also toggle fields
        toggleItemFields();
    });


    /* ---------------------------------------------
       OPEN SINGLE ITEM MODAL (Books)
    --------------------------------------------- */
    $(document).on('click', '#pca-add-book-btn', function(e){
        e.preventDefault();
        resetItemModal();

        // Preselect Books department
        $('#pca-item-department option').filter(function(){
            return $(this).text().trim().toLowerCase() === 'books';
        }).prop('selected', true);

        $('#pca-item-department').trigger('change');

        toggleItemFields();

        $('#pca-item-modal-title').text('Add New Book');
        $('#pca-add-item-modal').show();
    });

    /* ---------------------------------------------
       OPEN SINGLE ITEM MODAL (Stationery)
    --------------------------------------------- */
    $(document).on('click', '#pca-add-stationery-btn', function(e){
        e.preventDefault();
        resetItemModal();

        // Preselect Stationery department
        $('#pca-item-department option').filter(function(){
            return $(this).text().trim().toLowerCase() === 'stationery';
        }).prop('selected', true);

        $('#pca-item-department').trigger('change');

        toggleItemFields();

        $('#pca-item-modal-title').text('Add New Stationery');
        $('#pca-add-item-modal').show();
    });


    /* ---------------------------------------------------------
        Persistent Selected Items
    --------------------------------------------------------- */
    let selectedPackItems = {};
    // Structure: { 12: { id:12, name:"Book", qty:1 }, ... }

    /* ---------------------------------------------------------
        Helpers
    --------------------------------------------------------- */
    function resetPackModal() {
        selectedPackItems = {};
        $('#pca-pack-id').val('');
        $('#pca-pack-name').val('');
        $('#pca-pack-class').val('');
        $('#pca-pack-price').val('');
        $('#pca-pack-reorder').val('');
        $('#pca-pack-class-filter').val('');
        $('#pca-pack-subject-filter').val('');
        $('#pca-pack-book-list').html('<p>Select a class or subject to view books.</p>');
        $('#pca-pack-save-feedback').text('').hide();
        renderSelectedBooks();
    }

    function renderSelectedBooks() {
        const tbody = $('#pca-selected-books-body');
        const items = Object.values(selectedPackItems);

        tbody.empty();

        if (items.length === 0) {
            tbody.append('<tr><td colspan="3"><em>No books selected yet.</em></td></tr>');
            $('#pca-selected-count').text('(0)');
            return;
        }

        items.forEach(item => {
            tbody.append(`
                <tr data-id="${item.id}">
                    <td>${item.name}</td>
                    <td>
                        <input type="number" class="pca-selected-qty small-text"
                            data-id="${item.id}" value="${item.qty}" min="1">
                    </td>
                    <td>
                        <button class="button button-small pca-remove-selected" data-id="${item.id}">✕</button>
                    </td>
                </tr>
            `);
        });

        $('#pca-selected-count').text(`(${items.length})`);
    }

    /* ---------------------------------------------------------
        Load Filtered Books
    --------------------------------------------------------- */
    function loadPackBooks() {
        const classLevel = $('#pca-pack-class-filter').val();
        const subject    = $('#pca-pack-subject-filter').val();

        if (!classLevel && !subject) {
            $('#pca-pack-book-list').html('<p>Select a class or subject to view books.</p>');
            return;
        }

        $('#pca-pack-book-list').html('<p><em>Loading…</em></p>');

        $.get(ajaxurl, {
            action: 'pca_store_get_books_for_pack',
            class: classLevel,
            subject: subject,
        }, function(response) {
            if (!response.success) {
                $('#pca-pack-book-list').html('<p>Failed to load books.</p>');
                return;
            }

            const books = response.data.books;

            if (!books.length) {
                $('#pca-pack-book-list').html('<p>No books found for these filters.</p>');
                return;
            }

            let html = `
                <table class="widefat striped">
                    <thead>
                        <tr><th width="30">✓</th><th>Book</th></tr>
                    </thead>
                    <tbody>
            `;

            books.forEach(book => {
                const checked = selectedPackItems[book.id] ? 'checked' : '';
                const meta = [book.class_level, book.subject].filter(Boolean).join(' – ');
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="pca-pack-select"
                                data-id="${book.id}" data-name="${book.name}" ${checked}>
                        </td>
                        <td>${book.name}${meta ? ' <small>(' + meta + ')</small>' : ''}</td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            $('#pca-pack-book-list').html(html);
        });
    }

    /* ---------------------------------------------------------
        Checkbox: Add / Remove
    --------------------------------------------------------- */
    $(document).on('change', '.pca-pack-select', function() {
        const id   = $(this).data('id');
        const name = $(this).data('name');

        if ($(this).is(':checked')) {
            selectedPackItems[id] = { id, name, qty: 1 };
        } else {
            delete selectedPackItems[id];
        }

        renderSelectedBooks();
    });

    /* ---------------------------------------------------------
        Remove from Selected (sync checkbox)
    --------------------------------------------------------- */
    $(document).on('click', '.pca-remove-selected', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        delete selectedPackItems[id];
        $(`.pca-pack-select[data-id="${id}"]`).prop('checked', false);
        renderSelectedBooks();
    });

    /* ---------------------------------------------------------
        Update Quantity
    --------------------------------------------------------- */
    $(document).on('input', '.pca-selected-qty', function() {
        const id  = $(this).data('id');
        const qty = parseInt($(this).val(), 10);
        if (selectedPackItems[id] && qty > 0) {
            selectedPackItems[id].qty = qty;
        }
    });

    /* ---------------------------------------------------------
        Filters
    --------------------------------------------------------- */
    $(document).on('change', '#pca-pack-class-filter, #pca-pack-subject-filter', loadPackBooks);

    /* ---------------------------------------------------------
        Open Modal
    --------------------------------------------------------- */
    $(document).on('click', '#pca-add-pack-btn', function(e) {
        e.preventDefault();
        resetPackModal();
        $('#pca-pack-modal-title').text('Add New Pack');
        $('#pca-add-pack-modal').show();
    });

    /* ---------------------------------------------------------
        Save Pack  ← THIS WAS MISSING
    --------------------------------------------------------- */
    $(document).on('click', '#pca-save-pack', function(e) {
        e.preventDefault();

        const name  = $('#pca-pack-name').val().trim();
        const deptId = $('#pca-pack-department-id').val();
        const packId = $('#pca-pack-id').val();
        const items  = Object.values(selectedPackItems);

        // Client-side validation
        if (!name) {
            alert('Please enter a pack name.');
            $('#pca-pack-name').focus();
            return;
        }
        if (items.length === 0) {
            alert('Please select at least one book for this pack.');
            return;
        }

        const $btn = $(this).prop('disabled', true).text('Saving…');

        $.post(ajaxurl, {
            action:        'pca_store_save_item',
            id:            packId,
            item_type:     'pack',
            name:          name,
            department_id: deptId,
            supplier_id:   0,
            selling_price: $('#pca-pack-price').val()   || 0,
            reorder_level: $('#pca-pack-reorder').val() || 0,
            class_level:   $('#pca-pack-class').val(),
            // pack_items:    items,   // array of { id, name, qty }
            pack_items: JSON.stringify(items),
        }, function(response) {
            if (response.success) {
                $('#pca-add-pack-modal').hide();
                resetPackModal();
                // TODO: refresh your packs list here, e.g.:
                // loadPacksList();
            } else {
                alert('Error: ' + (response.data?.message || 'Could not save pack.'));
            }
        }).fail(function() {
            alert('Server error. Please try again.');
        }).always(function() {
            $btn.prop('disabled', false).text('Save Pack');
        });
    });

    /* ---------------------------------------------------------
        Close Modals
    --------------------------------------------------------- */
    $('#pca-close-item-modal').on('click', function() {
        $('#pca-add-item-modal').hide();
    });

    $('#pca-close-pack-modal').on('click', function() {
        $('#pca-add-pack-modal').hide();
    });


    /* ---------------------------------------------
       TOGGLE FIELDS WHEN DEPARTMENT CHANGES
    --------------------------------------------- */
    $('#pca-item-department').on('change', function(){
        toggleItemFields();
    });

    function toggleItemFields() {
        const selectedName = $('#pca-item-department option:selected').text().trim().toLowerCase();

        // Hide all conditional fields first
        $('.pca-book-field').hide();
        $('.pca-uniform-field').hide();

        if (selectedName === 'books') {
            $('.pca-book-field').show();
        }

        if (selectedName === 'uniforms') {
            $('.pca-uniform-field').show();
        }
    }

    /* ---------------------------------------------
       RESET SINGLE ITEM MODAL
    --------------------------------------------- */
    function resetItemModal() {
        $('#pca-item-id').val('');
        $('#pca-item-name').val('');
        $('#pca-item-department').val('');
        $('#pca-item-supplier').val('');
        $('#pca-item-price').val('');
        $('#pca-item-reorder').val('');
        $('#pca-item-class').val('');
        $('#pca-item-subject').val('');
        $('#pca-item-size').val('');
        $('#pca-item-gender').val('');
        $('#pca-item-color').val('');
        $('#pca-item-type').val('single');
        $('#pca-item-department').trigger('change');
    }

    /* ---------------------------------------------
       RESET PACK MODAL
    --------------------------------------------- */
    function resetPackModal() {
        $('#pca-pack-id').val('');
        $('#pca-pack-name').val('');
        $('#pca-pack-class').val('');
        $('#pca-pack-price').val('');
        $('#pca-pack-reorder').val('');

        // Reset all qty fields to 1
        $('.pca-pack-qty').val(1);
    }

    /* ---------------------------------------------
       SAVE SINGLE ITEM
    --------------------------------------------- */
    $('#pca-save-item').on('click', function(e){
        e.preventDefault();

        const data = {
            action: 'pca_store_save_item',
            item_type: 'single',
            id: $('#pca-item-id').val(),
            name: $('#pca-item-name').val(),
            department_id: $('#pca-item-department').val(),
            supplier_id: $('#pca-item-supplier').val(),
            selling_price: $('#pca-item-price').val(),
            reorder_level: $('#pca-item-reorder').val(),
            class_level: $('#pca-item-class').val(),
            subject: $('#pca-item-subject').val(),
            size: $('#pca-item-size').val(),
            gender: $('#pca-item-gender').val(),
            color: $('#pca-item-color').val(),
        };

        $.post(ajaxurl, data, function(response){
            if (!response.success) {
                alert(response.data.message);
                return; // STOP HERE
            }

            alert(response.data.message);
            location.reload();
        });

    });

    /* ---------------------------------------------
       SAVE PACK
    --------------------------------------------- */
    $('#pca-save-pack').on('click', function(e){
        e.preventDefault();

        let packItems = [];

        $('.pca-pack-row').each(function(){
            packItems.push({
                id: $(this).data('id'),
                qty: $(this).find('.pca-pack-qty').val()
            });
        });

        const data = {
            action: 'pca_store_save_item',
            item_type: 'pack',
            id: $('#pca-pack-id').val(),
            name: $('#pca-pack-name').val(),
            department_id: $('#pca-pack-department-id').val(),
            selling_price: $('#pca-pack-price').val(),
            reorder_level: $('#pca-pack-reorder').val(),
            class_level: $('#pca-pack-class').val(),
            // pack_items: packItems
            pack_items: JSON.stringify(items)
            
        };

        $.post(ajaxurl, data, function(response){
            alert(response.data.message);
            location.reload();
        });
    });

});


// ADD STOCK
jQuery(document).on('click', '#pca-save-stock', function() {
    let data = {
        action: 'pca_store_add_stock',
        item_id: jQuery('#pca-stock-item').val(),
        qty: jQuery('#pca-stock-qty').val(),
        supplier_id: jQuery('#pca-stock-supplier').val(),
        notes: jQuery('#pca-stock-notes').val()
    };

    jQuery.post(ajaxurl, data, function(response) {
        alert(response.data.message);
        location.reload();
    });
});

// DAMAGE
jQuery(document).on('click', '#pca-save-damage', function() {
    let data = {
        action: 'pca_store_damage_stock',
        item_id: jQuery('#pca-damage-item').val(),
        qty: jQuery('#pca-damage-qty').val(),
        reason: jQuery('#pca-damage-reason').val(),
        notes: jQuery('#pca-damage-notes').val()
    };

    jQuery.post(ajaxurl, data, function(response) {
        alert(response.data.message);
        location.reload();
    });
});

// CORRECTION
jQuery(document).on('click', '#pca-save-correction', function() {
    let data = {
        action: 'pca_store_correct_stock',
        item_id: jQuery('#pca-correction-item').val(),
        new_qty: jQuery('#pca-correction-new').val(),
        notes: jQuery('#pca-correction-notes').val()
    };

    jQuery.post(ajaxurl, data, function(response) {
        alert(response.data.message);
        location.reload();
    });
});

// RETURNS
jQuery(document).on('click', '#pca-save-return', function() {
    let data = {
        action: 'pca_store_return_stock',
        item_id: jQuery('#pca-return-item').val(),
        qty: jQuery('#pca-return-qty').val(),
        reason: jQuery('#pca-return-reason').val(),
        notes: jQuery('#pca-return-notes').val()
    };

    jQuery.post(ajaxurl, data, function(response) {
        alert(response.data.message);
        location.reload();
    });
});

jQuery(document).on('click', '#pca-record-sale-btn', function(){

    let data = {
        action: 'pca_store_record_sale',
        item_id: jQuery('#pca-sale-item').val(),
        qty: jQuery('#pca-sale-qty').val(),
        price: jQuery('#pca-sale-price').val(),
        discount: jQuery('#pca-sale-discount').val(),
        receipt_no: jQuery('#pca-sale-receipt').val(),
        payment_method: jQuery('#pca-sale-method').val(),
        notes: jQuery('#pca-sale-notes').val(),
        department: jQuery('#pca-sale-department').val()
    };

    jQuery.post(ajaxurl, data, function(response){
        alert(response.data.message);
        location.reload();
    });
});


jQuery(function($){

    // Auto-fill price when selecting item
    $('#pca-sale-item').on('change', function(){
        let price = $('option:selected', this).data('price');
        $('#pca-sale-price').val(price);
    });

    // Record sale
    $('#pca-record-sale-btn').on('click', function(){

        let data = {
            action: 'pca_store_record_sale',
            item_id: $('#pca-sale-item').val(),
            qty: $('#pca-sale-qty').val(),
            price: $('#pca-sale-price').val(),
            discount: $('#pca-sale-discount').val() || 0,
            receipt_no: $('#pca-sale-receipt').val(),
            payment_method: $('#pca-sale-method').val(),
            notes: $('#pca-sale-notes').val(),
            department: $('#pca-sale-department').val()
        };

        $.post(ajaxurl, data, function(response){
            alert(response.data.message);
            location.reload();
        });
    });

});


function pcaLoadReport(url, target) {
    jQuery.get(url, function(response){
        if (response.success) {
            jQuery(target).html(JSON.stringify(response.data, null, 2));
        }
    });
}

jQuery(function($){

    $('#pca-save-campus').on('click', function(){

        let data = {
            action: 'pca_settings_save_campus',
            name: $('#pca-campus-name').val(),
            school_id: $('#pca-campus-school').val(),
            status: $('#pca-campus-status').val()
        };

        $.post(ajaxurl, data, function(response){
            alert(response.data.message);
            location.reload();
        });
    });

    $(document).on('click', '.pca-delete-campus', function(e){
        e.preventDefault();

        if (!confirm('Delete this campus?')) return;

        let id = $(this).data('id');

        $.post(ajaxurl, {
            action: 'pca_settings_delete_campus',
            id: id
        }, function(response){
            alert(response.data.message);
            location.reload();
        });
    });

});

// SAVE SCHOOL
jQuery(document).on('click', '#pca-save-school', function(){
    let data = {
        action: 'pca_settings_save_school',
        name: jQuery('#pca-school-name').val()
    };

    jQuery.post(ajaxurl, data, function(response){  // <-- was pcaStore.ajaxurl
        alert(response.data.message);
        location.reload();
    });
});

// DELETE SCHOOL
jQuery(document).on('click', '.pca-delete-school', function(e){
    e.preventDefault();
    if (!confirm('Delete this school?')) return;

    let id = jQuery(this).data('id');

    jQuery.post(ajaxurl, {                          // <-- was pcaStore.ajaxurl
        action: 'pca_settings_delete_school',
        id: id
    }, function(response){
        alert(response.data.message);
        location.reload();
    });
});


// SAVE DEPARTMENT
jQuery(document).on('click', '#pca-save-department', function(){

    let data = {
        action: 'pca_settings_save_department',
        name: jQuery('#pca-dept-name').val(),
        code: jQuery('#pca-dept-code').val()
    };

    jQuery.post(ajaxurl, data, function(response){
        alert(response.data.message);
        location.reload();
    });
});

// DELETE DEPARTMENT
jQuery(document).on('click', '.pca-delete-department', function(e){
    e.preventDefault();

    if (!confirm('Delete this department?')) return;

    let id = jQuery(this).data('id');

    jQuery.post(ajaxurl, {
        action: 'pca_settings_delete_department',
        id: id
    }, function(response){
        alert(response.data.message);
        location.reload();
    });
});

jQuery(function($){

    $('#pca-save-roles').on('click', function(){

        let data = {
            action: 'pca_settings_save_roles',
            can_edit_stock: $('#pca-role-edit-stock').is(':checked') ? 1 : 0,
            can_view_reports: $('#pca-role-view-reports').is(':checked') ? 1 : 0,
            can_manage_settings: $('#pca-role-manage-settings').is(':checked') ? 1 : 0
        };

        $.post(ajaxurl, data, function(response){
            alert(response.data.message);
        });
    });

});

jQuery(function($){

    $('#pca-save-advanced').on('click', function(){

        let data = {
            action: 'pca_settings_save_advanced',
            debug_mode: $('#pca-advanced-debug').is(':checked') ? 1 : 0
        };

        $.post(ajaxurl, data, function(response){
            alert(response.data.message);
        });
    });

});


