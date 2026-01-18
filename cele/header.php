<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>
	<?php wp_head(); ?>
	<meta name="description" content="<?php bloginfo('description'); ?>">
	<meta name="robots" content="index, follow">
	<meta property="og:title" content="<?php wp_title(); ?>">
	<meta property="og:description" content="<?php bloginfo('description'); ?>">
	<meta property="og:url" content="<?php echo home_url(); ?>">
	<meta property="og:type" content="website">
</head>

<body id="<?php print get_stylesheet(); ?>" <?php body_class(); ?>>
<?php do_action( 'ct_cele_body_top' ); ?>
<?php 
if ( function_exists( 'wp_body_open' ) ) {
			wp_body_open();
	} else {
			do_action( 'wp_body_open' );
} ?>
<a class="skip-content" href="#main"><?php esc_html_e( 'Press "Enter" to skip to content', 'cele' ); ?></a>
<div id="overflow-container" class="overflow-container">
	<div id="max-width" class="max-width">
		<div id="main-sidebar" class="main-sidebar">
			<div id="sidebar-inner" class="sidebar-inner">
				<?php do_action( 'ct_cele_before_header' ); ?>
				<?php
				// Elementor `header` location
				if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :
				?>
				<header class="site-header" id="site-header" role="banner">
					<div id="title-container" class="title-container">
						<?php get_template_part( 'logo' ) ?>
						<?php if ( get_bloginfo( 'description' ) ) {
							echo '<p class="tagline">' . esc_html( get_bloginfo( 'description' ) ) . '</p>';
						} ?>
					</div>
					<button id="toggle-navigation" class="toggle-navigation" name="toggle-navigation" aria-expanded="false">
						<span class="screen-reader-text"><?php echo esc_html_x( 'open menu', 'verb: open the menu', 'cele' ); ?></span>
						<?php echo ct_cele_svg_output( 'toggle-navigation' ); ?>
					</button>
					<div id="menu-primary-container" class="menu-primary-container">
						<?php get_template_part( 'menu', 'primary' ); ?>
						<?php ct_cele_social_icons_output(); ?>
					</div>
				</header>
				<?php endif; ?>
				<?php do_action( 'ct_cele_after_header' ); ?>
				<?php get_sidebar( 'primary' ); ?>
			</div>
		</div>
		<section id="main" class="main" role="main">
			<?php do_action( 'ct_cele_main_top' );
			if ( function_exists( 'yoast_breadcrumb' ) ) {
				yoast_breadcrumb( '<p id="breadcrumbs">', '</p>' );
			}
