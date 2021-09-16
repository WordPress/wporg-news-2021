<?php

/*
 * These are stubs for closed source code, or things that only apply to local environments.
 */

namespace WordPress_org\News_2021\Stubs;

defined( 'WPINC' ) || die();

require_once WPMU_PLUGIN_DIR . '/wporg-mu-plugins/mu-plugins/blocks/global-header-footer/blocks.php';

/*
 * Inject BrowserSync's watcher script, to inject changed files or reload the page.
 */
add_action( 'wp_print_footer_scripts', function() {
	?>

	<script id="__bs_script__">//<![CDATA[
		// Port must match the one in `bs-config.js`.
		document.write( `<script async src=\'http://${location.hostname}:3008/browser-sync/browser-sync-client.js?v=2.27.5\'><\/script>` );
		//]]>
	</script>

	<?php
}, 99 ); // Add as late as possible, since BrowserSync expects to be right before `</body>`.
