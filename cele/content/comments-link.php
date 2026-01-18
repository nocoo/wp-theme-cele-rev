<span class="comments-link">
	<i class="fas fa-comment" title="<?php _e( 'comment icon', 'cele' ); ?>"></i>
	<?php
	if ( ! comments_open() && get_comments_number() < 1 ) :
		comments_number( esc_html__( 'Comments closed', 'cele' ), esc_html__( '1 Comment', 'cele' ), esc_html_x( '% Comments', 'noun: 5 comments', 'cele' ) );
	else :
		echo '<a href="' . esc_url( get_comments_link() ) . '">';
		comments_number( esc_html__( 'Leave a Comment', 'cele' ), esc_html__( '1 Comment', 'cele' ), esc_html_x( '% Comments', 'noun: 5 comments', 'cele' ) );
		echo '</a>';
	endif;
	?>
</span>