<?php

namespace WordPressdotorg\Theme\News_2021\Blocks\Release_Version;

add_action( 'init', __NAMESPACE__ . '\register_block' );
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\register_block_type_js' );

/**
 * Renders the `wporg/release-version` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the release version for the current post wrapped inside "h1" tags.
 */
function render_block( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$post_ID          = $block->context['postId'];
	$align_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";

	$version = '';
	$title = get_the_title( $post_ID );
	// Do we also want x.y.z?
	if ( preg_match( '/WordPress (\d{0,3}(?:\.\d{1,3})+)\s*(?|Release Candidate\s*(\d+)|RC\s*(\d+))?/', $title, $matches ) ) {
		$version = $matches[1];
		if ( ! empty( $matches[2] ) ) {
			$version = 'RC' . $matches[2];
		}
	}

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $align_class_name ) );

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$version
	);
}

/**
 * Registers the `wporg/release-version` block on the server.
 */
function register_block() {
	register_block_type(
		'wporg/release-version',
		array(
			'title'           => 'WordPress.org Release Version',
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
