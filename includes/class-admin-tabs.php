<?php

class PCA_Store_Admin_Tabs {

    public static function render_tabs( $tabs, $active ) {
        echo '<h2 class="nav-tab-wrapper">';

        foreach ( $tabs as $key => $label ) {
            $class = ($key === $active) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url   = add_query_arg( ['tab' => $key] );

            echo '<a href="' . esc_url($url) . '" class="' . $class . '">' . esc_html($label) . '</a>';
        }

        echo '</h2>';
    }
}
