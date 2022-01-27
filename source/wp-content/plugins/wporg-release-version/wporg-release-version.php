<?php
/**
 * Plugin Name:       Wporg Release Version Block
 * Description:       Displays the WP release version for a News post.
 * Author:            WordPress.org
 * Author URI:        https://wordpress.org/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wporg
 */

/**
 * Server-side rendering of the `wporg/release-version` block.
 */

/**
 * Renders the `wporg/release-version` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the release version for the current post wrapped inside "h1" tags.
 */
function render_block_wporg_release_version( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$post_ID          = $block->context['postId'];
	$tag_name         = 'h2';
	$align_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";

	if ( isset( $attributes['level'] ) ) {
		$tag_name = 0 === $attributes['level'] ? 'p' : 'h' . $attributes['level'];
	}

	$version = '';
	$title = get_the_title( $post_ID );
	// Do we also want x.y.z?
	if ( preg_match( '/WordPress (\d+[.]\d+)/', $title, $matches ) ) {
		$version = $matches[1];
	}
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $align_class_name ) );

	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$version
	);
}

/**
 * Registers the `wporg/release-version` block on the server.
 */
function register_block_wporg_release_version() {
	register_block_type_from_metadata(
		__DIR__,
		array(
			'render_callback' => 'render_block_wporg_release_version',
		)
	);
}
add_action( 'init', 'register_block_wporg_release_version' );
