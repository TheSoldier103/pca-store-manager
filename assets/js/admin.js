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
