<?php

namespace WordPressdotorg\Theme\News_2021\Blocks\PostTypeName;

add_action( 'init', __NAMESPACE__ . '\register_block' );
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\register_block_type_js' );

/**
 * Renders the `wporg/post-type-name` block on the server.
 *
 * The block render the post type name. It also optionally renders it as a link.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string
 */
function render_block( $attributes, $content, $block ) {
	if ( ! isset( $block->context['postId'] ) ) {
		return '';
	}

	$post_id  = $block->context['postId'];
	$post_type = get_post_type( $post_id  );
	$post_type_obj = get_post_type_object( $post_type );
	$wrapper_attributes = get_block_wrapper_attributes();

	if ( isset( $attributes['isLink'] ) && $attributes['isLink'] ) {
		return sprintf(
			'<a %1$s href="%2$s">%3$s</a>',
			$wrapper_attributes,
			get_post_type_archive_link( $post_type ),
			$post_type_obj->label,
		);
	}

	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$attributes['tagName'],
		$wrapper_attributes,
		$post_type_obj->label
	);
}

/**
 * Registers the `wporg/post-type-name` block on the server.
 */
function register_block() {
	register_block_type(
		'wporg/post-type-name',
		array(
			'title'           =>  __( 'Post Type Name', 'wporg' ),
			'render_callback' => __NAMESPACE__ . '\render_block',
			'uses_context'    => [ 'postId' ],
			'attributes'	=> [
				'tagName' => [
					'default' => 'span',
					'type' => 'string'
				]
			]
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
