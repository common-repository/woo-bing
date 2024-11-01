<?php

$header_path = dirname(__FILE__) . '/header.php';

if ( file_exists( $header_path ) ) {
    require_once $header_path;
}


$feeds =  woobing_get_feeds();
if ( ! count( $feeds ) ) {
	_e( 'No Feed Found!','woobing' );
	return;
}
$url_feed_download   = home_url( '?woobing_feed_download=true' );

?>
<table class="widefat">
	<thead>
		<tr>
			<th><?php _e( 'Feed Name', 'woobing' ); ?></th>
			<th><?php _e( 'Feed Preview Link', 'woobing' ); ?></th>
			<th><?php _e( 'Feed Download', 'woobing' ); ?></th>
			<th><?php _e( 'Edit', 'woobing' ); ?></th>
			<th><?php _e( 'Delete', 'woobing' ); ?></th>
		<tr>
	</thead>
	<tbody>
	<?php
		foreach ( $feeds as $key => $feed_obj ) {
			$url_feed_download = wp_nonce_url( home_url( '?woobing_feed_download=1&feed_id=' . $feed_obj->ID ), 'woobing_feed_download', 'nonce' );
			$url_feed_preview  = wp_nonce_url( home_url( '?woobing_feed_download=0&feed_id=' . $feed_obj->ID ), 'woobing_feed_download', 'nonce' );
			$edit_url          = admin_url( 'edit.php?post_type=product&page=product_woobing&woobing_tab=woobing_new_feed&feed_id=' . $feed_obj->ID );
			$delete_url        = admin_url( 'edit.php?post_type=product&page=product_woobing&woobing_tab=woobing_multiple&woobing_sub_tab=feed-lists&action=delete&feed_id=' . $feed_obj->ID );
			?>

			<tr>
				<td><?php echo $feed_obj->post_title; ?></td>
				<td><a href="<?php echo $url_feed_preview; ?>" target="_blank"><?php _e( 'View', 'woobing' ); ?></a></td>
				<td><a href="<?php echo $url_feed_download; ?>"><?php echo $url_feed_download; ?></a></td>
				<td><a href="<?php echo $edit_url; ?>"><?php _e( 'Edit', 'woobing' ); ?></a></td>
				<td><a href="<?php echo $delete_url; ?>"><?php _e( 'Delete', 'woobing' ); ?></a></td>
			</tr>

			<?php
		}
	?>
	</tbody>
</table>


