<?php

namespace WordPressdotorg\Theme\News_2021\Blocks\Month_In_WordPress;

add_action( 'init', __NAMESPACE__ . '\register_block' );

/**
 * Renders the `wporg/month-in-wp-title` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the release version for the current post wrapped inside "h1" tags.
 */
function render_block_wporg_month_in_wp_title( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$post_id  = $block->context['postId'];
	$tag_name = 'h2';

	if ( isset( $attributes['level'] ) ) {
		$tag_name = 0 === $attributes['level'] ? 'p' : 'h' . $attributes['level'];
	}

	$label = get_the_date( 'F Y', $post_id );
	$visual_label = get_the_date( 'M', $post_id );

	$wrapper_attributes = get_block_wrapper_attributes();

	if ( isset( $attributes['isLink'] ) && $attributes['isLink'] ) {
		return sprintf(
			'<%1$s %2$s><a href="%3$s" aria-label="%4$s">%5$s</a></%1$s>',
			$tag_name,
			$wrapper_attributes,
			get_permalink( $post_id ),
			$label,
			$visual_label
		);
	}

	return sprintf(
		'<%1$s %2$s aria-label="%3$s">%4$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$label,
		$visual_label
	);
}

/**
 * Registers the `wporg/month-in-wp-title` block on the server.
 */
function register_block() {
	register_block_type(
		'wporg/month-in-wp-title',
		array(
			'title'           => 'Month in WordPress Title',
			'render_callback' => __NAMESPACE__ . '\render_block_wporg_month_in_wp_title',
			'uses_context'    => [ 'postId', 'postType' ],
		)
	);
}
