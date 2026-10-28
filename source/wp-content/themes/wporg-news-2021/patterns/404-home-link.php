<?php
/**
 * Title: 404 homepage link
 * Slug: wporg-news-2021/404-home-link
 * Inserter: no
 *
 * Points visitors on the 404 page back to the homepage of the current site.
 *
 * @package WordPressdotorg\Theme\News_2021
 */

declare( strict_types = 1 );

?>
<!-- wp:paragraph -->
<p>
	<?php
	printf(
		/* translators: %s: URL of the News homepage. */
		wp_kses_post( __( 'Go to <a href="%s">the homepage</a>, or try searching News posts using the field below.', 'wporg' ) ),
		esc_url( home_url( '/' ) )
	);
	?>
</p>
<!-- /wp:paragraph -->
