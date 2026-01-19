<?php

// PERFORMANCE: Use cached value
$image = ct_cele_get_mod( 'logo_upload' );

if ( $image ) {
	$post_id = attachment_url_to_postid( $image );
	$image_alt = $post_id ? get_post_meta( $post_id, '_wp_attachment_image_alt', true ) : '';
	if ( empty( $image_alt ) ) {
		$image_alt = get_bloginfo( 'name' );
	}
	$logo = "<span class='screen-reader-text'>" . esc_html( get_bloginfo( 'name' ) ) . "</span><img class='logo' src='" . esc_url( $image ) . "' alt='" . esc_attr( $image_alt ) . "' />";
} else {
	$logo = esc_html( get_bloginfo( 'name' ) );
}

$output = "<div id='site-title' class='site-title'>";
$output .= "<a href='" . esc_url( home_url() ) . "'>";
$output .= $logo;
$output .= "</a>";
$output .= "</div>";

echo $output;