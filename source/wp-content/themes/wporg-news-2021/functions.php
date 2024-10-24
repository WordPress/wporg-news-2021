<?php

namespace WordPressdotorg\Theme\News_2021;

use WP_Query;
use function WordPressdotorg\MU_Plugins\Global_Fonts\get_font_stylesheet_url;

defined( 'WPINC' ) || die();

require_once __DIR__ . '/blocks/event-year/index.php';
require_once __DIR__ . '/blocks/month-in-wp-title/index.php';
require_once __DIR__ . '/blocks/podcast-player/index.php';
require_once __DIR__ . '/blocks/release-version/index.php';
require_once __DIR__ . '/blocks/post-type-name/index.php';

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\disable_jetpack_sharing' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_support', 9 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\override_category_query_args' );
add_filter( 'body_class', __NAMESPACE__ . '\clarify_body_classes' );
add_filter( 'post_class', __NAMESPACE__ . '\specify_post_classes', 10, 3 );
add_filter( 'render_block_data', __NAMESPACE__ . '\custom_query_block_attributes' );
add_filter( 'template_redirect', __NAMESPACE__ . '\jetpack_likes_workaround' );
add_action( 'tag_template_hierarchy', __NAMESPACE__ . '\override_tag_template' );
add_action( 'ssp_album_art_cover', __NAMESPACE__ . '\custom_default_album_art_cover', 10, 2 );
add_filter( 'wp_list_categories', __NAMESPACE__ . '\add_links_to_categories_list', 10, 2 );
add_filter( 'author_link', __NAMESPACE__ . '\use_wporg_profile_for_author_link', 10, 3 );
add_action( 'wp_print_footer_scripts', __NAMESPACE__ . '\print_events_category_archive_script' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\add_block_styles' );
add_filter( 'render_block_core/query-title', __NAMESPACE__ . '\modify_query_title' );
add_filter( 'render_block_data', __NAMESPACE__ . '\update_header_template_part_class' );
add_filter( 'wporg_block_navigation_menus', __NAMESPACE__ . '\add_site_navigation_menus' );
add_filter( 'get_the_archive_title', __NAMESPACE__ . '\update_archive_title' );
add_filter( 'render_block_core/site-title', __NAMESPACE__ . '\update_site_title' );

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
	add_editor_style( get_font_stylesheet_url() );

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
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	wp_enqueue_style(
		'wporg-news-style',
		get_stylesheet_uri(),
		array( 'wporg-global-fonts' ),
		filemtime( __DIR__ . '/style.css' )
	);
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
		if ( $query->is_category( 'community' ) ) {
			$query->set( 'posts_per_page', 20 );
		}
		if ( $query->is_category( 'month-in-wordpress' ) ) {
			$query->set( 'posts_per_page', 600 );
		}
		if ( $query->is_category( 'releases' ) ) {
			$query->set( 'posts_per_page', 100 );
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

	if ( is_search() && is_category() ) {
		$classes = array_filter(
			$classes,
			function ( $class ) {
				return ! str_starts_with( $class, 'category' );
			}
		);
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
	global $wp_query;
	if ( ! is_object( $wp_query ) || ! isset( $wp_query->posts ) ) {
		return $classes;
	}

	// Seems like the wp:query loop block doesn't count as "in the loop" so we'll do this the hard way:
	$current_post = null;
	$count_posts = count( $wp_query->posts );
	for ( $i = 0; $i < $count_posts; $i++ ) {
		if ( $wp_query->posts[ $i ]->ID === $post_id ) {
			$current_post = $i;
		}
	}

	// Add last-in-year to help put design elements in between year groups in the Month In WordPress category
	if ( $wp_query->is_category( 'month-in-wordpress' ) && $wp_query->post_count > 1 && ! is_null( $current_post ) ) {
		// The "0th" of the month returns the last day of the previous month.
		$date = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00' ) );
		$classes[] = 'post-year-' . $date->format( 'Y' );

		if ( $current_post < $count_posts - 1 ) {
			$this_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00' ) );
			$next_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00', $wp_query->posts[ $current_post + 1 ] ) );

			if ( $this_year->format( 'Y' ) !== $next_year->format( 'Y' ) ) {
				$classes[] = 'last-in-year';
			}
		}
	}

	// Add helper classes for the Events category.
	if ( $wp_query->is_category( 'events' ) && $wp_query->post_count > 0 && ! is_null( $current_post ) ) {
		$first_year_of_page = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-d 00:00:00', $wp_query->posts[0] ) );
		$this_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-d 00:00:00' ) );
		$classes[] = 'post-year-' . $this_year->format( 'Y' );

		if ( $first_year_of_page->format( 'Y' ) === $this_year->format( 'Y' ) ) {
			$classes[] = 'first-year-of-page';
		}

		if ( $current_post === 0 ) {
			$classes[] = 'first-in-year';
		}

		if ( $current_post === $count_posts - 1 ) {
			$classes[] = 'last-in-year';
		}

		if ( $current_post < $count_posts - 1 ) {
			$next_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-d 00:00:00', $wp_query->posts[ $current_post + 1 ] ) );

			if ( $this_year->format( 'Y' ) !== $next_year->format( 'Y' ) ) {
				$classes[] = 'last-in-year';
			}
		}

		if ( $current_post > 0 ) {
			$prev_year = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-d 00:00:00', $wp_query->posts[ $current_post - 1 ] ) );

			if ( $this_year->format( 'Y' ) !== $prev_year->format( 'Y' ) ) {
				$classes[] = 'first-in-year';
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
 * Force tag requests to use the Category template rather than the Podcast template.
 *
 * `SeriouslySimplePodcasting\Controllers\Frontend_Controller\add_all_post_types_for_tag_archive()` adds the
 * `podcast` post type to `$wp_query->query_vars['post_type']`. That causes the template loader to choose
 * `archive-podcast.html`, but we really want a standard content template instead.
 */
function override_tag_template( array $templates ) : array {
	if ( is_main_query() ) {
		// This must use the Classic `.php` hierarchy extension. It will later be automatically converted to `.html`
		// by Core.
		$templates = array( 'category.php' );
	}

	return $templates;
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
 * Prepend a link to "All Posts" to the category list.
 *
 * @param string $html HTML output.
 * @return string
 */
function add_links_to_categories_list( $html, $args ) {
	if ( '' !== $args['title_li'] ) {
		return $html;
	}

	$raw_links = explode( "\n\t", $html );
	$labels    = array_map(
		function( $link ) {
			preg_match( '|href="[^"]+">([^<]+)</a>|', $link, $matches );
			return $matches[1] ?? '';
		},
		$raw_links
	);
	$links = array_combine( $labels, $raw_links );

	// All Posts
	$links[ __( 'All Posts', 'wporg' ) ] = sprintf(
		'<li class="cat-item cat-item-0 %1$s"><a href="%2$s">%3$s</a></li>',
		is_home() ? 'current-cat' : '',
		site_url( '/all-posts/' ),
		__( 'All Posts', 'wporg' )
	);

	// Podcast
	$links[ __( 'Podcast', 'wporg' ) ] = sprintf(
		'<li class="cat-item cat-item-0 %1$s"><a href="%2$s">%3$s</a></li>',
		is_post_type_archive( 'podcast' ) ? 'current-cat' : '',
		get_post_type_archive_link( 'podcast' ),
		__( 'Podcast', 'wporg' )
	);

	ksort( $links );

	return implode( "\n\t", $links );
}

/**
 * Swap out the normal author archive link for the author's wp.org profile link.
 *
 * @param string $link            Overwritten.
 * @param int    $author_id       Unused.
 * @param string $author_nicename Used as the slug in the profiles URL.
 *
 * @return string
 */
function use_wporg_profile_for_author_link( $link, $author_id, $author_nicename ) {
	return sprintf(
		'https://profiles.wordpress.org/%s/',
		$author_nicename
	);
}

/**
 * Add a script to the footer of the Events category archive page.
 *
 * @return void
 */
function print_events_category_archive_script() {
	if ( ! is_category( 'events' ) ) {
		return;
	}

	ob_start();
	?>
<script id="wporg-news-2021-events-archive-handler">
	( () => {
		const getPostYear = ( element ) => {
			return Array.from( element.classList ).find( yearClass => yearClass.match( /^post-year-/ ) );
		};

		const eventHandler = ( event ) => {
			const row = event.currentTarget;
			const postYear = getPostYear( row );
			const yearGroup = document.getElementsByClassName( postYear );

			Array.from( yearGroup ).forEach( ( row ) => {
				switch ( event.type ) {
					case 'focus':
					case 'mouseenter':
						row.classList.add( 'active' );
						break;
					case 'blur':
					case 'mouseleave':
						row.classList.remove( 'active' );
						break;
				}
			} );
		};

		const rows = document.querySelectorAll('[class*="post-year-"]');
		Array.from( rows ).forEach( ( row ) => {
			const postYear = getPostYear( row );

			if ( postYear ) {
				row.addEventListener( 'focus', eventHandler, { capture: true } );
				row.addEventListener( 'mouseenter', eventHandler );
				row.addEventListener( 'blur', eventHandler, { capture: true } );
				row.addEventListener( 'mouseleave', eventHandler );
			}
		} );
	} )();
</script>
	<?php
	echo ob_get_clean();
}

/**
 * Register custom block styles for the News site.
 *
 * @return void
 */
function add_block_styles() {
	$props_styles = array(
		'short' => array(
			'name'  => 'wporg-props-short',
			'label' => __( 'Props (Short)', 'wporg' ),
		),
		'medium' => array(
			'name'  => 'wporg-props-medium',
			'label' => __( 'Props (Medium)', 'wporg' ),
		),
		'long' => array(
			'name'  => 'wporg-props-long',
			'label' => __( 'Props (Long)', 'wporg' ),
		),
	);

	register_block_style( 'core/paragraph', $props_styles['short'] );
	register_block_style( 'core/paragraph', $props_styles['medium'] );
	register_block_style( 'core/paragraph', $props_styles['long'] );
}

/**
 * Turns off Jetpack Social feature auto-share for posts.
 *
 * See: https://jetpack.com/support/jetpack-social/social-sharing-new-posts/
 */
function disable_jetpack_sharing() {
	remove_post_type_support( 'post', 'publicize' );
}

/**
 * Prevent the query title on search+category views.
 *
 * These pages use a template with a large heading, so the local-nav
 * heading is not necessary.
 *
 * @param string $block_content The block content.
 *
 * @return string
 */
function modify_query_title( $block_content ) {
	if ( is_search() && is_category() ) {
		return '';
	}
	return $block_content;
}

/**
 * Add the has-display-contents class to the header template so that the fixed local nav bar works.
 *
 * @param array $block The parsed block data.
 *
 * @return array
 */
function update_header_template_part_class( $block ) {
	if ( ! empty( $block['blockName'] ) && 'core/template-part' === $block['blockName'] ) {
		if ( str_starts_with( $block['attrs']['slug'], 'header' ) ) {
			if ( isset( $block['attrs']['className'] ) ) {
				$block['attrs']['className'] .= ' has-display-contents';
			} else {
				$block['attrs']['className'] = 'has-display-contents';
			}
		}
	}
	return $block;
}

/**
 * Provide a list of local navigation menus.
 */
function add_site_navigation_menus( $menus ) {
	$term_list = get_terms(
		array(
			'taxonomy' => 'category',
			'parent' => 0,
			'orderby' => 'name',
		)
	);

	$categories = array_map(
		function( $item ) {
			return array(
				'label' => $item->name,
				'url' => get_term_link( $item ),
				'className' => is_category( $item->term_id ) ? 'current-menu-item' : '',
			);
		},
		$term_list
	);

	// All Posts
	$categories[] = array(
		'label' => __( 'All Posts', 'wporg' ),
		'url' => site_url( '/all-posts/' ),
		'className' => is_home() ? 'current-menu-item' : '',
	);

	usort(
		$categories,
		function( $a, $b ) {
			return strcmp( $a['label'], $b['label'] );
		}
	);

	return array(
		'categories' => array(
			array(
				'label' => __( 'Categories', 'wporg' ),
				'submenu' => $categories,
			),
		),
	);
}

/**
 * Update the archive title on the Podcast archive.
 */
function update_archive_title( $title ) {
	if ( is_post_type_archive( 'podcast' ) ) {
		$title = __( 'WP Briefing Podcast', 'wporg' );
	}
	return $title;
}

/**
 * Update the archive title for all filter views.
 *
 * @param string $block_content The block content.
 */
function update_site_title( $block_content ) {
	return str_replace(
		get_bloginfo( 'name' ),
		__( 'News', 'wporg' ),
		$block_content
	);
}
