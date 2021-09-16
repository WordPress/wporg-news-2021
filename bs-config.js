// See http://www.browsersync.io/docs/options/

const paths = {
	theme: 'source/wp-content/themes/wporg-news-2021',
	globalHeaderFooter: 'source/wp-content/mu-plugins/wporg-mu-plugins/mu-plugins/blocks/global-header-footer'
};

// Any file of these types in any subdirectory
const watchedFilesPattern = '/**/*.(php|js|html|css|svg|png)';

module.exports = {
	/*
	 * This is disabled so that BrowserSync can run on any URL, which is needed to support multiple development
	 * environments.
	 */
	 'snippet': false,

	// Avoid conflicts with other running tasks.
	// Can't be auto-detected because has to match injected port in `0-sandbox.php`.
	'port': 3008,

	"files": [
		paths.theme + '/theme.json',
		paths.theme + watchedFilesPattern,
		paths.globalHeaderFooter + watchedFilesPattern
	],

	"ignore": [
		paths.theme + '/sass/**/*.*',
		paths.globalHeaderFooter + '/postcss/**/*.*'
	],

	"open": false,
	"reloadOnRestart": true,
	"notify": false,
};
