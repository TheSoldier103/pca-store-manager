<h2 class="title">Book Packs</h2>
<button class="button button-primary" id="pca-add-pack-btn">Add New Pack</button>

<table class="wp-list-table widefat fixed striped pca-items-table">
    <thead>
        <tr>
            <th>Pack Name</th>
            <th>Class</th>
            <th>Books</th>
            <th>Price</th>
            <th>Virtual Stock</th>
            <th width="120">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $wpdb;

        $items_table = $wpdb->prefix . 'pca_store_items';
        $packs_table = $wpdb->prefix . 'pca_store_item_packs';
        $dept_table  = $wpdb->prefix . 'pca_store_departments';
        $stock_table = $wpdb->prefix . 'pca_store_item_stock';

        $books_dept_id = $wpdb->get_var("
            SELECT id FROM $dept_table 
            WHERE LOWER(name) = 'books'
            LIMIT 1
        ");

        $pack_items = $wpdb->get_results($wpdb->prepare("
            SELECT i.*, COUNT(p.child_item_id) AS book_count
            FROM $items_table i
            LEFT JOIN $packs_table p ON p.pack_id = i.id
            WHERE i.department_id = %d
            AND i.item_type = 'pack'
            AND i.status != 'deleted'
            GROUP BY i.id
            ORDER BY i.name ASC
        ", $books_dept_id));

        if ($pack_items):
            foreach ($pack_items as $pack):

                // Child items with names
                $children = $wpdb->get_results($wpdb->prepare("
                    SELECT 
                        p.quantity,
                        i.name,
                        (
                            SELECT stock FROM $stock_table 
                            WHERE item_id = i.id AND campus_id = 1
                        ) AS stock_ughelli,
                        (
                            SELECT stock FROM $stock_table 
                            WHERE item_id = i.id AND campus_id = 2
                        ) AS stock_okuokoko
                    FROM $packs_table p
                    INNER JOIN $items_table i ON i.id = p.child_item_id
                    WHERE p.pack_id = %d
                    ORDER BY i.name ASC
                ", $pack->id));

                // Virtual stock
                $virtual_ughelli = null;
                $virtual_okuokoko = null;

                foreach ($children as $child) {

                    $qty = max(1, intval($child->quantity));

                    $stock_u = max(0, intval($child->stock_ughelli));
                    $stock_o = max(0, intval($child->stock_okuokoko));

                    $possible_u = floor($stock_u / $qty);
                    $possible_o = floor($stock_o / $qty);

                    $virtual_ughelli = is_null($virtual_ughelli) ? $possible_u : min($virtual_ughelli, $possible_u);
                    $virtual_okuokoko = is_null($virtual_okuokoko) ? $possible_o : min($virtual_okuokoko, $possible_o);
                }

                $virtual_stock = min($virtual_ughelli, $virtual_okuokoko);



                $row_id = 'pca-children-' . $pack->id;
                ?>

                <tr class="pca-pack-row" data-id="<?php echo $pack->id; ?>">
                    <td>
                        <button class="pca-pack-toggle" 
                            aria-expanded="false"
                            aria-controls="<?php echo $row_id; ?>"
                            title="Show books in this pack">+</button>
                        <?php echo esc_html($pack->name); ?>
                    </td>
                    <td><?php echo esc_html($pack->class_level); ?></td>
                    <td><?php echo intval($pack->book_count); ?></td>
                    <td>₦<?php echo number_format($pack->selling_price, 2); ?></td>
                    <td><?php echo intval($virtual_stock); ?></td>
                    <td style="white-space:nowrap;">
                        <a href="#" class="pca-edit-pack" data-id="<?php echo $pack->id; ?>">Edit</a> |
                        <a href="#" class="pca-add-stationery-to-pack" data-id="<?php echo $pack->id; ?>">Add Stationery</a> |
                        <a href="#" class="button-danger pca-delete-pack" data-id="<?php echo $pack->id; ?>">Delete</a>
                    </td>
                </tr>

                <tr class="pca-pack-children" id="<?php echo $row_id; ?>">
                    <td colspan="6">
                        <div class="pca-pack-children-inner">
                            <?php if ($children): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Book</th>
                                            <th>Qty in Pack</th>
                                            <th>Ughelli Stock</th>
                                            <th>Okuokoko Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($children as $child): ?>
                                            <tr>
                                                <td><?php echo esc_html($child->name); ?></td>
                                                <td><?php echo intval($child->quantity); ?></td>
                                                <td><?php echo intval($child->stock_u); ?></td>
                                                <td><?php echo intval($child->stock_o); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <span class="pca-no-children">No books assigned to this pack.</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

            <?php endforeach;
        else: ?>
            <tr><td colspan="6">No packs found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
