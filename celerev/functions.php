<?php
//----------------------------------------------------------------------------------
//	Include all required files
//----------------------------------------------------------------------------------
require_once(trailingslashit(get_template_directory()) . 'theme-options.php');
require_once(trailingslashit(get_template_directory()) . 'inc/customizer.php');
require_once(trailingslashit(get_template_directory()) . 'inc/last-updated-meta-box.php');
require_once(trailingslashit(get_template_directory()) . 'inc/scripts.php');

//----------------------------------------------------------------------------------
// Theme Mod Cache - Performance optimization
//----------------------------------------------------------------------------------
// Cache all theme_mod values in one query to avoid N+1 query problems
if (! function_exists('ct_cele_get_theme_mods_cached')) {
    function ct_cele_get_theme_mods_cached()
    {
        static $cached_mods = null;

        if ($cached_mods === null) {
            // Get all theme mods at once - WordPress caches this internally
            $all_mods = get_theme_mods();
            $cached_mods = $all_mods ? $all_mods : array();
        }

        return $cached_mods;
    }
}

// Wrapper function to get a single theme mod from cache
if (! function_exists('ct_cele_get_mod')) {
    function ct_cele_get_mod($key, $default = false)
    {
        $mods = ct_cele_get_theme_mods_cached();
        return isset($mods[$key]) ? $mods[$key] : $default;
    }
}

if (! function_exists('ct_cele_set_content_width')) {
    function ct_cele_set_content_width()
    {
        if (! isset($content_width)) {
            $content_width = 891;
        }
    }
}
add_action('after_setup_theme', 'ct_cele_set_content_width', 0);

if (! function_exists('ct_cele_theme_setup')) {
    function ct_cele_theme_setup()
    {
        add_theme_support('post-thumbnails');
        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption'
        ));
        add_theme_support('infinite-scroll', array(
            'container' => 'loop-container',
            'footer'    => 'overflow-container',
            'render'    => 'ct_cele_infinite_scroll_render'
        ));

        register_nav_menus(array(
            'primary' => esc_html__('Primary', 'celerev')
        ));

        // Add WooCommerce support
        add_theme_support('woocommerce');
        // Add support for WooCommerce image gallery features
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        // Gutenberg - wide & full images
        add_theme_support('align-wide');

        // Gutenberg - add support for editor styles
        add_theme_support('editor-styles');

        // Gutenberg - modify the font sizes
        add_theme_support('editor-font-sizes', array(
            array(
                    'name' => __('small', 'celerev'),
                    'shortName' => __('S', 'celerev'),
                    'size' => 12,
                    'slug' => 'small'
            ),
            array(
                    'name' => __('regular', 'celerev'),
                    'shortName' => __('M', 'celerev'),
                    'size' => 16,
                    'slug' => 'regular'
            ),
            array(
                    'name' => __('large', 'celerev'),
                    'shortName' => __('L', 'celerev'),
                    'size' => 21,
                    'slug' => 'large'
            ),
            array(
                    'name' => __('larger', 'celerev'),
                    'shortName' => __('XL', 'celerev'),
                    'size' => 28,
                    'slug' => 'larger'
            )
        ));

        load_theme_textdomain('celerev', get_template_directory() . '/languages');
    }
}
add_action('after_setup_theme', 'ct_cele_theme_setup', 10);

//-----------------------------------------------------------------------------
// Load custom stylesheet for the post editor
//-----------------------------------------------------------------------------
if (! function_exists('ct_cele_add_editor_styles')) {
    function ct_cele_add_editor_styles()
    {
        add_editor_style('styles/editor-style.css');
    }
}
add_action('admin_init', 'ct_cele_add_editor_styles');

if (! function_exists('ct_cele_register_widget_areas')) {
    function ct_cele_register_widget_areas()
    {
        register_sidebar(array(
            'name'          => esc_html__('Primary Sidebar', 'celerev'),
            'id'            => 'primary',
            'description'   => esc_html__('Widgets in this area will be shown in the sidebar next to the main post content', 'celerev'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>'
        ));
    }
}
add_action('widgets_init', 'ct_cele_register_widget_areas');

if (! function_exists('ct_cele_customize_comments')) {
    function ct_cele_customize_comments($comment, $args, $depth)
    {
        $GLOBALS['comment'] = $comment;
        global $post; ?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<div class="comment-author">
				<?php
                echo get_avatar(get_comment_author_email(), 48, '', get_comment_author()); ?>
				<span class="author-name"><?php comment_author_link(); ?></span>
				<span class="comment-date"><?php comment_date(); ?></span>
			</div>
			<div class="comment-content">
				<?php if ($comment->comment_approved == '0') : ?>
					<em><?php esc_html_e('Your comment is awaiting moderation.', 'celerev') ?></em>
					<br/>
				<?php endif; ?>
				<?php comment_text(); ?>
			</div>
			<div class="comment-footer">
				<?php comment_reply_link(array_merge($args, array(
                    'reply_text' => esc_html_x('Reply', 'verb: reply to this comment', 'celerev'),
                    'depth'      => $depth,
                    'max_depth'  => $args['max_depth'],
                    'after'     => '<i class="fas fa-reply" aria-hidden="true"></i>'
                ))); ?>
				<?php edit_comment_link(esc_html_x('Edit', 'verb: reply to this comment', 'celerev'), '<div class="edit-comment-container">', '<i class="fas fa-edit" aria-hidden="true"></i></div>'); ?>
			</div>
		</article>
		<?php
    }
}

if (! function_exists('ct_cele_update_fields')) {
    function ct_cele_update_fields($fields)
    {
        $commenter = wp_get_current_commenter();
        $req       = get_option('require_name_email');
        $label     = $req ? '*' : ' ' . esc_html__('(optional)', 'celerev');
        $aria_req  = $req ? "aria-required='true'" : '';

        $fields['author'] =
            '<p class="comment-form-author">
	            <label for="author">' . esc_html_x("Name", "noun", "celerev") . $label . '</label>
	            <input id="author" name="author" type="text" placeholder="' . esc_attr__("Jane Doe", "celerev") . '" value="' . esc_attr($commenter['comment_author']) .
            '" size="30" ' . $aria_req . ' />
	        </p>';

        $fields['email'] =
            '<p class="comment-form-email">
	            <label for="email">' . esc_html_x("Email", "noun", "celerev") . $label . '</label>
	            <input id="email" name="email" type="email" placeholder="' . esc_attr__("name@email.com", "celerev") . '" value="' . esc_attr($commenter['comment_author_email']) .
            '" size="30" ' . $aria_req . ' />
	        </p>';

        $fields['url'] =
            '<p class="comment-form-url">
	            <label for="url">' . esc_html__("Website", "celerev") . '</label>
	            <input id="url" name="url" type="url"  placeholder="' . esc_attr__("http://google.com", "celerev") . '" value="' . esc_attr($commenter['comment_author_url']) .
            '" size="30" />
	            </p>';

        return $fields;
    }
}
add_filter('comment_form_default_fields', 'ct_cele_update_fields');

if (! function_exists('ct_cele_update_comment_field')) {
    function ct_cele_update_comment_field($comment_field)
    {
        // don't filter the WooCommerce review form
        if (function_exists('is_woocommerce')) {
            if (is_woocommerce()) {
                return $comment_field;
            }
        }

        $comment_field =
            '<p class="comment-form-comment">
	            <label for="comment">' . esc_html_x("Comment", "noun", "celerev") . '</label>
	            <textarea required id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
	        </p>';

        return $comment_field;
    }
}
add_filter('comment_form_field_comment', 'ct_cele_update_comment_field', 7);

if (! function_exists('ct_cele_remove_comments_notes_after')) {
    function ct_cele_remove_comments_notes_after($defaults)
    {
        $defaults['comment_notes_after'] = '';
        return $defaults;
    }
}
add_action('comment_form_defaults', 'ct_cele_remove_comments_notes_after');

if (! function_exists('ct_cele_filter_read_more_link')) {
    function ct_cele_filter_read_more_link($custom = false)
    {
        if (is_feed()) {
            return;
        }
        global $post;
        $ismore             = strpos($post->post_content, '<!--more-->');
        $read_more_text     = get_theme_mod('read_more_text');
        $new_excerpt_length = get_theme_mod('excerpt_length');
        $excerpt_more       = ($new_excerpt_length === 0) ? '' : '&#8230;';
        $output = '';

        // add ellipsis for automatic excerpts
        if (empty($ismore) && $custom !== true) {
            $output .= $excerpt_more;
        }
        // Because i18n text cannot be stored in a variable
        if (empty($read_more_text)) {
            $output .= '<div class="more-link-wrapper"><a class="more-link" href="' . esc_url(get_permalink()) . '">' . esc_html__('Continue Reading', 'celerev') . '<span class="screen-reader-text">' . esc_html(get_the_title()) . '</span></a></div>';
        } else {
            $output .= '<div class="more-link-wrapper"><a class="more-link" href="' . esc_url(get_permalink()) . '">' . esc_html($read_more_text) . '<span class="screen-reader-text">' . esc_html(get_the_title()) . '</span></a></div>';
        }
        return $output;
    }
}
add_filter('the_content_more_link', 'ct_cele_filter_read_more_link'); // more tags
add_filter('excerpt_more', 'ct_cele_filter_read_more_link', 10); // automatic excerpts

// handle manual excerpts
if (! function_exists('ct_cele_filter_manual_excerpts')) {
    function ct_cele_filter_manual_excerpts($excerpt)
    {
        $excerpt_more = '';
        if (has_excerpt()) {
            $excerpt_more = ct_cele_filter_read_more_link(true);
        }
        return $excerpt . $excerpt_more;
    }
}
add_filter('get_the_excerpt', 'ct_cele_filter_manual_excerpts');

if (! function_exists('ct_cele_excerpt')) {
    function ct_cele_excerpt()
    {
        global $post;
        $show_full_post = get_theme_mod('full_post');
        $ismore         = strpos($post->post_content, '<!--more-->');

        if ($show_full_post === 'yes' || $ismore) {
            the_content();
        } else {
            the_excerpt();
        }
    }
}

if (! function_exists('ct_cele_custom_excerpt_length')) {
    function ct_cele_custom_excerpt_length($length)
    {
        $new_excerpt_length = get_theme_mod('excerpt_length');

        if (! empty($new_excerpt_length) && $new_excerpt_length != 25) {
            return $new_excerpt_length;
        } elseif ($new_excerpt_length === 0) {
            return 0;
        } else {
            return 25;
        }
    }
}
add_filter('excerpt_length', 'ct_cele_custom_excerpt_length', 99);

if (! function_exists('ct_cele_remove_more_link_scroll')) {
    function ct_cele_remove_more_link_scroll($link)
    {
        $link = preg_replace('|#more-[0-9]+|', '', $link);
        return $link;
    }
}
add_filter('the_content_more_link', 'ct_cele_remove_more_link_scroll');

// Yoast OG description has "Continue readingPost Title Here" due to its use of get_the_excerpt(). This fixes that.
function ct_cele_update_yoast_og_description($ogdesc)
{
    $read_more_text = get_theme_mod('read_more_text');
    if (empty($read_more_text)) {
        $read_more_text = esc_html__('Continue Reading', 'celerev');
    }
    $ogdesc = substr($ogdesc, 0, strpos($ogdesc, $read_more_text));

    return $ogdesc;
}
add_filter('wpseo_opengraph_desc', 'ct_cele_update_yoast_og_description');

if (! function_exists('ct_cele_featured_image')) {
    function ct_cele_featured_image()
    {
        global $post;
        $featured_image = '';

        if (has_post_thumbnail($post->ID)) {
            if (is_singular()) {
                $featured_image = '<div class="featured-image">' . get_the_post_thumbnail($post->ID, 'full') . '</div>';
            } else {
                $featured_image = '<div class="featured-image"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . get_the_post_thumbnail($post->ID, 'full') . '</a></div>';
            }
        }

        $featured_image = apply_filters('ct_cele_featured_image', $featured_image);

        if ($featured_image) {
            echo $featured_image;
        }
    }
}

if (! function_exists('ct_cele_social_array')) {
    function ct_cele_social_array()
    {
        $social_sites = array(
            'twitter'       => 'cele_twitter_profile',
            'facebook'      => 'cele_facebook_profile',
            'instagram'     => 'cele_instagram_profile',
            'linkedin'      => 'cele_linkedin_profile',
            'pinterest'     => 'cele_pinterest_profile',
            'youtube'       => 'cele_youtube_profile',
            'rss'           => 'cele_rss_profile',
            'email'         => 'cele_email_profile',
            'phone'         => 'cele_phone_profile',
            'email-form'    => 'cele_email_form_profile',
            'amazon'        => 'cele_amazon_profile',
            'artstation'    => 'cele_artstation_profile',
            'bandcamp'      => 'cele_bandcamp_profile',
            'behance'       => 'cele_behance_profile',
            'bitbucket'     => 'cele_bitbucket_profile',
            'codepen'       => 'cele_codepen_profile',
            'delicious'     => 'cele_delicious_profile',
            'deviantart'    => 'cele_deviantart_profile',
            'digg'          => 'cele_digg_profile',
            'discord'       => 'cele_discord_profile',
            'dribbble'      => 'cele_dribbble_profile',
            'etsy'          => 'cele_etsy_profile',
            'flickr'        => 'cele_flickr_profile',
            'foursquare'    => 'cele_foursquare_profile',
            'github'        => 'cele_github_profile',
            'goodreads'		=> 'cele_goodreads_profile',
            'google-wallet' => 'cele_google_wallet_profile',
            'hacker-news'   => 'cele_hacker-news_profile',
            'medium'        => 'cele_medium_profile',
            'meetup'        => 'cele_meetup_profile',
            'mixcloud'      => 'cele_mixcloud_profile',
            'ok-ru'      	=> 'cele_ok_ru_profile',
            'orcid'      	=> 'cele_orcid_profile',
            'patreon'       => 'cele_patreon_profile',
            'paypal'        => 'cele_paypal_profile',
            'pocket'       	=> 'cele_pocket_profile',
            'podcast'       => 'cele_podcast_profile',
            'qq'            => 'cele_qq_profile',
            'quora'         => 'cele_quora_profile',
            'ravelry'       => 'cele_ravelry_profile',
            'reddit'        => 'cele_reddit_profile',
            'researchgate'  => 'cele_researchgate_profile',
            'skype'         => 'cele_skype_profile',
            'slack'         => 'cele_slack_profile',
            'slideshare'    => 'cele_slideshare_profile',
            'soundcloud'    => 'cele_soundcloud_profile',
            'spotify'       => 'cele_spotify_profile',
            'snapchat'      => 'cele_snapchat_profile',
            'stack-overflow' => 'cele_stack_overflow_profile',
            'steam'         => 'cele_steam_profile',
            'strava'        => 'cele_strava_profile',
            'telegram'      => 'cele_telegram_profile',
            'tencent-weibo' => 'cele_tencent_weibo_profile',
            'tumblr'        => 'cele_tumblr_profile',
            'twitch'        => 'cele_twitch_profile',
            'untappd'       => 'cele_untappd_profile',
            'vimeo'         => 'cele_vimeo_profile',
            'vk'            => 'cele_vk_profile',
            'wechat'        => 'cele_wechat_profile',
            'weibo'         => 'cele_weibo_profile',
            'whatsapp'      => 'cele_whatsapp_profile',
            'xing'          => 'cele_xing_profile',
            'yahoo'         => 'cele_yahoo_profile',
            'yelp'          => 'cele_yelp_profile',
            '500px'         => 'cele_500px_profile',
            'social_icon_custom_1' => 'social_icon_custom_1_profile',
            'social_icon_custom_2' => 'social_icon_custom_2_profile',
            'social_icon_custom_3' => 'social_icon_custom_3_profile'
        );

        return apply_filters('ct_cele_social_array_filter', $social_sites);
    }
}

if (! function_exists('ct_cele_social_icons_output')) {
    function ct_cele_social_icons_output()
    {
        // Icon class mapping - optimization #3
        $icon_classes = array(
            'rss'         => 'fas fa-rss',
            'email-form'  => 'far fa-envelope',
            'podcast'     => 'fas fa-podcast',
            'ok-ru'       => 'fab fa-odnoklassniki',
            'wechat'      => 'fab fa-weixin',
            'pocket'      => 'fab fa-get-pocket',
            'phone'       => 'fas fa-phone',
            'twitter'     => 'fab fa-x-twitter',
        );

        $social_sites = ct_cele_social_array();

        // PERFORMANCE: Get all theme mods at once from cache
        $cached_mods = ct_cele_get_theme_mods_cached();

        // Filter to only active sites - using cached values
        $active_sites = array();
        foreach ($social_sites as $social_site => $profile) {
            if (!empty($cached_mods[$social_site])) {
                $active_sites[$social_site] = $social_site;
            }
        }

        if (empty($active_sites)) {
            return;
        }

        echo "<ul class='social-media-icons'>";

        foreach ($active_sites as $key => $active_site) {
            // Get icon class from mapping or use default - optimization #3
            $class = isset($icon_classes[$active_site]) ? $icon_classes[$active_site] : 'fab fa-' . $active_site;

            echo '<li>';

            // Email - special handling
            if ($active_site == 'email') {
                $email = antispambot(is_email($cached_mods[$key]));
                echo '<a class="email" target="_blank" href="mailto:' . esc_attr($email) . '">';
                echo '<i class="fas fa-envelope" title="' . esc_attr_x('email', 'noun', 'celerev') . '"></i>';
                echo '<span class="screen-reader-text">' . esc_html_x('email', 'noun', 'celerev') . '</span>';
                echo '</a>';
            }
            // Skype - special protocol
            elseif ($active_site == 'skype') {
                $href = esc_url($cached_mods[$key], array('http', 'https', 'skype'));
                echo '<a class="' . esc_attr($active_site) . '" target="_blank" href="' . $href . '">';
                echo '<i class="' . esc_attr($class) . '" title="' . esc_attr($active_site) . '"></i>';
                echo '<span class="screen-reader-text">' . esc_html($active_site) . '</span>';
                echo '</a>';
            }
            // Phone - tel protocol
            elseif ($active_site == 'phone') {
                $href = esc_url($cached_mods[$active_site], array('tel'));
                echo '<a class="' . esc_attr($active_site) . '" target="_blank" href="' . $href . '">';
                echo '<i class="' . esc_attr($class) . '"></i>';
                echo '<span class="screen-reader-text">' . esc_html($active_site) . '</span>';
                echo '</a>';
            }
            // Custom icons - image based
            elseif (strpos($active_site, 'social_icon_custom') === 0) {
                $href = esc_url($cached_mods[$active_site]);
                $img_src = esc_url($cached_mods[$active_site . '_image']);
                $img_size = absint($cached_mods[$active_site . '_size']);
                echo '<a class="custom-icon" target="_blank" href="' . $href . '">';
                echo '<img class="icon" src="' . $img_src . '" style="width: ' . $img_size . 'px;" />';
                echo '<span class="screen-reader-text">' . esc_html($active_site) . '</span>';
                echo '</a>';
            }
            // Standard social icons
            else {
                $href = esc_url($cached_mods[$key]);
                echo '<a class="' . esc_attr($active_site) . '" target="_blank" href="' . $href . '">';
                echo '<i class="' . esc_attr($class) . '" title="' . esc_attr($active_site) . '"></i>';
                echo '<span class="screen-reader-text">' . esc_html($active_site) . '</span>';
                echo '</a>';
            }

            echo '</li>';
        }

        echo "</ul>";
    }
}

/*
 * WP will apply the ".menu-primary-items" class & id to the containing <div> instead of <ul>
 * making styling difficult and confusing. Using this wrapper to add a unique class to make styling easier.
 */
if (! function_exists('ct_cele_wp_page_menu')) {
    function ct_cele_wp_page_menu()
    {
        wp_page_menu(
            array(
                "menu_class" => "menu-unset",
                "depth"      => -1
            )
        );
    }
}

if (! function_exists('ct_cele_nav_dropdown_buttons')) {
    function ct_cele_nav_dropdown_buttons($item_output, $item, $depth, $args)
    {
        if ($args->theme_location == 'primary') {
            if (in_array('menu-item-has-children', $item->classes) || in_array('page_item_has_children', $item->classes)) {
                $item_output = str_replace($args->link_after . '</a>', $args->link_after . '</a><button class="toggle-dropdown" aria-expanded="false" name="toggle-dropdown"><span class="screen-reader-text">' . esc_html_x("open menu", "verb: open the menu", "celerev") . '</span><i class="fas fa-angle-down"></i></button>', $item_output);
            }
        }

        return $item_output;
    }
}
add_filter('walker_nav_menu_start_el', 'ct_cele_nav_dropdown_buttons', 10, 4);

if (! function_exists('ct_cele_sticky_post_marker')) {
    function ct_cele_sticky_post_marker()
    {
        if (is_sticky() && !is_archive() && !is_search()) {
            echo '<div class="sticky-status"><span>' . esc_html__("Featured", "celerev") . '</span></div>';
        }
    }
}
add_action('ct_cele_sticky_post_status', 'ct_cele_sticky_post_marker');

if (! function_exists('ct_cele_reset_customizer_options')) {
    function ct_cele_reset_customizer_options()
    {
        if (empty($_POST['cele_reset_customizer']) || 'cele_reset_customizer_settings' !== $_POST['cele_reset_customizer']) {
            return;
        }

        if (! wp_verify_nonce($_POST['cele_reset_customizer_nonce'], 'cele_reset_customizer_nonce')) {
            return;
        }

        if (! current_user_can('edit_theme_options')) {
            return;
        }

        $mods_array = array(
            'logo_upload',
            'full_post',
            'excerpt_length',
            'read_more_text',
            'display_post_author',
            'display_post_date',
            'custom_css'
        );

        $social_sites = ct_cele_social_array();

        // add social site settings to mods array
        foreach ($social_sites as $social_site => $value) {
            $mods_array[] = $social_site;
        }

        $mods_array = apply_filters('ct_cele_mods_to_remove', $mods_array);

        foreach ($mods_array as $theme_mod) {
            remove_theme_mod($theme_mod);
        }

        $redirect = admin_url('themes.php?page=cele-options');
        $redirect = add_query_arg('cele_status', 'deleted', $redirect);

        // safely redirect
        wp_safe_redirect($redirect);
        exit;
    }
}
add_action('admin_init', 'ct_cele_reset_customizer_options');

if (! function_exists('ct_cele_delete_settings_notice')) {
    function ct_cele_delete_settings_notice()
    {
        if (isset($_GET['cele_status'])) {
            if ($_GET['cele_status'] == 'deleted') {
                ?>
				<div class="updated">
					<p><?php esc_html_e('Customizer settings deleted.', 'celerev'); ?></p>
				</div>
				<?php
            }
        }
    }
}
add_action('admin_notices', 'ct_cele_delete_settings_notice');

if (! function_exists('ct_cele_body_class')) {
    function ct_cele_body_class($classes)
    {
        // PERFORMANCE: Use cached value
        $full_post = ct_cele_get_mod('full_post');

        if ($full_post === 'yes') {
            $classes[] = 'full-post';
        }

        return $classes;
    }
}
add_filter('body_class', 'ct_cele_body_class');

if (! function_exists('ct_cele_post_class')) {
    function ct_cele_post_class($classes)
    {
        $classes[] = 'entry';

        return $classes;
    }
}
add_filter('post_class', 'ct_cele_post_class');

if (! function_exists('ct_cele_custom_css_output')) {
    function ct_cele_custom_css_output()
    {
        if (function_exists('wp_get_custom_css')) {
            $custom_css = wp_get_custom_css();
        } else {
            $custom_css = get_theme_mod('custom_css');
        }

        if (! empty($custom_css)) {
            $custom_css = ct_cele_sanitize_css($custom_css);

            wp_add_inline_style('ct-cele-style', $custom_css);
            wp_add_inline_style('ct-cele-style-rtl', $custom_css);
        }
    }
}
add_action('wp_enqueue_scripts', 'ct_cele_custom_css_output', 20);

if (! function_exists('ct_cele_svg_output')) {
    function ct_cele_svg_output($type)
    {
        $svg = '';

        if ($type == 'toggle-navigation') {
            $svg = '<svg width="24px" height="18px" viewBox="0 0 24 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g transform="translate(-148.000000, -36.000000)" fill="#6B6B6B">
				            <g transform="translate(123.000000, 25.000000)">
				                <g transform="translate(25.000000, 11.000000)">
				                    <rect x="0" y="16" width="24" height="2"></rect>
				                    <rect x="0" y="8" width="24" height="2"></rect>
				                    <rect x="0" y="0" width="24" height="2"></rect>
				                </g>
				            </g>
				        </g>
				    </g>
				</svg>';
        }

        return $svg;
    }
}

if (! function_exists('ct_cele_add_meta_elements')) {
    function ct_cele_add_meta_elements()
    {
        $meta_elements = '';

        $meta_elements .= sprintf('<meta charset="%s" />' . "\n", esc_html(get_bloginfo('charset')));
        $meta_elements .= '<meta name="viewport" content="width=device-width, initial-scale=1" />' . "\n";

        echo $meta_elements;
    }
}
add_action('wp_head', 'ct_cele_add_meta_elements', 1);

if (! function_exists('ct_cele_infinite_scroll_render')) {
    function ct_cele_infinite_scroll_render()
    {
        while (have_posts()) {
            the_post();
            get_template_part('content', 'archive');
        }
    }
}

if (! function_exists('ct_cele_get_content_template')) {
    function ct_cele_get_content_template()
    {
        if (function_exists('is_bbpress')) {
            if (is_bbpress()) {
                get_template_part('content/bbpress');
                return;
            }
        }
        if (is_home() || is_archive()) {
            get_template_part('content-archive', get_post_type());
        } else {
            get_template_part('content', get_post_type());
        }
    }
}

// allow skype URIs to be used
if (! function_exists('ct_cele_allow_skype_protocol')) {
    function ct_cele_allow_skype_protocol($protocols)
    {
        $protocols[] = 'skype';

        return $protocols;
    }
}
add_filter('kses_allowed_protocols', 'ct_cele_allow_skype_protocol');

//----------------------------------------------------------------------------------
// Add paragraph tags for author bio displayed in content/archive-header.php.
// the_archive_description includes paragraph tags for tag and category descriptions, but not the author bio.
//----------------------------------------------------------------------------------
if (! function_exists('ct_cele_modify_archive_descriptions')) {
    function ct_cele_modify_archive_descriptions($description)
    {
        if (is_author()) {
            $description = wpautop($description);
        }
        return $description;
    }
}
add_filter('get_the_archive_description', 'ct_cele_modify_archive_descriptions');

//----------------------------------------------------------------------------------
// Output the markup for the optional scroll-to-top arrow
//----------------------------------------------------------------------------------
function ct_cele_scroll_to_top_arrow()
{
    $setting = get_theme_mod('scroll_to_top');

    if ($setting === 'yes') {
        echo '<button id="scroll-to-top" class="scroll-to-top"><span class="screen-reader-text">'. esc_html__('Scroll to the top', 'celerev') .'</span><i class="fas fa-arrow-up"></i></button>';
    }
}
add_action('ct_cele_body_bottom', 'ct_cele_scroll_to_top_arrow');

//----------------------------------------------------------------------------------
// Output the "Last Updated" date on posts
//----------------------------------------------------------------------------------
function ct_cele_output_last_updated_date()
{
    global $post;

    if (get_the_modified_date() != get_the_date()) {
        $updated_post = get_post_meta($post->ID, 'ct_cele_last_updated', true);
        $updated_customizer = get_theme_mod('last_updated');
        if (
            ($updated_customizer === 'yes' && $updated_post !== 'no')
            || $updated_post === 'yes'
        ) {
            echo '<p class="last-updated">'. esc_html__("Last updated on", "celerev") . ' ' . get_the_modified_date() . ' </p>';
        }
    }
}

//----------------------------------------------------------------------------------
// Output standar pagination and account for bbPress forum archives
//----------------------------------------------------------------------------------
function ct_cele_pagination()
{
    if (function_exists('is_bbpress')) {
        if (is_bbpress()) {
            return;
        }
    }
    the_posts_pagination(array(
    'prev_text' => esc_html__('Previous', 'celerev'),
    'next_text' => esc_html__('Next', 'celerev')
    ));
}

//----------------------------------------------------------------------------------
// Output categories on blog page - optimization #4
//----------------------------------------------------------------------------------
if (! function_exists('ct_cele_output_blog_categories')) {
    function ct_cele_output_blog_categories()
    {
        // PERFORMANCE: Use cached value
        if (ct_cele_get_mod('display_post_categories_blog') !== 'show') {
            return;
        }

        $categories = get_the_category();
        if (empty($categories)) {
            return;
        }

        echo '<p class="post-categories-blog">';
        echo '<i class="far fa-folder"></i>';

        foreach ($categories as $category) {
            $url = esc_url(get_category_link($category->term_id));
            $title = esc_attr(sprintf(esc_html_x("View all posts in %s", 'View all posts in post category', 'celerev'), $category->name));
            $name = esc_html($category->cat_name);
            echo '<a href="' . $url . '" title="' . $title . '">' . $name . '</a>';
        }

        echo '</p>';
    }
}

//----------------------------------------------------------------------------------
// Output tags on blog page - optimization #4
//----------------------------------------------------------------------------------
if (! function_exists('ct_cele_output_blog_tags')) {
    function ct_cele_output_blog_tags()
    {
        // PERFORMANCE: Use cached value
        if (ct_cele_get_mod('display_post_tags_blog') !== 'show') {
            return;
        }

        $tags = get_the_tags();
        if (empty($tags)) {
            return;
        }

        echo '<p class="post-tags-blog">';
        echo '<i class="fas fa-tag"></i>';

        foreach ($tags as $tag) {
            $url = esc_url(get_tag_link($tag->term_id));
            $title = esc_attr(sprintf(esc_html_x("View all posts tagged %s", 'View all posts in post tag', 'celerev'), $tag->name));
            $name = esc_html($tag->name);
            echo '<a href="' . $url . '" title="' . $title . '">' . $name . '</a>';
        }

        echo '</p>';
    }
}

//----------------------------------------------------------------------------------
// Add support for Elementor headers & footers
//----------------------------------------------------------------------------------
function ct_cele_register_elementor_locations($elementor_theme_manager)
{
    $elementor_theme_manager->register_location('header');
    $elementor_theme_manager->register_location('footer');
}
add_action('elementor/theme/register_locations', 'ct_cele_register_elementor_locations');


//----------------------------------------------------------------------------------
// Replace avatar
//----------------------------------------------------------------------------------
add_filter('get_avatar_url', function($url) {
    return str_replace(['www.gravatar.com', 'secure.gravatar.com'], 'gravatar.loli.net', $url);
});
