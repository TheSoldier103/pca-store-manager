jQuery(document).ready(function($){

    // Open modal
    $('#pca-add-stationery-btn').on('click', function(){
        $('#pca-add-stationery-modal').show();
    });

    // Close modal
    $('#pca-close-stationery-modal').on('click', function(){
        $('#pca-add-stationery-modal').hide();
    });

    // Save (backend will be added later)
    $('#pca-save-stationery').on('click', function(){
        alert('Saving stationery... (backend coming later)');
    });

});

jQuery(document).ready(function($){

    // Open modal
    $('#pca-add-supplier-btn').on('click', function(){
        $('#pca-add-supplier-modal').show();
    });

    // Close modal
    $('#pca-close-supplier-modal').on('click', function(){
        $('#pca-add-supplier-modal').hide();
    });

    // Save (backend coming later)
    $('#pca-save-supplier').on('click', function(){
        alert('Saving supplier... (backend coming later)');
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
