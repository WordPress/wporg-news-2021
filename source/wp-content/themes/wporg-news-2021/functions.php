<?php

namespace WordPressdotorg\Theme\News_2021;

use WP_Query;

defined( 'WPINC' ) || die();

require_once __DIR__ . '/blocks/month-in-wp-title/index.php';

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_support', 9 );
add_action( 'admin_init', __NAMESPACE__ . '\editor_styles' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_filter( 'get_the_archive_title_prefix', __NAMESPACE__ . '\modify_archive_title_prefix' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\override_category_query_args' );
add_filter( 'body_class', __NAMESPACE__ . '\clarify_body_classes' );
add_filter( 'post_class', __NAMESPACE__ . '\specify_post_classes', 10, 3 );
add_filter( 'render_block_data', __NAMESPACE__ . '\custom_query_block_attributes' );
add_filter( 'template_redirect', __NAMESPACE__ . '\jetpack_likes_workaround' );
add_filter( 'the_title', __NAMESPACE__ . '\update_the_title', 10, 2 );
add_action( 'ssp_album_art_cover', __NAMESPACE__ . '\custom_default_album_art_cover', 10, 2 );
add_filter( 'render_block', __NAMESPACE__ . '\customize_podcast_player_position', null, 2 );
add_filter( 'wp_list_categories', __NAMESPACE__ . '\add_all_posts_to_categories', 10, 2 );
add_action( 'parse_query', __NAMESPACE__ . '\compat_workaround_core_55100' );

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
			get_stylesheet_uri(),
		)
	);
}

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	// Enqueue Google fonts
	wp_enqueue_style( 'wporg-news-fonts', fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
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
		foreach ( $theme_data['typography']['fontFamilies']['theme'] as $font ) {
			if ( ! empty( $font['google'] ) ) {
				$font_families[] = $font['google'];
			}
		}
	}

	if ( ! empty( $theme_data['typography']['fontFamilies']['user'] ) ) {
		foreach ( $theme_data['typography']['fontFamilies']['user'] as $font ) {
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
	if ( is_category() || is_post_type_archive() ) {
		$prefix = '';
	}

	return $prefix;
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
	if ( is_front_page() ) {
		// Strip out "page" class, to prevent single-page styles from applying.
		$classes = array_diff( $classes, [ 'page' ] );
		$classes[] = 'news-front-page';
	}

	if ( is_home() ) {
		// Strip out "page" class, to prevent single-page styles from applying.
		$classes = array_diff( $classes, [ 'page' ] );
		$classes[] = 'news-posts-index';
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
	// The "0th" of the month returns the last day of the previous month.
	$date = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00' ) );
	$classes[] = 'post-year-' . $date->format( 'Y' );

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
				$this_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00' ) );
				$next_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00', $wp_query->posts[ $current_post + 1 ] ) );
				$prev_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00', $wp_query->posts[ $current_post - 1 ] ) );

				if ( $this_year->format( 'Y' ) !== $prev_year->format( 'Y' ) ) {
					$classes[] = 'first-in-year';
				}

				if ( $this_year->format( 'Y' ) !== $next_year->format( 'Y' ) ) {
					$classes[] = 'last-in-year';
				}
			}
		}
	}

	return $classes;
}

/**
 * Support some additional pseudo-attributes for the wp:query block; notably category slugs.
 *
 * This could be removed if https://github.com/WordPress/gutenberg/issues/36785 is resolved.
 *
 * @param array $parsed_block The block being rendered.
 *
 * @return array
 */
function custom_query_block_attributes( $parsed_block ) {
	if ( 'core/query' === $parsed_block['blockName'] ) {
		// If the block has a `category` attribute, then find the corresponding cat ID and set the `categoryIds` attribute.
		// TODO: support multiple?
		if ( isset( $parsed_block['attrs']['query']['category'] ) ) {
			$category = get_category_by_slug( $parsed_block['attrs']['query']['category'] );
			if ( $category ) {
				$parsed_block['attrs']['query']['categoryIds'] = [ $category->term_id ];
			}
		}
		if ( isset( $parsed_block['attrs']['query']['tag'] ) ) {
			$tag = get_term_by( 'slug', $parsed_block['attrs']['query']['tag'], 'post_tag' );
			if ( $tag ) {
				$parsed_block['attrs']['query']['tagIds'] = [ $tag->term_id ];
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
 * @return string           The updated url of the album image.
 */
function custom_default_album_art_cover( $album_art ) {
	if ( str_contains( $album_art, 'seriously-simple-podcasting' ) ) {
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
 * @param array  $block          The full block, including name and attributes.
 *
 * @return string               The block content about to be appended.
 */
function customize_podcast_player_position(
	$block_content,
	$block
) {
	if (
		'core/group' === $block['blockName'] &&
		! is_admin() &&
		! wp_is_json_request()
	) {
		if ( isset( $block['attrs']['className'] ) ) {
			if ( 'podcast-player' === $block['attrs']['className'] ) {
				$block_content = do_blocks( '<!-- wp:seriously-simple-podcasting/castos-html-player {"episodeId":"' . get_the_ID() . '"} /-->' );
			}
		}
	}

	return $block_content;
}

/**
 * Prepend a link to "All Posts" to the category list.
 *
 * @param string $html HTML output.
 * @return string
 */
function add_all_posts_to_categories( $html, $args ) {
	if ( '' !== $args['title_li'] ) {
		return $html;
	}

	$all_posts = sprintf(
		'<li class="cat-item cat-item-0 %1$s"><a href="%2$s">%3$s</a></li>',
		is_home() ? 'current-cat' : '',
		site_url( '/all-posts/' ),
		__( 'All Posts', 'wporg' )
	);
	return $all_posts . $html;
}

/**
 * Ensure that WP_Query::get_queried_object() works for /author/xxx requests.
 * 
 * @see https://core.trac.wordpress.org/ticket/55100
 * 
 * @param \WP_Query $query The WP_Query instance.
 */
function compat_workaround_core_55100( $query ) {
	$author_name = $query->get( 'author_name' );
	if ( $author_name ) {
		$author = get_user_by( 'slug', $author_name );
		if ( $author ) {
			$query->set( 'author', $author->ID );
		}
	}
}
