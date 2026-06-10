<?php
global $wpdb;

$items_table = $wpdb->prefix . 'pca_store_items';
$dept_table  = $wpdb->prefix . 'pca_store_departments';
$stock_table = $wpdb->prefix . 'pca_store_item_stock';

// ── Config ────────────────────────────────────────────────────────────────────
$per_page      = 20;
$current_page  = max( 1, intval( $_GET['paged']   ?? 1 ) );
$search        = sanitize_text_field( $_GET['pca_search'] ?? '' );
$orderby_input = sanitize_key( $_GET['orderby'] ?? 'name' );
$order_input   = strtoupper( sanitize_key( $_GET['order'] ?? 'ASC' ) );

// Allowlist sortable columns → map to actual SQL columns
$sortable_columns = [
    'name'    => 'i.name',
    'class'   => 'i.class_level',
    'subject' => 'i.subject',
    'price'   => 'i.selling_price',
];
$orderby = $sortable_columns[ $orderby_input ] ?? 'i.name';
$order   = ( $order_input === 'DESC' ) ? 'DESC' : 'ASC';
$offset  = ( $current_page - 1 ) * $per_page;

// ── Department ID ─────────────────────────────────────────────────────────────
$books_dept_id = (int) $wpdb->get_var(
    "SELECT id FROM $dept_table WHERE LOWER(name) = 'books' LIMIT 1"
);

// ── Build WHERE clause ────────────────────────────────────────────────────────
$where_args = [ $books_dept_id ];
$where_sql  = "i.department_id = %d AND i.item_type = 'single' AND i.status != 'deleted'";

if ( $search !== '' ) {
    $like        = '%' . $wpdb->esc_like( $search ) . '%';
    $where_sql  .= ' AND (i.name LIKE %s OR i.class_level LIKE %s OR i.subject LIKE %s)';
    $where_args  = array_merge( $where_args, [ $like, $like, $like ] );
}

// ── Total count (for pagination) ──────────────────────────────────────────────
$total_items = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $items_table i WHERE $where_sql",
        ...$where_args
    )
);
$total_pages = (int) ceil( $total_items / $per_page );

// ── Main query — single JOIN replaces N+1 stock queries ───────────────────────
$books = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT
            i.*,
            COALESCE(s1.stock, 0) AS ughelli_stock,
            COALESCE(s2.stock, 0) AS okuokoko_stock
         FROM $items_table i
         LEFT JOIN $stock_table s1 ON s1.item_id = i.id AND s1.campus_id = 1
         LEFT JOIN $stock_table s2 ON s2.item_id = i.id AND s2.campus_id = 2
         WHERE $where_sql
         ORDER BY $orderby $order
         LIMIT %d OFFSET %d",
        ...array_merge( $where_args, [ $per_page, $offset ] )
    )
);

// ── Helper: sortable <th> link ────────────────────────────────────────────────
function pca_sort_link( string $label, string $column, string $current_orderby, string $current_order ): string {
    $is_active   = ( $column === $current_orderby );
    $next_order  = ( $is_active && $current_order === 'ASC' ) ? 'DESC' : 'ASC';
    $arrow       = $is_active ? ( $current_order === 'ASC' ? ' ▲' : ' ▼' ) : '';
    $url         = add_query_arg( [ 'orderby' => $column, 'order' => $next_order, 'paged' => 1 ] );
    return '<a href="' . esc_url( $url ) . '" style="white-space:nowrap">'
        . esc_html( $label . $arrow ) . '</a>';
}

$action_nonce = wp_create_nonce( 'pca_item_action' );
?>

<h2 class="title">Books Inventory</h2>

<div style="display:flex; gap:8px; align-items:center; margin-bottom:12px; flex-wrap:wrap;">
    <button class="button button-primary" id="pca-add-book-btn">Add New Book</button>
    <button class="button" id="pca-import-books-btn">Import CSV</button>

    <form method="get" style="margin-left:auto; display:flex; gap:6px;">
        <?php
        // Preserve existing non-search query params (page slug, tab, etc.)
        foreach ( $_GET as $key => $val ) {
            if ( ! in_array( $key, [ 'pca_search', 'paged' ], true ) ) {
                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
            }
        }
        ?>
        <input
            type="search"
            name="pca_search"
            class="regular-text"
            placeholder="Search name, class, subject…"
            value="<?php echo esc_attr( $search ); ?>"
        >
        <button type="submit" class="button">Search</button>
        <?php if ( $search !== '' ): ?>
            <a href="<?php echo esc_url( remove_query_arg( [ 'pca_search', 'paged' ] ) ); ?>" class="button">Clear</a>
        <?php endif; ?>
    </form>
</div>

<?php if ( $total_items > 0 ): ?>
    <p style="color:#666; font-size:13px;">
        Showing <?php echo number_format( $offset + 1 ); ?>–<?php echo number_format( min( $offset + $per_page, $total_items ) ); ?>
        of <?php echo number_format( $total_items ); ?> books
        <?php if ( $search !== '' ) echo '— filtered by <strong>' . esc_html( $search ) . '</strong>'; ?>
    </p>
<?php endif; ?>

<table class="wp-list-table widefat fixed striped pca-items-table">
    <thead>
        <tr>
            <th><?php echo pca_sort_link( 'Name',    'name',    $orderby_input, $order ); ?></th>
            <th><?php echo pca_sort_link( 'Class',   'class',   $orderby_input, $order ); ?></th>
            <th><?php echo pca_sort_link( 'Subject', 'subject', $orderby_input, $order ); ?></th>
            <th><?php echo pca_sort_link( 'Price',   'price',   $orderby_input, $order ); ?></th>
            <th>Ughelli</th>
            <th>Okuokoko</th>
            <th>Status</th>
            <th width="120">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ( $books ): ?>
            <?php foreach ( $books as $book ): ?>
                <tr>
                    <td><?php echo esc_html( $book->name ); ?></td>
                    <td><?php echo esc_html( $book->class_level ); ?></td>
                    <td><?php echo esc_html( $book->subject ); ?></td>
                    <td>₦<?php echo number_format( $book->selling_price ); ?></td>
                    <td><?php echo intval( $book->ughelli_stock ); ?></td>
                    <td><?php echo intval( $book->okuokoko_stock ); ?></td>
                    <td><?php echo esc_html( $book->status ); ?></td>
                    <td style="white-space:nowrap;">
                        <a href="#"
                           class="pca-edit-item"
                           data-id="<?php echo esc_attr( $book->id ); ?>"
                           data-nonce="<?php echo esc_attr( $action_nonce ); ?>"
                        >Edit</a>
                        |
                        <a href="#"
                           class="button-danger pca-delete-item"
                           data-id="<?php echo esc_attr( $book->id ); ?>"
                           data-nonce="<?php echo esc_attr( $action_nonce ); ?>"
                        >Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">
                    <?php echo $search !== ''
                        ? 'No books match <strong>' . esc_html( $search ) . '</strong>.'
                        : 'No books found.';
                    ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ( $total_pages > 1 ): ?>
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

