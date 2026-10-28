<?php
/**
 * Title: Podcast social links
 * Slug: wporg-news-2021/podcast-social-links
 * Inserter: no
 *
 * Links to the podcast on listening services and to the site's own podcast feed.
 *
 * @package WordPressdotorg\Theme\News_2021
 */

declare( strict_types = 1 );

?>
<!-- wp:social-links {"iconColor":"blue-1","iconColorValue":"#3e58e1","className":"is-style-logos-only"} -->
<ul class="wp-block-social-links has-icon-color is-style-logos-only">
	<!-- wp:social-link {"url":"https://podcasts.apple.com/us/podcast/wp-briefing/id1551691710","service":"chain","label":"Apple Podcasts"} /-->
	<!-- wp:social-link {"url":"https://pca.st/uqvvmt8t","service":"chain","label":"Pocket Casts"} /-->
	<!-- wp:social-link {"url":"<?php echo esc_url( get_feed_link( 'podcast' ) ); ?>","service":"feed","label":"RSS"} /-->
	<!-- wp:social-link {"url":"https://open.spotify.com/show/6BxgmE9Qg2TZA8EKZLJ4zS","service":"spotify","label":"Spotify"} /-->
</ul>
<!-- /wp:social-links -->
