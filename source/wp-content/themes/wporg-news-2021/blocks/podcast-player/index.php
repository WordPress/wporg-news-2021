<?php

namespace WordPressdotorg\Theme\News_2021\Blocks\Podcast_Player;

add_action( 'init', __NAMESPACE__ . '\register_block' );
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\register_block_type_js' );

/**
 * Renders the `wporg/podcast-player` block on the server.
 *
 * This block is just a proxy on top of the Seriously Simple Podcast player, because that player requires
 * a set post ID, while we want to always use the current post ID.
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

	$post_id = $block->context['postId'];
	$output = do_blocks( '<!-- wp:seriously-simple-podcasting/castos-html-player {"episodeId":"' . $post_id . '"} /-->' );

	$show_meta = isset( $attributes['showMeta'] ) && $attributes['showMeta'];

	if ( $show_meta ) {
		// Building this by hand rather than using `episode_meta_details()` to avoid confusing `title` attributes on links.
		$ssp_controller = ssp_frontend_controller();

		$download_url   = $ssp_controller->get_episode_download_link( $post_id );
		$new_window_url = add_query_arg( 'ref', 'new_window', $download_url );

		$download_link   = '<a href="' . esc_url( $download_url ) . '" class="podcast-meta-download">' . __( 'Download file', 'wporg' ) . '</a>';
		$new_window_link = '<a href="' . esc_url( $new_window_url ) . '" target="_blank" class="podcast-meta-new-window">' . __( 'Play in new window', 'wporg' ) . '</a>';

		$duration      = get_post_meta( $post_id, 'duration', true );
		/* translators: %s duration of podcast episode. */
		$duration_text = '<span class="podcast-meta-duration">' . sprintf( __( 'Duration: %s', 'wporg' ), esc_html( $duration ) ) . '</span>';

		$output .= sprintf(
			'<p class="podcast-player-meta">%s | %s | %s</p>',
			$download_link,
			$new_window_link,
			$duration_text
		);
	}

	$wrapper_attributes = get_block_wrapper_attributes();

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$output
	);
}

/**
 * Registers the `wporg/podcast-player` block on the server.
 */
function register_block() {
	register_block_type(
		__DIR__,
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
