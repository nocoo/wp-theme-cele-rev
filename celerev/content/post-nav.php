<?php

global $post;

// Helper function to get blog fallback URL
function ct_cele_get_blog_fallback_url() {
	if ( get_option( 'show_on_front' ) === 'page' ) {
		return get_permalink( get_option( 'page_for_posts' ) );
	} else {
		return get_home_url();
	}
}

// Helper function to generate fallback link
function ct_cele_get_fallback_link() {
	return '<a href="' . esc_url( ct_cele_get_blog_fallback_url() ) . '">' . esc_html__( 'Return to Blog', 'celerev' ) . '</a>';
}

// gets the previous post if it exists
$previous_post = get_adjacent_post( false, '', true );
$previous_text = esc_html__( 'Previous Post', 'celerev' );

if ( ! $previous_post ) {
	$previous_text  = esc_html__( 'No Older Posts', 'celerev' );
	$previous_link = ct_cele_get_fallback_link();
}

$next_post  = get_adjacent_post( false, '', false );
$next_text  = esc_html__( 'Next Post', 'celerev' );

if ( ! $next_post ) {
	$next_text  = esc_html__( 'No Newer Posts', 'celerev' );
	$next_link = ct_cele_get_fallback_link();
}

?>
<nav class="further-reading">
	<div class="previous">
		<span><?php echo esc_html( $previous_text ); ?></span>
		<?php
		if ( ! $previous_post ) {
			echo $previous_link;
		} else {
			previous_post_link( '%link' );
		}
		?>
	</div>
	<div class="next">
		<span><?php echo esc_html( $next_text ); ?></span>
		<?php
		if ( ! $next_post ) {
			echo $next_link;
		} else {
			next_post_link( '%link' );
		}
		?>
	</div>
</nav>