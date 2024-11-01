<?php
function woobing_pages() {
    $pages                     = array();
    $pages['woobing_new_feed']   = woobing_new_feed_items();
    $pages['woobing_feed_lists'] = woobing_feed_list_items();
    //$pages['woobing_license']  = woobing_license();

    return $pages;
}

function woobing_license() {
    $license = array();

    $license = array(
        'id'        => 'woobing-license',
        'title'     => __( 'License', 'woobing' ),
        'file_slug' => 'license/license',
        'file_path' => WOOBING_VIEWS . '/license/license.php',
    );

    return $license;
}

function woobing_new_feed_items() {
	$new_feed = array();

    $new_feed = array(
        'id'        => 'woobing-new-feed',
        'title'     => __( 'New Feed', 'woobing' ),
        'file_slug' => 'feed/feed',
        'file_path' => WOOBING_VIEWS . '/feed/new-feed.php',
    );

    return $new_feed;
}

function woobing_feed_list_items() {
	$new_feed = array();

    $new_feed = array(
        'id'        => 'woobing-feed-lists',
        'title'     => __( 'Feed Lists', 'woobing' ),
        'file_slug' => 'lists/lists',
        'file_path' => WOOBING_VIEWS . '/lists/feed-lists.php',
    );

    return $new_feed;
}

function woobing_page_slug() {
    return apply_filters( 'woobing_slug', 'product_woobing' );
}