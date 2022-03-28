const autoprefixer =  require( 'autoprefixer' );
const browserSync =   require( 'browser-sync' ).create( 'shiny' );
const cssnano =       require( 'cssnano' );
const del =           require( 'del' );
const extReplace =    require( 'gulp-ext-replace' );
const magicImporter = require( 'node-sass-magic-importer' );
const header =        require( 'gulp-header' );
const gulp =          require( 'gulp' );
const gulpif =        require( 'gulp-if' );
const gutil =         require( 'gulp-util' );
const imagemin =      require( 'gulp-imagemin' );
const imageminWebP =  require( 'imagemin-webp' );
const lazypipe =      require( 'lazypipe' );
const log =           require( 'fancy-log' );
const named =         require( 'vinyl-named' );
const notify =        require( 'gulp-notify' );
const tap =           require( 'gulp-tap' );
const plumber =       require( 'gulp-plumber' );
const postcss =       require( 'gulp-postcss' );
// const postcssImport = require( 'postcss-import' );
// const postcssNested = require( 'postcss-nested' );
//const reporter =      require( 'postcss-reporter' );
const sass =          require( 'gulp-sass' )( require( 'node-sass' ) );
const rename =        require( 'gulp-rename' );
const styleLint =     require( 'gulp-stylelint' );
const sourcemaps =    require( 'gulp-sourcemaps' );
const webpackStream = require( 'webpack-stream' );
const webpackConfig = require( './webpack.config.js' );

const siteConfig =    require( './config' );
const siteRoot = siteConfig.LOCAL_DIST_URL.split( '/wp-content' )[0];

/**
 * Settings.
 */
const settings = {
  localSite:      siteRoot,
  scssDirs:       './src/**/!(_)*.scss',
  scssWatchDirs:  './src/**/*.scss',
  jsDirs:         './src/**/!(*.min)*.js*',
  imgDirs:        './src/img/*',
  fontDirs:       './src/fonts/**/*.woff*',
}

/**
*   Error Reporting
*/
function reportError( err ) {

  const plugin = ! err.plugin && err instanceof Error ? 'webpack-stream' : err.plugin;
  const errorString = plugin === 'webpack-stream' ? err.toString() : null;

  // const file = plugin === 'webpack-stream' ? errorString.match( /(.\/.*.js)/ )[0] : err.file || err.relativePath;
  // const line = plugin === 'webpack-stream' ? errorString.match( /(\s\d.*:.*)/ ) : `${err.line}:${err.column}`;

  // notify( {
  //   title: `Task Failed`,
  //   message: `Line ${line} in ${file}`,
  //   sound: 'Sosumi', // See: https://github.com/mikaelbr/node-notifier#all-notification-options-with-their-defaults
  //   //wait: true,
  //   timeout: 15,
  // } ).write( err );


  // Pretty error reporting
  const chalk = gutil.colors.bold.red;
  const report = chalk( `${err.messageFormatted || err.message || err}\n` );

  console.error( report );

  // Prevent the 'watch' task from stopping
  this.emit( 'end' );

}

/**
 * Run browser sync.
 */
const runBrowserSync = done => {

  browserSync.init( {
    proxy: settings.localSite,
    notify: true,
    open: false,
  } );

  // setTimeout( () => done(), 500 );

}


/**
 * Process scss file.
 */
const processScssFile = inputStream => {

  const env = process.env.NODE_ENV;

  return inputStream
    .pipe( plumber( { errorHandler: reportError } ) )
    .on( 'error', reportError )
    .pipe( gulpif( env === 'development', sourcemaps.init() ) )
    .pipe( header( `
      $env_node_env: '${process.env.NODE_ENV}';
      $dist_dir: '${process.env.NODE_ENV === 'development' ? `${siteConfig.DEV_DIST_URL}` : `${siteConfig.PUBLIC_DIST_URL}` }';
      $fonts_dir: '${process.env.NODE_ENV === 'development' ? `${siteConfig.DEV_DIST_URL}/fonts` : `${siteConfig.PUBLIC_DIST_URL}/fonts` }';
    ` ) )
    .pipe( sass( {
      importer: magicImporter(),
      includePaths: [
        'node_modules',
        'src',
      ]
    } ) )
    .pipe( tap( ( file, t ) => {

      if( ! file.path.includes( 'admin' ) ) {
        return t.through( postcss, [] )
      }
    } ) )
    .pipe( gulpif( env === 'development', sourcemaps.write( '.' ) ) )
    .pipe( rename( path => {
      path.dirname = '';
    } ) )
    .pipe( gulp.dest( 'dist/css') )
    .pipe( gulpif( env === 'development', browserSync.reload( { stream: true } ) ) );

}


/**
 * Process js file.
 */
const processJSFile = inputStream => {

  const env = process.env.NODE_ENV;

  return inputStream
    .pipe( plumber( { errorHandler: reportError } ) )
    .on( 'error', reportError )
    .pipe( named() )
    .pipe( webpackStream( webpackConfig ) )
    .pipe( gulp.dest( 'dist/js' ) )
    .pipe( gulpif( env === 'development', browserSync.reload( { stream: true } ) ) );
}

/**
 * Process image file.
 */
const processImageFile = inputStream => {

  return inputStream
    .pipe( imagemin( [
      imagemin.gifsicle( { interlaced: true } ),
      imagemin.mozjpeg( { quality: 80, progressive: true } ),
      imagemin.optipng( { optimizationLevel: 5 } ),
      imagemin.svgo( {
        plugins: [
          { removeViewBox: true },
          { cleanupIDs: false }
        ]
      } ),
    ], {
      verbose: true,
    } ) )
    .pipe( gulp.dest( 'dist/img' ) )
}

/**
 * Convert to webp.
 */
const convertToWebP = path => {

  log( 'Converting to WebP...' );

  gulp.src( path )
    .pipe( imagemin( [
      imageminWebP( {
        quality: 75
      } )
    ], { verbose: true } ) )
    .pipe( extReplace( '.webp' ) )
    .pipe( gulp.dest( 'dist/img' ) );
}

/**
 * Process font file.
 */

const processFontFile = inputStream => {

const env = process.env.NODE_ENV;

return inputStream
  .pipe( rename( path => {
    path.basename = 'webfont';
  } ) )
  .pipe( gulp.dest( 'dist/fonts' ) )
  .pipe( gulpif( env === 'development', browserSync.reload( { stream: true } ) ) );
}

/* ------ = Tasks. = --------------------------------------------------------------------- */


/**
 * Browser sync.
 */
gulp.task( 'browserSync', runBrowserSync );

/**
 * Sass task.
 */
gulp.task( 'sass', () => processScssFile( gulp.src( settings.scssDirs ) ) );

/**
 * JS.
 */
gulp.task( 'js', () => processJSFile( gulp.src( settings.jsDirs ) ) );

/**
 * Img.
 */
gulp.task( 'removeAllImages', () => del( 'dist/img/*' ) );
gulp.task( 'processImages', () => processImageFile( gulp.src( settings.imgDirs ) ) );
//gulp.task( 'convertImages', async () => convertToWebP( 'dist/img/!(*.svg)' ) );
gulp.task( 'img', gulp.series( 'removeAllImages', 'processImages' ) );

/**
 * Fonts.
 */
gulp.task( 'fonts', () => processFontFile( gulp.src( settings.fontDirs ) ) );

/**
 *  ಠ_ರೃ
 */
gulp.task( 'watch', done => {

  if( ! browserSync.active ) {
    runBrowserSync();
  }

  // Scss
  gulp.watch( settings.scssWatchDirs, gulp.parallel( 'sass' ) );

  // JS.
  gulp.watch( settings.jsDirs, gulp.parallel( 'js' ) );

  // Images.
  gulp.watch( settings.imgDirs ).on( 'all', ( event, path ) => {

    const filename = path.substring( path.lastIndexOf( '/' ) + 1 );

    console.log( event );

    // how to prevent this from running twice?
    if( event == 'add' || event == 'change' ) {
      processImageFile( gulp.src( path ) );
    }
    else if( event == 'unlink' ) {
      del( `dist/img/${filename}` );
      del( `dist/img/${filename.replace( '.jpg', ''  ).replace( 'jpeg' ).replace( '.png', '' )}.webp` );
    }

  } );

  // Webp.
  gulp.watch( 'dist/img/*' ).on( 'all', ( event, path ) => {

    const filename = path.substring( path.lastIndexOf( '/' ) + 1 );

    // how to prevent this from running twice?
    // if( ! path.includes( '.webp' ) && ! path.includes( 'svg' ) && ( event == 'add' || event == 'change' ) ) {
    //   convertToWebP( path );
    // }

  } );

  // PHP & config.
  gulp.watch( [ '*.php', '**/*.php', '*.json' ] ).on( 'change', browserSync.reload );

  done();

} );

/**
 * Default.
 */
gulp.task( 'default', gulp.series( 'watch', [ gulp.parallel( 'sass', 'js', 'img' ) ] ) );

/**
 * Build.
 */
gulp.task( 'build', gulp.series( gulp.parallel( 'sass', 'js', 'img', 'fonts' ) ) );
