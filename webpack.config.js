const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        'admin/index': './admin/src/index.tsx',
    },
    output: {
        ...defaultConfig.output,
        path: __dirname + '/admin/build',
    },
};
