var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the 'encore' command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // When enabled, Webpack 'splits' your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(true)

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .enableSassLoader()
    //.enableLessLoader()
    .enableTypeScriptLoader()

    // uncomment to get integrity='...' attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')


    .copyFiles({
        from: './node_modules/@fortawesome/fontawesome-free/webfonts/',
        to: '/fonts/[path][name].[ext]'
    })
    /*
     * Commented here because leaflet-draw uses the same base images as Leaflet.
     * If it ever changes, feel free to check it out and uncomment this ;)
    .copyFiles({
        from: './node_modules/leaflet/dist/images/',
        to: '/images/[path][name].[ext]'
    })
     */
    .copyFiles({
        from: './node_modules/leaflet-draw/dist/images/',
        to: '/images/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/esteren/fonts/',
        to: '/fonts/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/esteren_maps/img/markerstypes/',
        to: '/markerstypes/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/agate/images/',
        to: '/agate/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/esteren/images/',
        to: '/[path][name].[ext]'
    })


    .addStyleEntry('style_global', './assets/esteren/sass/main.scss')
    .addStyleEntry('agate', './assets/agate/sass/agate-theme.scss')
    .addStyleEntry('white_layout', './assets/esteren/sass/white_layout.scss')
    .addStyleEntry('initializer', './assets/esteren/css/initializer.css')
    .addStyleEntry('fa', './node_modules/@fortawesome/fontawesome-free/css/all.css')
    .addStyleEntry('maps_styles', './assets/esteren_maps/less/maps.scss')


    .addEntry('global', [
        './node_modules/@materializecss/materialize/dist/js/materialize.js',
        './assets/esteren/js/helpers.js',
        './assets/esteren/js/global.js'
    ])
    .addEntry('maps', [
        './node_modules/leaflet/dist/leaflet-src.js',
        './node_modules/leaflet-draw/dist/leaflet.draw-src.js',
        './assets/esteren_maps/js/EsterenMap/1_EsterenMap.js',
        './assets/esteren_maps/js/EsterenMap/1_EsterenMap.load.js',
        './assets/esteren_maps/js/EsterenMap/2_EsterenMap_CRS_XY.js',
        './assets/esteren_maps/js/EsterenMap/2_EsterenMap_directions.js',
        './assets/esteren_maps/js/EsterenMap/2_EsterenMap_LatLngBounds.js',
        './assets/esteren_maps/js/EsterenMap/2_EsterenMap_Sidebar.js',
        './assets/esteren_maps/js/EsterenMap/3_EsterenMap_ActivateLeafletDraw.js',
        './assets/esteren_maps/js/EsterenMap/3_EsterenMap_options.js',
        './assets/esteren_maps/js/EsterenMap/4_EsterenMap_markers.js',
        './assets/esteren_maps/js/EsterenMap/4_EsterenMap_polygons.js',
        './assets/esteren_maps/js/EsterenMap/4_EsterenMap_polylines.js',
        './assets/esteren_maps/js/EsterenMap/4_EsterenMap_filters.js',
        './assets/esteren_maps/js/EsterenMap/4_EsterenMap_search_engine.js',
        './assets/esteren_maps/js/EsterenMap/5_EsterenMap_mapEdit.js',
        './assets/esteren_maps/js/EsterenMap/5_EsterenMap_mapNotes.js'
    ])
;

module.exports = Encore.getWebpackConfig();
