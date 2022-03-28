import { Log } from 'nice-logs';
import loadJS from './js/loadJS';

window.loadJS = loadJS;
// window.lazySizesConfig = window.lazySizesConfig || {};
// window.lazySizesConfig.lazyClass = 'lazy';
// window.lazySizesConfig.loadedClass = 'is-loaded';
// window.lazySizesConfig.init = false;


const features = [];
let loadWebPPolyfill = false;

// Include intersection observer polyfill if needed.
// Primarily for IE 11.
if (
  !'IntersectionObserver' in window &&
  !'IntersectionObserverEntry' in window &&
  !'intersectionRatio' in window.IntersectionObserverEntry.prototype
) {
  features.push( 'es2015' );
  features.push( 'IntersectionObserver' );
  features.push( 'IntersectionObserverEntry' );
  features.push( 'URLSearchParams' );
  window.legacyBrowser = true;
}

if( !! features.length ) {
  loadJS( `https://cdn.polyfill.io/v3/polyfill.min.js?features=${features.join( ',' )}&flags=gated,always&ua=chrome/87`, function() {
    console.warn( 'Polyfills loaded for IE 11' );
  } );
}


if( process.env.NODE_ENV === 'development' ) {
  require( 'panic-overlay' );
}


/**
 * Put any scripts you need to run or include before all others here.
 */

console.log( `%c

                   ###
                 #######
                #########
               ###########
              #############
               ###########
            #  ###########  #
            #################
            #  ###########  #
  #####       #############       #####
 #######    #################    #######
 #######   ###################   #######
#########################################
#########################################
 #######   ###################   #######
 #######    #################    #######
  #####        ###########        #####
             ###############
             ###############
                 #######
               ###########
              #############
              #############
               ###########
                 ## # ##
                  #   # 

                  SHINY!

`, 'color:blue'  );


// Fancy dev logs.
Log.timeStampEnabled = false;
const emptyFunc = () => {};
const devlog = process.env.NODE_ENV === 'development' ? Log : {
  title:    emptyFunc,
  info:     emptyFunc,
  warn:     emptyFunc,
  error:    emptyFunc,
  success:  emptyFunc,
}
window.devlog = devlog;