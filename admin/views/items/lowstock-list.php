<?php
global $wpdb;

$items_table  = $wpdb->prefix . 'pca_store_items';
$packs_table  = $wpdb->prefix . 'pca_store_item_packs';
$stock_table  = $wpdb->prefix . 'pca_store_item_stock';

// ── Config ────────────────────────────────────────────────────────────────────
$per_page     = 25;
$current_page = max( 1, intval( $_GET['paged'] ?? 1 ) );
$offset       = ( $current_page - 1 ) * $per_page;

$filter_campus = intval( $_GET['campus']        ?? 0 );
$filter_dept   = intval( $_GET['department_id'] ?? 0 );
$filter_type   = sanitize_key( $_GET['type']    ?? '' );

// ── Department lookup ─────────────────────────────────────────────────────────
$departments = $wpdb->get_results( "
    SELECT id, name FROM {$wpdb->prefix}pca_store_departments
    WHERE is_active = 1 ORDER BY name ASC
" );
$dept_map = array_column( $departments, 'name', 'id' );

// ── WHERE clause ──────────────────────────────────────────────────────────────
// Stock lives in pca_store_item_stock, so we JOIN and compare there.
// campus filter: restrict to that campus's stock row.
// No campus filter: use the minimum stock across all campuses (most conservative).
$where_parts = [ "i.status != 'deleted'" ];
$where_args  = [];

if ( $filter_dept ) {
    $where_parts[] = 'i.department_id = %d';
    $where_args[]  = $filter_dept;
}

if ( $filter_type ) {
    $where_parts[] = 'i.item_type = %s';
    $where_args[]  = $filter_type;
}

$where_sql = implode( ' AND ', $where_parts );

// Stock JOIN + low-stock condition changes based on campus filter
if ( $filter_campus ) {
    $stock_join      = $wpdb->prepare(
        "JOIN $stock_table s ON s.item_id = i.id AND s.campus_id = %d",
        $filter_campus
    );
    $low_stock_where = 's.stock <= i.reorder_level';
} else {
    // Aggregate across all campuses; flag if any campus is low
    $stock_join      = "JOIN $stock_table s ON s.item_id = i.id";
    $low_stock_where = 's.stock <= i.reorder_level';
}

$full_where = $where_sql ? "$low_stock_where AND $where_sql" : $low_stock_where;

// ── Total count ───────────────────────────────────────────────────────────────
$count_query = "SELECT COUNT(DISTINCT i.id)
                FROM $items_table i
                $stock_join
                WHERE $full_where";

$total_items = (int) ( $where_args
    ? $wpdb->get_var( $wpdb->prepare( $count_query, ...$where_args ) )
    : $wpdb->get_var( $count_query )
);
$total_pages = (int) ceil( $total_items / $per_page );

// ── Main query ────────────────────────────────────────────────────────────────
// GROUP BY i.id so multi-campus rows don't duplicate; pick MIN stock for display.
$main_query = "SELECT i.*,
                      MIN(s.stock)      AS min_stock,
                      GROUP_CONCAT(CONCAT(s.campus_id, ':', s.stock) ORDER BY s.campus_id) AS campus_stocks
               FROM $items_table i
               $stock_join
               WHERE $full_where
               GROUP BY i.id
               HAVING MIN(s.stock) <= i.reorder_level
               ORDER BY min_stock ASC
               LIMIT %d OFFSET %d";

$paginated_args = array_merge( $where_args, [ $per_page, $offset ] );
$items = $wpdb->get_results( $wpdb->prepare( $main_query, ...$paginated_args ) );
?>

<h2 class="title">Low Stock Items</h2>

<div class="pca-filters">
    <form method="get">
        <input type="hidden" name="page"  value="pca-store-items">
        <input type="hidden" name="tab"   value="lowstock">
        <input type="hidden" name="paged" value="1">

        <label>Campus:</label>
        <select name="campus">
            <option value="">All Campuses</option>
            <?php
            $campuses = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}pca_store_campuses ORDER BY name ASC" );
            foreach ( $campuses as $c ) {
                printf(
                    '<option value="%d" %s>%s</option>',
                    $c->id,
                    selected( $filter_campus, $c->id, false ),
                    esc_html( $c->name )
                );
            }
            ?>
        </select>

        <label>Department:</label>
        <select name="department_id">
            <option value="">All</option>
            <?php foreach ( $departments as $d ) : ?>
                <option value="<?php echo $d->id; ?>" <?php selected( $filter_dept, $d->id ); ?>>
                    <?php echo esc_html( $d->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Item Type:</label>
        <select name="type">
            <option value="">All</option>
            <option value="single" <?php selected( $filter_type, 'single' ); ?>>Single Items</option>
            <option value="pack"   <?php selected( $filter_type, 'pack' ); ?>>Book Packs</option>
        </select>

        <button class="button" type="submit">Filter</button>
    </form>
</div>

<hr>

<?php if ( $total_items > 0 ) : ?>
    <p style="color:#666; font-size:13px;">
        Showing <?php echo number_format( $offset + 1 ); ?>–<?php echo number_format( min( $offset + $per_page, $total_items ) ); ?>
        of <?php echo number_format( $total_items ); ?> low stock items
    </p>
<?php endif; ?>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Department</th>
            <th>Type</th>
            <th>Class</th>
            <th>Stock</th>
            <th>Reorder Level</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if ( $items ) : ?>
            <?php foreach ( $items as $item ) : ?>
                <?php
                if ( $item->item_type === 'pack' ) {
                    $children = $wpdb->get_results( $wpdb->prepare(
                        "SELECT child_item_id, quantity FROM $packs_table WHERE pack_id = %d",
                        $item->id
                    ) );

                    $virtual_stock = PHP_INT_MAX;
                    foreach ( $children as $child ) {
                        $child_stock = (int) $wpdb->get_var( $wpdb->prepare(
                            "SELECT MIN(stock) FROM $stock_table WHERE item_id = %d",
                            $child->child_item_id
                        ) );
                        $virtual_stock = min( $virtual_stock, (int) floor( $child_stock / $child->quantity ) );
                    }
                    $display_stock = ( $virtual_stock === PHP_INT_MAX ) ? 0 : $virtual_stock;
                } else {
                    $display_stock = (int) $item->min_stock;
                }

                $dept_name = esc_html( $dept_map[ $item->department_id ] ?? '—' );
                ?>
                <tr>
                    <td><?php echo esc_html( $item->name );       ?></td>
                    <td><?php echo $dept_name;                     ?></td>
                    <td><?php echo esc_html( $item->item_type );   ?></td>
                    <td><?php echo esc_html( $item->class_level ); ?></td>
                    <td><?php echo $display_stock;                 ?></td>
                    <td><?php echo intval( $item->reorder_level ); ?></td>
                    <td><?php echo esc_html( $item->status );      ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="7">No low stock items found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ( $total_pages > 1 ) : ?>
    <div class="tablenav bottom" style="margin-top:12px;">
        <div class="tablenav-pages">
            <?php
            echo paginate_links( [
                'base'      => add_query_arg( 'paged', '%#%' ),
                'format'    => '',
                'current'   => $current_page,
                'total'     => $total_pages,
                'prev_text' => '&laquo; Prev',
                'next_text' => 'Next &raquo;',
            ] );
            ?>
        </div>
    </div>
<?php endif; ?>