/**
 * This emulates the build command in v1.1 of Blockbase, but in a way that doesn't require providing a path to
 * the node-sass-package-importer package.
 *
 * Original command:
 * node-sass --importer node_modules/node-sass-package-importer/dist/cli.js sass/ponyfill.scss assets/ponyfill.css --output-style expanded --indent-type tab --indent-width 1 --source-map true
 */

/**
 * External dependencies.
 */
const fs = require( 'fs' );
const { resolve } = require( 'path' );
const sass = require( 'node-sass' );
const packageImporter = require( 'node-sass-package-importer' );

const inputFile = resolve( 'sass/ponyfill.scss' );
const outputFile = resolve( 'assets/ponyfill.css' );

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
		fs.writeFile(
			outputFile,
			result.css,
			( err ) => {
				if ( err ) {
					console.log( err );
				}
			}
		);
	}
} );
