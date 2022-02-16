<?php

namespace WordPressdotorg\Theme\News_2021\Blocks\Event_Year;

add_action( 'init', __NAMESPACE__ . '\register_block' );
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\register_block_type_js' );

/**
 * Renders the `wporg/event-year` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the event year for the current post.
 */
function render_block( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$post_ID          = $block->context['postId'];
	$align_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";

	$year = '';
	$post_year = get_the_date( 'Y', $post_ID );

	/** @var \WP_Query $wp_query */
	global $wp_query;
	$current_page_post_dates = wp_list_pluck( $wp_query->posts, 'post_date', 'ID' );
	array_reduce(
		$wp_query->posts,
		function( $carry, $item ) {
			$year = get_the_date( 'Y', $item->ID );

			if ( ! isset( $carry[ $year ] ) ) {
				$carry[ $year ] = array();
			}


		},
		array()
	);


	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $align_class_name ) );

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$year
	);
}


function get_current_post_ids_sorted_by_year() {
	/** @var \WP_Query $wp_query */
	global $wp_query;

	array_reduce(
		$wp_query->posts,
		function( $carry, $item ) {
			$year = get_the_date( 'Y', $item->ID );

			if ( ! isset( $carry[ $year ] ) ) {
				$carry[ $year ] = array();
			}


		},
		array()
	);
}

/**
 * Registers the `wporg/event-year` block on the server.
 */
function register_block() {
	register_block_type(
		'wporg/event-year',
		array(
			'title'           => 'WordPress.org Event Year',
			'render_callback' => __NAMESPACE__ . '\render_block',
			'uses_context'    => [ 'postId' ],
		)
	);
}

/**
 * Register block type in JS, for the editor.
 */
function register_block_type_js() {
	$block = wp_json_file_decode( __DIR__ . '/block.json' );
	ob_start();
	?>
	( function( wp ) {
		wp.blocks.registerBlockType(
			'<?php echo esc_js( $block->name ); ?>',
			{
				title: '<?php echo esc_js( $block->title ); ?>',
				edit: function( props ) {
					return wp.element.createElement( wp.serverSideRender, {
						block: '<?php echo esc_js( $block->name ); ?>',
						attributes: props.attributes
					} );
				},
			}
		);
	}( window.wp ));
	<?php
	wp_add_inline_script( 'wp-editor', ob_get_clean(), 'after' );
}
