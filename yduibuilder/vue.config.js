const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin')
// console.log('test')
module.exports = {
  configureWebpack: {
    plugins: [
      new MonacoWebpackPlugin()
    ]
  }
}
