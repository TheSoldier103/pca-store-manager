// jQuery(document).ready(function($){

//     // Open modal
//     $('#pca-add-stationery-btn').on('click', function(){
//         $('#pca-add-stationery-modal').show();
//     });

//     // Close modal
//     $('#pca-close-stationery-modal').on('click', function(){
//         $('#pca-add-stationery-modal').hide();
//     });

//     // Save (backend will be added later)
//     $('#pca-save-stationery').on('click', function(){
//         alert('Saving stationery... (backend coming later)');
//     });

// });

jQuery(document).ready(function($){

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

});


// Save item
jQuery(document).on('click', '#pca-save-item', function() {

    let data = {
        action: 'pca_store_save_item',
        item_type: jQuery('#pca-item-type').val(),
        name: jQuery('#pca-item-name').val(),
        price: jQuery('#pca-item-price').val(),
        reorder_level: jQuery('#pca-item-reorder').val(),
        department: jQuery('#pca-item-department').val(),
        supplier_id: jQuery('#pca-item-supplier').val(),
    };

    // Pack items
    if (data.item_type === 'pack') {
        data.pack_items = [];

        jQuery('.pca-pack-row').each(function() {
            data.pack_items.push({
                id: jQuery(this).data('id'),
                qty: jQuery(this).find('.pca-pack-qty').val()
            });
        });
    }

    jQuery.post(ajaxurl, data, function(response) {
        alert(response.data.message);
        location.reload();
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
