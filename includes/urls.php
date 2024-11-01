<?php
function woobing_tab_menu_url( $tab ) {
    $url = sprintf( '%1s?post_type=%2s&page=%3s&woobing_tab=%4s', admin_url( 'edit.php' ), 'product', woobing_page_slug(), $tab );
    return apply_filters( 'woobing_tab_menu_url', $url, woobing_page_slug(), $tab );
}

function woobing_subtab_menu_url( $tab, $sub_tab ) {
    $url = sprintf( '%1s?post_type=%2s&page=%3s&woobing_tab=%4s&woobing_sub_tab=%5s', admin_url( 'edit.php' ), 'product', woobing_page_slug(), $tab, $sub_tab );
    return apply_filters( 'woobing_subtab_menu_url', $url, woobing_page_slug(), $tab, $sub_tab );
}