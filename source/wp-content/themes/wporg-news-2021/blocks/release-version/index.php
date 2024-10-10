<?php

namespace WordPressdotorg\Theme\News_2021\Blocks\Release_Version;

use WP_Block;

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

	$post_ID            = $block->context['postId'];
	$align_class_name   = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";
	$show_major_version = $attributes['showMajorVersion'];

	$version = '';
	$title = get_the_title( $post_ID );

	if ( preg_match( '/WordPress (\d{0,3}(?:\.\d{1,3})+)\s*(?|Release Candidate\s*(\d+)|RC\s*(\d+))?(?|Beta\s*(\d+))?/', $title, $matches ) ) {
		$version = $matches[1];
		if ( ! empty( $matches[2] ) ) {
			if ( $show_major_version ) {
				$version .= ' RC' . $matches[2];
			} else {
				$version = 'RC' . $matches[2];
			}
		}
		if ( ! empty( $matches[3] ) ) {
			if ( $show_major_version ) {
				$version .= ' Beta' . $matches[3];
			} else {
				$version = 'Beta' . $matches[3];
			}
		}
	}

	$wrapper_tag   = $attributes['tagName'] ?? 'div';
	$wrapper_open  = "<$wrapper_tag " . get_block_wrapper_attributes( array( 'class' => $align_class_name ) ) . '>';
	$link_open     = empty( $attributes['isLink'] ) ? '' : '<a href="' . get_permalink( $post_ID ) . '">';
	$link_close    = empty( $attributes['isLink'] ) ? '' : '</a>';
	$wrapper_close = "</$wrapper_tag>";

	return "$wrapper_open $link_open $version $link_close $wrapper_close";
}

/**
 * Registers the `wporg/release-version` block on the server.
 */
function register_block() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => __NAMESPACE__ . '\render_block',
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
