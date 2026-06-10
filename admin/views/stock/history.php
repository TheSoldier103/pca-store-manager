<?php
global $wpdb;

$movements_table = $wpdb->prefix . 'pca_store_stock_movements';
$items_table     = $wpdb->prefix . 'pca_store_items';
$users_table     = $wpdb->users;

// ── Config ────────────────────────────────────────────────────────────────────
$per_page     = 50;
$current_page = max( 1, intval( $_GET['paged'] ?? 1 ) );
$offset       = ( $current_page - 1 ) * $per_page;

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_campus = intval( sanitize_text_field( $_GET['campus']        ?? 0 ) );
$filter_type   = sanitize_key(               $_GET['movement_type']  ?? '' );
$filter_item   = sanitize_text_field(        $_GET['item_search']    ?? '' );
$filter_from   = sanitize_text_field(        $_GET['date_from']      ?? '' );
$filter_to     = sanitize_text_field(        $_GET['date_to']        ?? '' );

// ── Allowlisted movement types ────────────────────────────────────────────────
$valid_movement_types = [ 'add', 'sale', 'return', 'damage', 'correction', 'fulfill_owed', 'csv_import' ];

// ── WHERE clause ──────────────────────────────────────────────────────────────
$where_parts = [];
$where_args  = [];

if ( $filter_campus ) {
    $where_parts[] = 'm.campus_id = %d';
    $where_args[]  = $filter_campus;
}

if ( $filter_type && in_array( $filter_type, $valid_movement_types, true ) ) {
    $where_parts[] = 'm.movement_type = %s';
    $where_args[]  = $filter_type;
}

if ( $filter_item !== '' ) {
    $where_parts[] = 'i.name LIKE %s';
    $where_args[]  = '%' . $wpdb->esc_like( $filter_item ) . '%';
}

if ( $filter_from !== '' ) {
    $where_parts[] = 'DATE(m.created_at) >= %s';
    $where_args[]  = $filter_from;
}

if ( $filter_to !== '' ) {
    $where_parts[] = 'DATE(m.created_at) <= %s';
    $where_args[]  = $filter_to;
}

$where_sql = $where_parts ? 'WHERE ' . implode( ' AND ', $where_parts ) : '';

// ── Total count ───────────────────────────────────────────────────────────────
$count_query = "SELECT COUNT(*)
                FROM $movements_table m
                LEFT JOIN $items_table i ON i.id = m.item_id
                $where_sql";

$total_items = (int) ( $where_args
    ? $wpdb->get_var( $wpdb->prepare( $count_query, ...$where_args ) )
    : $wpdb->get_var( $count_query )
);
$total_pages = (int) ceil( $total_items / $per_page );

// ── Main query — single JOIN to users, no per-row get_userdata() ──────────────
$main_query = "SELECT
                   m.*,
                   i.name        AS item_name,
                   u.display_name AS user_name
               FROM $movements_table m
               LEFT JOIN $items_table i ON i.id  = m.item_id
               LEFT JOIN $users_table u ON u.ID  = m.created_by
               $where_sql
               ORDER BY m.created_at DESC
               LIMIT %d OFFSET %d";

$paginated_args = array_merge( $where_args, [ $per_page, $offset ] );
$rows = $wpdb->get_results( $wpdb->prepare( $main_query, ...$paginated_args ) );

// ── Campus list for filter dropdown ──────────────────────────────────────────
$campuses = $wpdb->get_results(
    "SELECT id, name FROM {$wpdb->prefix}pca_store_campuses ORDER BY name ASC"
);

// ── Movement type labels ──────────────────────────────────────────────────────
$type_labels = [
    'add'          => 'Stock Add',
    'sale'         => 'Sale',
    'return'       => 'Return',
    'damage'       => 'Damage',
    'correction'   => 'Correction',
    'fulfill_owed' => 'Fulfill Owed',
    'csv_import'   => 'CSV Import',
];
?>

<h2 class="title">Stock Movement History</h2>

<div class="pca-filters" style="margin-bottom:12px;">
    <form method="get" style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ?? 'pca-store-items' ); ?>">
        <input type="hidden" name="tab"  value="movements">
        <input type="hidden" name="paged" value="1">

        <div>
            <label style="display:block; font-size:12px; margin-bottom:3px;">Campus</label>
            <select name="campus">
                <option value="">All Campuses</option>
                <?php foreach ( $campuses as $c ) : ?>
                    <option value="<?php echo $c->id; ?>" <?php selected( $filter_campus, $c->id ); ?>>
                        <?php echo esc_html( $c->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; margin-bottom:3px;">Movement Type</label>
            <select name="movement_type">
                <option value="">All Types</option>
                <?php foreach ( $type_labels as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filter_type, $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display:block; font-size:12px; margin-bottom:3px;">Item Name</label>
            <input type="search" name="item_search" class="regular-text"
                   placeholder="Search item…"
                   value="<?php echo esc_attr( $filter_item ); ?>">
        </div>

        <div>
            <label style="display:block; font-size:12px; margin-bottom:3px;">From</label>
            <input type="date" name="date_from" value="<?php echo esc_attr( $filter_from ); ?>">
        </div>

        <div>
            <label style="display:block; font-size:12px; margin-bottom:3px;">To</label>
            <input type="date" name="date_to" value="<?php echo esc_attr( $filter_to ); ?>">
        </div>

        <div style="display:flex; gap:6px;">
            <button type="submit" class="button">Filter</button>
            <?php if ( $where_parts ) : ?>
                <a href="<?php echo esc_url( remove_query_arg( [ 'campus', 'movement_type', 'item_search', 'date_from', 'date_to', 'paged' ] ) ); ?>"
                   class="button">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ( $total_items > 0 ) : ?>
    <p style="color:#666; font-size:13px;">
        Showing <?php echo number_format( $offset + 1 ); ?>–<?php echo number_format( min( $offset + $per_page, $total_items ) ); ?>
        of <?php echo number_format( $total_items ); ?> movements
    </p>
<?php endif; ?>

<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Item</th>
            <th>Campus</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Before</th>
            <th>After</th>
            <th>Reference</th>
            <th>Notes</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
        <?php if ( $rows ) : ?>
            <?php foreach ( $rows as $m ) : ?>
                <tr>
                    <td><?php echo esc_html( wp_date( 'd M Y, g:ia', strtotime( $m->created_at ) ) ); ?></td>
                    <td><?php echo esc_html( $m->item_name ?? '—' ); ?></td>
                    <td><?php
                        // Resolve campus name from the list we already fetched
                        static $campus_map = null;
                        if ( $campus_map === null ) {
                            $campus_map = array_column( $campuses, 'name', 'id' );
                        }
                        echo esc_html( $campus_map[ $m->campus_id ] ?? "Campus {$m->campus_id}" );
                    ?></td>
                    <td><?php echo esc_html( $type_labels[ $m->movement_type ] ?? $m->movement_type ); ?></td>
                    <td><?php echo intval( $m->quantity ); ?></td>
                    <td><?php echo intval( $m->stock_before ); ?></td>
                    <td><?php echo intval( $m->stock_after ); ?></td>
                    <td>
                        <?php if ( $m->reference_type && $m->reference_id ) : ?>
                            <?php echo esc_html( $m->reference_type ); ?> #<?php echo intval( $m->reference_id ); ?>
                        <?php else : ?>
                            <?php echo esc_html( $m->reference_type ?: '—' ); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html( $m->notes ?: '—' ); ?></td>
                    <td><?php echo esc_html( $m->user_name ?? 'Unknown' ); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr><td colspan="10">No stock movements found.</td></tr>
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