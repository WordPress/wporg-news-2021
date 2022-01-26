<?php
/**
 * Plugin Name:       Wporg Post Title Block
 * Description:       Post title block with custom attributes needed by WordPress.org.
 * Author:            WordPress.org
 * Author URI:        https://wordpress.org/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wporg
 */

/**
 * Server-side rendering of the `wporg/post-title` block.
 */

/**
 * Renders the `wporg/post-title` block on the server.
 * Copied from core post-title.php and modified.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the filtered post title for the current post wrapped inside "h1" tags.
 */
function render_block_wporg_post_title( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$post_ID          = $block->context['postId'];
	$tag_name         = 'h2';
	$align_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";

	if ( isset( $attributes['level'] ) ) {
		$tag_name = 0 === $attributes['level'] ? 'p' : 'h' . $attributes['level'];
	}

	if ( isset( $attributes['releaseVersion'] ) ) {
		$title = get_the_title( $post_ID );
		// Do we also want x.y.z?
		if ( preg_match( '/WordPress (\d+[.]\d+)/', $title, $matches ) ) {
			$title = $matches[1];
		} else {
			// Empty title if no match.
			$title = '';
		}
	} else {
		$title = get_the_title( $post_ID );
	}
	if ( isset( $attributes['isLink'] ) && $attributes['isLink'] ) {
		$title = sprintf( '<a href="%1s" target="%2s" rel="%3s">%4s</a>', get_the_permalink( $post_ID ), $attributes['linkTarget'], $attributes['rel'], $title );
	}
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $align_class_name ) );

	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$title
	);
}

/**
 * Registers the `wporg/post-title` block on the server.
 */
function register_block_wporg_post_title() {
	register_block_type_from_metadata(
		__DIR__,
		array(
			'render_callback' => 'render_block_wporg_post_title',
		)
	);
}
add_action( 'init', 'register_block_wporg_post_title' );
