const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	resolve: {
		...defaultConfig.resolve,
		fullySpecified: false,
	},
	entry: {
		'index': path.resolve( __dirname, 'assets/src/index.js' ),
	},
	output: {
		path: path.resolve(__dirname, 'assets/build'),
		filename: '[name].js',
	},
};
