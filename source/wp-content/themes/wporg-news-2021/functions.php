<?php

namespace WordPressdotorg\Theme\News;

/**
 * Actions and filters.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_support', 9 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_assets' );
add_action( 'admin_init', __NAMESPACE__ . '\editor_styles' );

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
	// Enqueue Google fonts
	wp_enqueue_style( 'wporg-news-fonts', fonts_url(), array(), null );
	wp_enqueue_style( 'wporg-news-ponyfill', get_template_directory_uri() . '/assets/ponyfill.css', array(), wp_get_theme()->get( 'Version' ) );
}

/**
 * Enqueue editor styles.
 */
function editor_styles() {
	// Enqueue editor styles.
	add_editor_style(
		array(
			fonts_url(),
			'/assets/ponyfill.css'
		)
	);
}

/**
 * Add Google webfonts.
 *
 * @return $fonts_url
 */
function fonts_url() {
	if ( ! class_exists( '\WP_Theme_JSON_Resolver_Gutenberg' ) ) {
		return '';
	}

	$theme_data = \WP_Theme_JSON_Resolver_Gutenberg::get_merged_data()->get_settings();
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
