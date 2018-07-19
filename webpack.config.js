let Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .addEntry('js/app', './assets/js/global.js')
    .addEntry('js/search', './assets/js/search.js')
    .addEntry('js/rating', './assets/js/rating.js')
    .addEntry('js/addToCart', './assets/js/addToCart.js')
    .addStyleEntry('css/app', './assets/css/global.css')
    .addStyleEntry('css/404', './assets/css/404.css')
    .addStyleEntry('css/rating', './assets/css/rating.css')
    .addStyleEntry('css/account', './assets/css/account.css')
    .addStyleEntry('css/product', './assets/css/product.css')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    // .addEntry('js/app', './assets/js/app.js')
    // .addStyleEntry('css/app', './assets/css/app.scss')

    // uncomment if you use Sass/SCSS files
    // .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    // .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
