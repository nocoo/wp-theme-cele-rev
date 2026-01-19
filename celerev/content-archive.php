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
		// Optimized functions - no longer duplicated inline code
		ct_cele_output_blog_categories();
		ct_cele_output_blog_tags();
		?>
	</article>
	<?php do_action( 'ct_cele_archive_post_after' ); ?>
</div>