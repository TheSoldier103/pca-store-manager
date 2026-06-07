<h2 class="title">Record Stationery Sale</h2>

<?php
global $wpdb;

// Determine campus
$campus_id = PCA_Store_Helpers::get_user_campus() ?: intval($_GET['campus_id'] ?? 0);

$items_table = $wpdb->prefix . 'pca_store_items';
$stock_table = $wpdb->prefix . 'pca_store_item_stock';

// Fetch stationery items WITH correct per‑campus stock
$stationery = $wpdb->get_results($wpdb->prepare("
    SELECT i.id, i.name, i.selling_price,
           COALESCE(s.stock, 0) AS stock
    FROM $items_table i
    LEFT JOIN $stock_table s 
        ON s.item_id = i.id AND s.campus_id = %d
    WHERE i.department = 'stationery'
      AND i.status = 'active'
    ORDER BY i.name ASC
", $campus_id));
?>

<table class="form-table">

    <tr>
        <th><label>Select Item</label></th>
        <td>
            <select id="pca-sale-item">
                <option value="">Select Item</option>

                <?php foreach ($stationery as $s): ?>
                    <option 
                        value="<?php echo $s->id; ?>" 
                        data-price="<?php echo $s->selling_price; ?>" 
                        data-stock="<?php echo $s->stock; ?>"
                    >
                        <?php echo esc_html($s->name); ?> 
                        (Stock: <?php echo intval($s->stock); ?>)
                    </option>
                <?php endforeach; ?>

            </select>
        </td>
    </tr>

    <tr>
        <th><label>Quantity</label></th>
        <td><input type="number" id="pca-sale-qty" value="1"></td>
    </tr>

    <tr>
        <th><label>Price</label></th>
        <td><input type="number" id="pca-sale-price" readonly></td>
    </tr>

    <tr>
        <th><label>Discount</label></th>
        <td><input type="number" id="pca-sale-discount" value="0"></td>
    </tr>

    <tr>
        <th><label>Total</label></th>
        <td><input type="number" id="pca-sale-total" readonly></td>
    </tr>

    <tr>
        <th><label>Receipt Number</label></th>
        <td><input type="text" id="pca-sale-receipt"></td>
    </tr>

    <tr>
        <th><label>Payment Method</label></th>
        <td>
            <select id="pca-sale-method">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="pos">POS</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label>Notes</label></th>
        <td><textarea id="pca-sale-notes" class="large-text"></textarea></td>
    </tr>

</table>

<!-- Department -->
<input type="hidden" id="pca-sale-department" value="stationery">

<!-- Campus -->
<input type="hidden" id="pca-sale-campus" value="<?php echo $campus_id; ?>">

<p>
    <button class="button button-primary" id="pca-record-sale-btn">Record Sale</button>
</p>

<!-- <script>
jQuery(document).ready(function($){

    // When item changes → update price + total
    $('#pca-sale-item').on('change', function(){
        let price = $('option:selected', this).data('price') || 0;
        $('#pca-sale-price').val(price);
        updateSaleTotal();
    });

    // Recalculate total
    function updateSaleTotal() {
        let price = parseFloat($('#pca-sale-price').val()) || 0;
        let qty = parseInt($('#pca-sale-qty').val()) || 1;
        let discount = parseFloat($('#pca-sale-discount').val()) || 0;

        let total = (price * qty) - discount;
        $('#pca-sale-total').val(total);
    }

    $('#pca-sale-qty, #pca-sale-discount').on('input', updateSaleTotal);

});
</script> -->
