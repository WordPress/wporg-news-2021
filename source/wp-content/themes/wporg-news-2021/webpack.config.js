/**
 * WordPress dependencies
 */
const config = require( '@wordpress/scripts/config/webpack.config' );

/**
 * External dependencies
 */
const path = require( 'path' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );

const cssLoader = require.resolve( 'css-loader' );

module.exports = {
	...config,
	entry: {
		style: path.resolve( __dirname, 'sass/main.scss' ),
	},
	module: {
		...config.module,
		rules: config.module.rules.map( ( rule ) => {
			if ( ! Array.isArray( rule.use ) ) {
				return rule;
			}

			// Leave `url()` paths as written, so they point at the theme's `images/` folder instead of copies in `build/`.
			return {
				...rule,
				use: rule.use.map( ( loader ) =>
					loader.loader === cssLoader
						? {
								...loader,
								options: { ...loader.options, url: false },
							}
						: loader
				),
			};
		} ),
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
