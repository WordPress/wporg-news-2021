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

	$date = date_create_from_format( 'Y-m-d H:i:s', get_the_date( 'Y-m-00 00:00:00', $post_id ) );

	$short_month = $date->format( 'M' );
	$long_month = $date->format( 'F' );
	$year = $date->format( 'Y' );

	$wrapper_attributes = get_block_wrapper_attributes();

	if ( isset( $attributes['isLink'] ) && $attributes['isLink'] ) {
		return sprintf(
			'<%1$s %2$s><a href="%3$s"><span aria-label="%4$s">%5$s</span> %6$s</a></%1$s>',
			$tag_name,
			$wrapper_attributes,
			get_permalink( $post_id ),
			$long_month,
			$short_month,
			$year
		);
	}

	return sprintf(
		'<%1$s %2$s><span aria-label="%3$s">%4$s</span> %5$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$long_month,
		$short_month,
		$year
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
