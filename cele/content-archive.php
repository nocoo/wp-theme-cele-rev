<div <?php post_class(); ?>>
	<?php do_action( 'ct_cele_archive_post_before' ); ?>
	<article>
		<div class='post-header'>
			<?php do_action( 'ct_cele_sticky_post_status' ); ?>
			<h2 class='post-title'>
				<a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a>
			</h2>
			<?php get_template_part( 'content/post-byline' ); ?>
		</div>
		<?php ct_cele_featured_image(); ?>
		<div class="post-content">
			<?php ct_cele_excerpt(); ?>
		</div>
		<?php
		if ( get_theme_mod( 'display_post_categories_blog' ) == 'show' ) {
			$categories = get_the_category( $post->ID );
			$output     = '';
			if ( $categories ) {
				echo '<p class="post-categories-blog">';
				echo '<i class="far fa-folder"></i>';
				foreach ( $categories as $category ) {
					$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" title="' . esc_attr( sprintf( esc_html_x( "View all posts in %s", 'View all posts in post category', 'cele' ), $category->name ) ) . '">' . esc_html( $category->cat_name ) . '</a>';
				}
				echo trim( $output, '');
				echo "</p>";
			}
		}
		if ( get_theme_mod( 'display_post_tags_blog' ) == 'show' ) {
			$tags = get_the_tags( $post->ID );
			$output     = '';
			if ( $tags ) {
				echo '<p class="post-tags-blog">';
				echo '<i class="fas fa-tag"></i>';
				foreach ( $tags as $tag ) {
					$output .= '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" title="' . esc_attr( sprintf( esc_html_x( "View all posts tagged %s", 'View all posts in post tag', 'cele' ), $tag->name ) ) . '">' . esc_html( $tag->name ) . '</a>';
				}
				echo trim( $output, '');
				echo "</p>";
			}
		}
		?>
	</article>
	<?php do_action( 'ct_cele_archive_post_after' ); ?>
</div>