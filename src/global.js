import { Log } from 'nice-logs';
import { format, utcToZonedTime } from 'date-fns-tz';
import Cookies from 'js-cookie';
window.Cookies = Cookies;
import { prefetchLinks } from './js/helpers';
import lozad from 'lozad';
// If needed.
 //import Masker from 'vanilla-masker';
// import 'unfetch/polyfill';
// window.FPConfig = {
//   ignoreKeywords: ['cart', 'checkout' ]
// }
// import flyingPages from './js/flying-pages';


const dist = WP_SETTINGS.IN_DEVMODE ? WP_SETTINGS.LOCAL_DIST_URL : WP_SETTINGS.PUBLIC_DIST_URL;

// Check it out!
if( typeof devlog !== 'undefined' ) {
  devlog.info( 'Here are your settings: \n' , WP_SETTINGS );
}

if( !! WP_SETTINGS ) {
  for( let [ key, value ] of Object.entries( WP_SETTINGS ) ) {
    window[key] = value;
  }
}

const lazy = lozad( '.lazy', {
  rootMargin: '300px 0px',
  loaded: el => {
    el.classList.add( 'is-loaded' )
  }
} );
lazy.observe();


// Polyfills
const isSafari = navigator.userAgent.indexOf( 'Safari' ) != -1 && navigator.userAgent.indexOf( 'Chrome' ) == -1;
if( isSafari || window.legacyBrowser ) {

 const loadJS = require( './js/loadJS' );
 loadJS( 'https://unpkg.com/webp-hero@0.0.0-dev.27/dist-cjs/polyfills.js' );
 loadJS( 'https://unpkg.com/webp-hero@0.0.0-dev.27/dist-cjs/webp-hero.bundle.js', () => {
   var webpMachine = new webpHero.WebpMachine()
   webpMachine.polyfillDocument()
 } );

}

// Get query params
const searchParams = new URLSearchParams( window.location.search );


/**
* Put your global JS here.
*
* Ideally JS should be coupled with individual components
* and placed within their directories.
* @dev NEED TO MAKE THIS FILE SMALLER.
*/


/**
 * Showers/hiders.
 */
const showers = document.querySelectorAll( '[data-shows]' );
if( !! showers ) {

  for( let el of showers ) {
    const { target, targetSelector, classname = 'is-showing', hide } = el.dataset.shows.includes( 'target' ) || el.dataset.shows.includes( 'targetSelector' ) ? JSON.parse( el.dataset.shows ) : {};
    const id = target || el.dataset.shows;
    const selector = targetSelector || `[data-ref="${id}"]`;
    const targetEl = document.querySelector( selector );
    const hideEls = !! hide ? document.querySelectorAll( hide ) : null;
  
    el.addEventListener( 'click', () => {

      // Temp hack.
      if( !! hideEls ) {
        for( let hideEl of hideEls ) {
          if( ! targetEl.classList.contains( classname ) ) {
            hideEl.style.visibility = 'hidden';
          }
          else {
            hideEl.style.visibility = 'visible';
          }
        }
      }
  
      el.classList.toggle( 'is-clicked' );
      targetEl.classList.toggle( classname );
  
      
      const iframe = targetEl.querySelector( 'iframe' );
      if( ! iframe ) {
        return;
      }
  
      const src = iframe.dataset.src;
  
      // console.log( targetEl.classList );
      // console.log( classname );
  
      // Add autoplay to iframe.
      if( targetEl.classList.contains( classname ) ) {
        iframe.setAttribute( 'src', `${src}&autoplay=1` );
      }
      // Remove autoplay from iframe.
      else {
        iframe.setAttribute( 'src', src.replace( '&autoplay=1', '' ) );
      }

    } );
  
  }

};


/**
 * Waypoints.
 */
const watchScrollers = document.querySelectorAll( '[data-watch-position]' );
if( !! watchScrollers ) {

  for( let el of watchScrollers ) {


    const data = JSON.parse( el.dataset.watchPosition );

    const direction = data.direction || 'top';
    const offset = data.offset || 0;
    const fixed = data.fixed || false;
    const classname = data.classname || 'is-showing';
    const isReveal = data.reveal;
    const cache = {};
    const revealPerc = .66;
    const revealPos = window.innerHeight * revealPerc;

    const initPosition = el.getBoundingClientRect()[direction];

    console.log( { initPosition } );
    console.log( window.scrollY );

    if( initPosition >= 0 ) {
      el.classList.add( classname );
      continue;
    }


    window.addEventListener( 'scroll', () => {

      const position = el.getBoundingClientRect()[direction];

      if( fixed ) {

        if( window.scrollY >= offset ) {
          el.classList.add( classname );
        }
        else if( el.classList.contains( classname ) ) {
          el.classList.remove( classname );
        }

      }
      else {

        if( isReveal ) {

          if( position <= revealPos ) {
            el.classList.add( classname )
          }

        }
        else {
          if( position < offset ) {
            cache.position = window.scrollY;
            el.classList.add( classname )
          }
          else if( ! isReveal && window.scrollY <= cache.position ) {
            el.classList.remove( classname );
          }
        }

        

      }


    } );

  }


}


/**
 * Prefetching.
 */

const { effectiveType } = typeof navigator?.connection == 'object' ? navigator.connection : {};
if( ! document.body.classList.contains( 'logged-in' ) && effectiveType !== '4g' && effectiveType !== '5g' ) {

  const prefetchItems = document.querySelectorAll( `[data-prefetch-url]` );
  if( !! prefetchItems.length ) {
    prefetchLinks( prefetchItems );
  }

  if( WP_SETTINGS.PREFETCH_LOCAL_LINKS ) {

    const localLinks = document.querySelectorAll( `a[href*="${WP_SETTINGS.HOME_URL}]` );
    if( !! localLinks.length ) {
      
      const observer = new IntersectionObserver( ( entries, observer ) => {

        entries.forEach( entry => {

          const { href } = entry.target;
          document.head.innerHTML += `<link rel="prefetch" href="${href}">`

        } );

      } );

      for( let link of localLinks ) {
        observer.observe( link ); 
      }

    }
    
  }
 
}


/* ------ = Marketing = --------------------------------------------------------------------- */


/* ------ = Forms. = --------------------------------------------------------------------- */

/*
 * Automatically add selected class to selected options.
*/
const selects = document.querySelectorAll( 'select' );
if( !! selects ) {
  for( let el of selects ) {

    if( !! el.selectedOptions ) {
      for( let option of el.selectedOptions ) {
        option.classList.add( 'is-selected' );
      }
    }
      
  }
}

/**
 * Mask phone inputs.
 */
//Masker( document.querySelectorAll( `input[type="tel"]` ) ).maskPattern( `(999) 999-9999` );

/**
 * Mask money inputs
 */
// Masker( document.querySelectorAll( `input[data-money]` ) ).maskMoney( {
//   unit: '$',
//   precision: 0,
//   delimiter: ',',
//   zeroCents: false,
// } );

/**
 * Autofill input.s
 * 
 */
const autofillInputs = document.querySelectorAll( `.Form__item [data-param]` );
if( !! autofillInputs ) {
  for( let input of autofillInputs ) {
    
    const paramKey = input.dataset.param;

    if( searchParams ) {
      for( let [ key, value ] of searchParams ) {
        
        if( key == paramKey ) {
          input.value = value;
        }
      }
    }

  }
}

/**
 *  Handle form submissions.
 * 
 * @param   {SubmitEvent}       e
 * @return  {WP_error|object}   Response containing error on fail or from confirmation
 * @return  {string}            response.id                             
 * @return  {bool}              response.isDefault
 * @return  {string}            response.message                            
 * @return  {string}            response.pageId                            
 * @return  {string}            response.queryString                            
 * @return  {string}            response.type
 * @return  {string}            response.url                          
 */
window.submitForm = async e => {

  e.preventDefault();

  loadJS( `${dist}/js/handleFormSubmission.js`, function() {
    handleFormSubmission( e );
  } );  

}

