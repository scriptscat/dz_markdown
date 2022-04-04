const path = require('path');

module.exports = {
    entry: {
        editor: './src/editor.js',
        viewer: './src/viewer.js'
    },
    output: {
        path: __dirname + "/dist",
        filename: "[name].js",
        clean: true,
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ["style-loader", "css-loader"],
            }
        ]
    }
};