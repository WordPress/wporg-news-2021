<?php

namespace WordPressdotorg\Theme\News_2021;
use WP_Query;

defined( 'WPINC' ) || die();


/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_support', 9 );
add_action( 'admin_init', __NAMESPACE__ . '\editor_styles' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_filter( 'get_the_archive_title_prefix', __NAMESPACE__ . '\modify_archive_title_prefix' );
add_filter( 'template_include', __NAMESPACE__ . '\override_front_page_template' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\offset_paginated_index_posts' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\override_category_query_args' );
add_filter( 'body_class', __NAMESPACE__ . '\clarify_body_classes' );
add_filter( 'post_class', __NAMESPACE__ . '\specify_post_classes', 10, 3 );
add_filter( 'theme_file_path', __NAMESPACE__ . '\conditional_template_part', 10, 2 );
add_filter( 'render_block_data', __NAMESPACE__ . '\custom_query_block_attributes' );
add_filter( 'template_redirect', __NAMESPACE__ . '\jetpack_likes_workaround' );
add_filter( 'the_title', __NAMESPACE__ . '\update_the_title', 10, 2 );
add_action( 'ssp_album_art_cover', __NAMESPACE__ . '\custom_default_album_art_cover', 10, 2 );
add_filter('render_block', __NAMESPACE__ . '\customize_podcast_player_position', null, 2);

/**
 * Register theme support.
 */
function theme_support() {
	// Alignwide and alignfull classes in the block editor.
	add_theme_support( 'align-wide' );

	// Add support for experimental link color control.
	add_theme_support( 'experimental-link-color' );

	// Add support for responsive embedded content.
	// https://github.com/WordPress/gutenberg/issues/26901
	add_theme_support( 'responsive-embeds' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// Add support for post thumbnails.
	add_theme_support( 'post-thumbnails' );

	// Declare that there are no <title> tags and allow WordPress to provide them
	add_theme_support( 'title-tag' );

	// Experimental support for adding blocks inside nav menus
	add_theme_support( 'block-nav-menus' );

	// Remove the default margin-top added when the admin bar is used, this is
	// handled by the theme, in `_site-header.scss`.
	add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );

	// This theme has one menu location.
	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'wporg' ),
		)
	);
}

/**
 * Enqueue editor styles.
 */
function editor_styles() {
	// Enqueue editor styles.
	add_editor_style(
		array(
			fonts_url(),
			get_stylesheet_uri()
		)
	);
}

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	// Enqueue Google fonts
	wp_enqueue_style( 'wporg-news-fonts', fonts_url(), array(), null );
	wp_enqueue_style( 'wporg-news-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
}

/**
 * Add Google webfonts.
 *
 * @return string $fonts_url
 */
function fonts_url() {
	if ( ! class_exists( '\WP_Theme_JSON_Resolver' ) ) {
		return '';
	}

	$theme_data = \WP_Theme_JSON_Resolver::get_merged_data()->get_settings();
	if ( empty( $theme_data ) || empty( $theme_data['typography'] ) || empty( $theme_data['typography']['fontFamilies'] ) ) {
		return '';
	}

	$font_families = [];
	if ( ! empty( $theme_data['typography']['fontFamilies']['theme'] ) ) {
		foreach( $theme_data['typography']['fontFamilies']['theme'] as $font ) {
			if ( ! empty( $font['google'] ) ) {
				$font_families[] = $font['google'];
			}
		}
	}

	if ( ! empty( $theme_data['typography']['fontFamilies']['user'] ) ) {
		foreach( $theme_data['typography']['fontFamilies']['user'] as $font ) {
			if ( ! empty( $font['google'] ) ) {
				$font_families[] = $font['google'];
			}
		}
	}

	$font_families[] = 'display=swap';

	// Make a single request for the theme fonts.
	return esc_url_raw( 'https://fonts.googleapis.com/css2?' . implode( '&', $font_families ) );
}

/**
 * Blank out the archive title prefix sometimes.
 *
 * We want the prefix when it's used in `query-title-banner`, but not in `local-header`.
 *
 * TODO This filter can be removed if/when this issue is resolved: https://github.com/WordPress/gutenberg/issues/30519
 *
 * @return string
 */
function modify_archive_title_prefix( $prefix ) {
	if ( is_category() ) {
		$prefix = '';
	}

	return $prefix;
}

/**
 * Load the `index.html` template for `w.org/news/page/{n}` requests, rather than `front-page.html`.
 *
 * The design calls for a mix of `front-page.html` and `home.html`/`index.html` functionality. "home" is Core's
 * legacy terminology for the posts index from the pre-CMS days. The design calls it the "all posts" screen.
 *
 * Setting it up in separate files requires this hack, but feels more straight-forward overall. This lets us have
 * static content and the latest posts on the front page, while also preserving clean URLs. Using
 * `show_on_front = page` would change the URLs to `w.org/news/posts/page/2` rather than `w.org/news/page/2`.
 *
 * Another reason is that this keeps the markup for the front page separate from the posts index, because they're
 * not similar. Showing/hiding different content with CSS or dynamic template logic would result in a lot of cruft.
 * Gutenberg doesn't currently support dynamic templates either.
 *
 * This approach avoid avoids creating empty "dummy pages" in the database for the front page and posts index.
 *
 * @link https://core.trac.wordpress.org/ticket/16379
 * @link https://core.trac.wordpress.org/ticket/21237
 * @link https://wordpress.stackexchange.com/questions/110349/template-hierarchy-confused-with-index-php-front-page-php-home-php
 * @link https://github.com/WordPress/gutenberg/issues/32939
 *
 * @param string $template
 *
 * @return string
 */
function override_front_page_template( $template ) {
	if( is_posts_index() ) {
		$template = locate_block_template(
			get_stylesheet_directory() . '/block-templates/index.html' ,
			'index',
			array()
		);
	}

	return $template;
}

/**
 * Test if the current page is the front page, or the posts index screen.
 *
 * @see override_front_page_template for background.
 *
 * @param null|WP_Query $wp_query
 *
 * @return bool
 */
function is_posts_index( $wp_query = null ) {
	if ( ! $wp_query ) {
		global $wp_query;
	}

	return $wp_query->is_home() && $wp_query->is_main_query() && $wp_query->is_paged();
}

/**
 * Offset `/page/{n}/` posts by 5 to sync with front page.
 *
 * The front page displays the latest 5 posts, and then links to `/page/2` for the rest. The default
 * `posts_per_page` option is 10, though. If this weren't here, then posts 6-10 would be skipped.
 *
 * @param WP_Query $query
 *
 * @see override_front_page_template()
 */
function offset_paginated_index_posts( $query ) {
	$is_posts_index = is_posts_index( $query );

	if ( ! $is_posts_index ) {
		return;
	}

	// This must match the `perPage` value in `block-template-parts/front-page/latest-posts.html`.
	$posts_on_front_page = 5;
	$posts_per_page      = get_option( 'posts_per_page' );
	$current_page        = $query->get( 'paged' );
	$default_offset      = ( $current_page - 2 ) * $posts_per_page;

	$query->set( 'offset', $default_offset + $posts_on_front_page );
}

/**
 * Adjust WP_Query arguments separately for certain categories.
 *
 * We have certain category templates designed for different numbers of posts per page. Unfortunately it's not possible to set this in the block temlates.
 * (See https://github.com/WordPress/wporg-news-2021/issues/70#issuecomment-996460735)
 *
 * @param WP_Query $query
 */
function override_category_query_args( $query ) {
	if ( ! $query->get_queried_object() ) {
		return;
	}
	if ( $query->is_category() ) {
		if ( $query->is_category( 'month-in-wordpress' ) ) {
			$query->set( 'posts_per_page', 600 );
		}
		if ( $query->is_category( 'community' ) ) {
			$query->set( 'posts_per_page', 20 );
		}
	}
}

/**
 * Add body classes to distinguish the front page template from the posts index template.
 *
 * @see override_front_page_template()
 * @link https://core.trac.wordpress.org/ticket/21237
 * @link https://wordpress.stackexchange.com/questions/110349/template-hierarchy-confused-with-index-php-front-page-php-home-php
 *
 * @param array $classes
 *
 * @return array
 */
function clarify_body_classes( $classes ) {
	if ( is_home() ) {
		// The "news-" prefix helps distinguish from Core classes and prevent future conflicts.
		if ( is_paged() ) {
			$classes[] = 'news-posts-index';
		} else {
			$classes[] = 'news-front-page';
		}
	}

	return $classes;
}

/**
 * Add post classes to help make possible some design elements such as spacers between groups of posts.
 *
 * @param array $classes
 *
 * @return array
 */
function specify_post_classes( $classes, $extra_classes, $post_id ) {
	$classes[] = 'post-year-' . get_the_date( 'Y' );

	global $wp_query;

	// Add first-in-year and last-in-year to help put design elements in between year groups in the Month In WordPress category
	if ( is_object( $wp_query ) && $wp_query->is_category( 'month-in-wordpress' ) && $wp_query->post_count > 1 ) {
		// Seems like the wp:query loop block doesn't count as "in the loop" so we'll do this the hard way:
		$current_post = null;
		$count_posts = count( $wp_query->posts );
		for ( $i = 0; $i < $count_posts; $i++ ) {
			if ( $wp_query->posts[ $i ]->ID === $post_id ) {
				$current_post = $i;
			}
		}

		if ( ! is_null( $current_post ) ) {
			if ( 0 !== $current_post && $current_post < $count_posts - 1 ) {
				if ( get_the_date( 'Y' ) !== get_the_date( 'Y', $wp_query->posts[ $current_post - 1 ] ) ) {
					$classes[] = 'first-in-year';
				}
				if ( get_the_date( 'Y' ) !== get_the_date( 'Y', $wp_query->posts[ $current_post + 1 ] ) ) {
					$classes[] = 'last-in-year';
				}
			}
		}
	}

	return $classes;
}

function conditional_template_part( $path, $file ) {
	// Crudely simulate the $name parameter to get_template_part() for the wp:template-part block
	// Example: <!-- wp:template-part {"slug":"foo-bar{-test}"} -->
	// will attempt to use "foo-bar-test", and fall back to "foo-bar" if that template file does not exist
	if ( false !== strpos( $path, '{' ) && !file_exists( $path ) ) {
		if ( preg_match( '/[{]([-\w]+)[}]/', $path, $matches ) ) {
			$name = $matches[1];
			// Try "foo-bar-test"
			$new_path = str_replace( '{' . $name . '}', $name, $path );
			if ( file_exists( $new_path ) ) {
				$path = $new_path;
			} else {
				// If that doesn't exist, try "foo-bar"
				$new_path = str_replace( '{' . $name . '}', '', $path );
				if ( file_exists( $new_path ) ) {
					$path = $new_path;
				}
			}
		}

	}

	return $path;
}

/**
 * Support some additional pseudo-attributes for the wp:query block; notably category slugs.
 *
 * This could be removed if https://github.com/WordPress/gutenberg/issues/36785 is resolved.
 *
 * @param array         $parsed_block The block being rendered.
 *
 * @return array
 */

function custom_query_block_attributes( $parsed_block ) {
	if ( 'core/query' === $parsed_block['blockName'] ) {
		// If the block has a `category` attribute, then find the corresponding cat ID and set the `categoryIds` attribute.
		// TODO: support multiple?
		if ( isset( $parsed_block[ 'attrs' ][ 'query' ][ 'category' ] ) ) {
			$category = get_category_by_slug( $parsed_block[ 'attrs' ][ 'query' ][ 'category' ] );
			if ( $category ) {
				$parsed_block[ 'attrs' ][ 'query' ][ 'categoryIds' ] = [ $category->term_id ];
			}
		}
		if ( isset( $parsed_block[ 'attrs' ][ 'query' ][ 'tag' ] ) ) {
			$tag = get_term_by( 'slug', $parsed_block[ 'attrs' ][ 'query' ][ 'tag' ], 'post_tag' );
			if ( $tag ) {
				$parsed_block[ 'attrs' ][ 'query' ][ 'tagIds' ] = [ $tag->term_id ];
			}

		}
	}

	return $parsed_block;
}

/**
 * A Workaround to make Jetpack Likes work with FSE themes.
 *
 * This is only needed until Jetpack Likes is updated to work properly with FSE themes,
 * or https://core.trac.wordpress.org/ticket/54529 is merged to Core.
 */

function jetpack_likes_workaround() {
	$jetpack_likes = class_exists( '\Jetpack_Likes' ) ? \Jetpack_Likes::init() : false;
	if ( is_callable( [ $jetpack_likes, 'load_styles_register_scripts' ] ) ) {
		$jetpack_likes->load_styles_register_scripts();
	}
}

/**
 * Remove "WordPress" from the release post title.
 *
 * @param string $title The post title.
 * @param int    $id    The post ID.
 * @return string Filtered post title.
 */
function update_the_title( $title, $id ) {
	// Remove "WordPress" from the post title in the Latest Release section on the front page.
	$category_slugs = wp_list_pluck( get_the_category( $id ), 'slug' );
	if ( is_front_page() && in_array( 'releases', $category_slugs ) ) {
		return str_replace( 'WordPress', '', $title );
	}

	return $title;
}

/**
 * Replaces the default artwork for the podcast player when none is defined
 *
 * @param string $album_art The url of the album image.
 * @return string 			The updated url of the album image.
 */
function custom_default_album_art_cover( $album_art ) {
	if( str_contains($album_art, 'seriously-simple-podcasting') ) {
		$album_art = get_stylesheet_directory_uri() . '/images/podcast-player/default_artwork.jpg';
	}
	
	return $album_art;
}

/**
 * Inserts the podcast player in a group block that has the 'podcast-player' or 'podcast-player-audio' class names
 * 'podcast-player' inserts the HTML5 player with album art
 * 'podcast-player-audio' inserts the standard compact player
 *
 * @param string $block_content The block content about to be appended.
 * @param array $block 			The full block, including name and attributes.
 * 
 * @return string 				The block content about to be appended.
 */
function customize_podcast_player_position (
	$block_content, 
	$block
) {
	if (
		$block['blockName'] === 'core/group' && 
		!is_admin() &&
		!wp_is_json_request()
	) {
		if( isset($block['attrs']['className']) ) {
			if( $block['attrs']['className'] == 'podcast-player' ) {
				$block_content = do_blocks( '<!-- wp:seriously-simple-podcasting/castos-html-player {"episodeId":"'.get_the_ID().'"} /-->' );
			}
		}
	}

	return $block_content;
}
