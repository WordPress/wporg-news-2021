/**
 * This emulates the build command in v1.1 of Blockbase, but in a way that doesn't require providing a path to
 * the node-sass-package-importer package.
 *
 * Original command:
 * node-sass --importer node_modules/node-sass-package-importer/dist/cli.js sass/style.scss assets/style.css --output-style expanded --indent-type tab --indent-width 1 --source-map true
 */

/**
 * External dependencies.
 */
const fs = require( 'fs' );
const { resolve } = require( 'path' );
const sass = require( 'node-sass' );
const packageImporter = require( 'node-sass-package-importer' );
const autoprefixer = require('autoprefixer');
const postcss = require('postcss');

const inputFile = resolve( 'sass/style.scss' );
const outputFile = resolve( './style.css' );

sass.render( {
	file: inputFile,
	outFile: outputFile,
	outputStyle: 'expanded',
	indentType: 'tab',
	indentWidth: 1,
	sourceMap: true,
	importer: packageImporter(),
}, ( error, result ) => {
	if ( error ) {
		console.log( error.message );
	} else {
		let css = result.css;
		postcss([ autoprefixer ]).process(css, { from: outputFile }).then(res => {
			res.warnings().forEach(warn => {
				console.warn(warn.toString());
			})
			fs.writeFile(
				outputFile,
				res.css,
				( err ) => {
					if ( err ) {
						console.log( err );
					}
				}
			);
		});
	}
} );


