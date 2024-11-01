<?php
function woobing_get_query_args() {

    $menu = woobing_pages();

    if ( isset( $_GET['woobing_tab'] ) && ! empty( $_GET['woobing_tab'] ) ) {
        $tab = $_GET['woobing_tab'];
    } else {
        $tab = array_keys( $menu );
        $tab = reset( $tab ); 
    }

    if ( isset( $_GET['woogbing_sub_tab'] ) && !empty( $_GET['woogbing_sub_tab'] ) ) {
        $subtab = $_GET['woogbing_sub_tab'];
    } else if ( isset( $menu[$tab]['submenu'] ) && count( $menu[$tab]['submenu'] ) ) {
        $subtab = array_keys( $menu[$tab]['submenu'] );
        $subtab = reset( $subtab );
    } else {
        $subtab = false;
    }

    return array(
        'page' => woobing_page_slug(),
        'tab'  => $tab,
        'sub_tab' => $subtab
    );
}

function woobing_get_products( $count = '-1', $offset = 0 ) {
    $args = array(
        'posts_per_page' => 5,
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'offset'         => $offset
    );

    return get_posts( $args );
}

function woobing_get_feeds() {

    $args = array(
        'posts_per_page'   => -1,
        'post_type'        => array( 'woobing_feed', 'woogool_feed' ),
        'post_status'      => 'publish',
    );

    return get_posts( $args );
}