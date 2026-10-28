/**
 * WordPress dependencies
 */
const config = require( '@wordpress/scripts/config/webpack.config' );

/**
 * External dependencies
 */
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );

// Leave `url()` paths as written, so they point at the theme's `images/` folder instead of copies in `build/`.
for ( const rule of config.module.rules ) {
	for ( const loader of rule.use ?? [] ) {
		if ( loader.loader === require.resolve( 'css-loader' ) ) {
			loader.options.url = false;
		}
	}
}

module.exports = {
	...config,
	entry: {
		style: './sass/main.scss',
	},
	plugins: [
		// The site is LTR only, so skip the RTL stylesheet.
		...config.plugins.filter(
			( plugin ) => plugin.constructor.name !== 'RtlCssPlugin'
		),
		// Drop the empty JS file webpack emits for the CSS-only entry.
		new RemoveEmptyScriptsPlugin(),
	],
};
