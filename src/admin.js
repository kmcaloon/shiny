//import loadJS from './js/loadJS';

import yesno from 'yesno-dialog';

window.apiHeaders = {
  'Content-Type': 'application/json',
  'X-WP-Nonce': WP_SETTINGS.NONCE,
}

/* ------ = Helpers. = --------------------------------------------------------------------- */

window.alpineFetchBtn = () => {
  return {
    status: null,

    /**
     * Run fetch.
     */
    async runFetch( {
      route,
      method,
      errorMessage,
      successMessage,
      showSpinner,
    } ) {

      const alpine = this;

      alpine.status = 'fetching';

      const button = alpine.$el.querySelector( 'button' );
      if( button ) {
        button.disabled = true;
      }
      if( !! showSpinner ) {
        document.body.classList.add( 'is-waiting' );
      }
      
      let override = false;
      if( route.includes( 'sf' ) && ! route.includes( 'locations' ) ) {
        
        override = await yesno( {
          bodyText: 'Would you like to skip updating items that have already been synced? Unless needed, this should generally be avoided to save resources.',
          labelNo:  `No, sync and overwrite all items`,
        } );

      }

      fetch( `${WP_SETTINGS.API}${route}`, {
        method: method || 'GET',
        headers: {
          ... apiHeaders,
        },
        body: JSON.stringify( {
          override: ! override
        } ),
      } )
      .then( function( response ) {

        document.body.classList.remove( 'is-waiting' );

        console.log( response );
        
        if( response.status !== 200 ) {
          status = 'fail';
          alert( errorMessage );
          return;
        }
        return response.json()
      } )
      .then( function( data ) {
        if( ! data ) {
          return;
        }
        alpine.status = 'success';

        if( !! button ) {
          button.disabled = false;
        }

        if( !! successMessage ) {
          alert( successMessage );
        }
        
      } ); 


    }
  }
}



/* ------ = Flex/Page Builder. = --------------------------------------------------------------------- */
const flexEditor = document.querySelector( `[data-acfe-flexible-preview="1"]` );

if( !! flexEditor ) {

  // Collapse menu
  const collapseBtn = document.querySelector( `#collapse-button[aria-expanded="true"]` );
  if( collapseBtn ) {

    // const click = new MouseEvent( 'click', {
    //   bubbles: true,
    //   view: window,
    // } );
    // collapseBtn.dispatchEvent( click );

    document.body.classList.add( 'folded' );

  }


  setTimeout( () => {

    let globalCSS = require( '../dist/css/global.css' ).toString();

    globalCSS = `
      h1, h2, h3, h4 {
        all: revert;
      }
      .acfe-flexible-placeholder {
        font-size: 1rem;
        //font-family: var( --f-accent );
        text-rendering: geometricPrecision;
        font-weight: 500;
      }

  
    ` + globalCSS;

    const globalStyles = document.createElement( 'style' );
    globalStyles.setAttribute( 'type', 'text/css' );
    globalStyles.setAttribute( 'scoped', true );
    globalStyles.appendChild( document.createTextNode( globalCSS ) );
    
    // const containers = document.querySelectorAll( `[data-acfe-flexible-preview="1"]` );
    // if( contains )

    let target = document.querySelector( '#acf_after_title-sortables .inside.acf-fields' );
    if( ! target ) {
      target = document.querySelector( '#normal-sortables .inside.acf-fields' )
    }

    console.log( { target } );
    target.prepend( globalStyles );
    // console.log( { targets } );
    // if( !! targets ) {
    //   for( let target of targets ) {
    //     console.log( 'injecting styles' );

    //     console.log( { target } );
    //     target.prepend( globalStyles );
    //   }
    // }
    
    //flexEditor.prepend( globalStyles );

    // Custom post types.
    let postTypeCSS = null;
    if( !! postTypeCSS ) {
      globalStyles.appendChild( document.createTextNode( postTypeCSS ) )
    }


    require( 'style-scoped' );

  }, 1000 );
}


/* ------ = Block editor. = --------------------------------------------------------------------- */
if( document.body.classList.contains( 'block-editor-page' ) ) {


  /**
   * Watch for changes.
   */
  const docObserver = new MutationObserver( ( mutationsList, observer ) => {

    if( !! mutationsList && mutationsList[0].target.className == 'block-editor-inserter__menu' ) {

      const preview = document.querySelector( '.block-editor-inserter__preview-content-missing' );
      if( ! preview ) {
        return;
      }
      
      setTimeout( () => {

        // Get the image.
        const imageContainer = document.querySelector( 'button.block-editor-block-types-list__item:not(:disabled):hover .block-editor-block-types-list__item-icon' );
        const styles = window.getComputedStyle( imageContainer, false );
        const bgValue = styles.backgroundImage;
        preview.style.backgroundImage = bgValue;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = 'center center';
        preview.style.fontSize = '0px';

      }, 0 );
      


    }

  } );
  docObserver.observe( document, {
    childList: true,
    subtree: true,
  } );

  /**
   * Add scoped stylsheet.
   */
  setTimeout( () => {
    const globalCSS = require( '../dist/css/global.css' ).toString();
    const globalStyles = document.createElement( 'style' );
    globalStyles.type = 'text/css';
    globalStyles.setAttribute( 'scoped', true );
    globalStyles.appendChild( document.createTextNode( globalCSS ) );

    const blocksRoot = document.querySelector( '.is-root-container' );

    blocksRoot.prepend( globalStyles );

    // Custom post types.
    let postTypeCSS = null;
    console.log( { postTypeCSS } );

    if( !! postTypeCSS ) {
      globalStyles.appendChild( document.createTextNode( postTypeCSS ) )
    }

    require( 'style-scoped' );

  }, 500 );


  /**
   * Reusable block edit button hijack.
   */
  wp.data.subscribe( () => {

    const reusablePanel = document.querySelector( '.reusable-block-edit-panel' );
    if( ! reusablePanel ) {
      return;
    }

    const reusableEdit = document.querySelector( '.reusable-block-edit-panel__button'  )
    const customButton = document.createElement( 'button' );
    customButton.className = 'components-button reusable-block-edit-panel__button is-secondary';
    customButton.innerHTML = 'Edit';
    customButton.addEventListener( 'click', e => {

      const blockTitle = e.target.parentNode.firstChild.innerHTML;
      const postID = blockTitle.match( /id:(.*)/ )[1];

      window.open( `${WP_SETTINGS.HOME_URL}/wp-admin/post.php?post=${postID}&action=edit`, '_blank' );

      const reload = window.confirm( `Do you want to save and refresh your page to see your updates to reusable sections? Cancel to continue editing.` );

      if( !! reload ) {
        wp.data.dispatch( 'core/editor' ).savePost();

        // How gross.
        setTimeout( () => {

          if( !! wp.data.select('core/editor').didPostSaveRequestSucceed() ) {
            window.location.reload();
          }
          else {
            setTimeout( () => {
              if( !! wp.data.select('core/editor').didPostSaveRequestSucceed() ) {
                window.location.reload();
              }

            }, 1500 )
          }

        }, 1500 );
      }
      else {
        console.log( 'no' );
      }

    } );
  
    reusableEdit.parentNode.replaceChild( customButton, reusableEdit );
   

  } );

  /**
   * Add double click editing.
   */
  ( function( $ ) {


    const $doc = $(document);

    $doc.on( 'dblclick', '.acf-block-body', function() {

      const $editBtn = $( document.querySelector( 'button[aria-label*="Edit"]' ) );

      if( ! $editBtn ) {
        return;
      }
      $editBtn.click();

      const $hack = $editBtn.clone();
      $hack.attr( 'id', 'hack' );
      $hack.appendTo( 'body' );

    } );

    $doc.on( 'click', function( e ) {

      const target = e.target;
      if( target.getAttribute( 'id' ) == 'hack' || target.closest( '.acf-block-body' ) ) {
        return ;
      }

      if( document.getElementById( 'hack' ) ) {
        $(document.getElementById( 'hack' ) ).click();

      }

      // if( !! window.$activeEditBtn ) {
      //   window.$activeEditBtn.click();
      //   //window.$activeEditBtn.onClick();
      // }

      // $(document.querySelector( 'button[aria-label*="Edit"]' ) ).click()
      //
      // if( !! document.querySelector( 'button[aria-label*="Edit"]' ) ) {
      //   console.log( 'toolbar is open' );
      // }
      // else {
      //   console.log( 'toolbar isnt open' );
      // }




    } );

  
   } )( jQuery );

}

/* ------ = Hack user editing. = --------------------------------------------------------------------- */

if( document.body.classList.contains( 'user-edit-php' ) ) {

  // Hide profile field.
  const defaultPhotoField = document.querySelector( '.user-profile-picture' );
  if( !! defaultPhotoField ) {
    defaultPhotoField.style.display = 'none';
  }
  const defaultDescriptionField = document.querySelector( '.user-description-wrap' )
  if( !! defaultDescriptionField ) {
    defaultDescriptionField.style.display = 'none';
  }

  // Get any custom fields.
  const customFields = document.querySelector( '.acf-field' );
  const acfTable = customFields.closest( 'table' );

  const aboutTable = document.getElementById( 'description' ).closest( 'table' );

  $(acfTable).insertAfter( aboutTable );

}



/* ------ = Admin menu. = --------------------------------------------------------------------- */
( function SideMenu( $ ) {

    $(window).load( function() {

        var $sectionHeads = $('li.js-menu-section');

        // Create menu sections
        createSections( $sectionHeads );

        // Collapsing events
        collapsingEvents( $sectionHeads );

        // Fit submenus to viewport
        fitMenusToViewport();

        // Custom clicking events
        clickingEvents();

    });


    /**
     * Create menu sections
    */
    function createSections( $sectionHeads ) {

        // Cycle through each section
        $sectionHeads.each( function() {

            // Sync all menu items between section heading and next separator
            var $sectionItems = $(this).nextUntil( '.wp-menu-separator', '.menu-top:not(.js-menu-section)' );
            $sectionItems.addClass( 'section-item' );

        });

        // Make section heading current if child is current
        var $activeSection = $('li.wp-has-current-submenu').prevUntil( '.js-menu-section' );
        var $submenuItems = $activeSection.nextUntil( '.wp-menu-separator', '.menu-top:not(.js-menu-section)' );

        $activeSection.addClass( 'is-collapsed' );
        $submenuItems.addClass( 'is-collapsed' );


    }

    /**
     * Collapsing events
     */
    function collapsingEvents( $sectionHeads ) {

        // User clicks on section head
        $sectionHeads.on( 'click', function( e ) {
          e.preventDefault();

            var $otherSections =  $('li.js-menu-section').not( this );
            var $submenuItems = $(this).nextUntil( '.wp-menu-separator', '.menu-top:not(.js-menu-section)' );
            var $otherItems = $otherSections.nextUntil( '.wp-menu-separator', '.menu-top:not(.js-menu-section)' );

            // Uncollapse other menus
            $otherSections.removeClass( 'is-collapsed' );
            $otherItems.removeClass( 'is-collapsed' );

            // Make sure this section head is active
            $(this).toggleClass( 'is-collapsed' );

            // Show/hide all menu items between section heading and next separator
            $submenuItems.toggleClass( 'is-collapsed' );

            // Adjust accordion?
            window.dispatchEvent( new Event( 'resize' ) );

        } );

    }

    /**
     * Adjust submenus so that the fit within viewport
     */
    function fitMenusToViewport() {

        checkMenus();
        setupScrollBars();

        /*
         * Check all menus to see if they need to be adjusted
         */
        function checkMenus() {

            var $submenus = $('.wp-submenu');
            var $hasSubmenu = $('.wp-has-submenu');

            // Go through each menu to check it's height/position relative to view port
            $hasSubmenu.on( 'hover', function() {

                var viewportHeight = $(window).height();
                var $submenu = $(this).find( '.wp-submenu' );

                if( $submenu.length ) {

                    // Reset to natural height
                    $submenu.removeClass( 'is-scrollable' );
                    $submenu.css( 'max-height', '1000px' );

                    var $menuContainer = $('#adminmenuback');
                    var menuHeight = $submenu.outerHeight();
                    var offsetTop = $submenu.offset().top - $menuContainer.offset().top;
                    var menuPosition = menuHeight + offsetTop;

                    // Adjust f bottom of menu exceeds viewport
                    if( menuPosition > viewportHeight ) {

                        adjustMenu( $submenu, offsetTop, viewportHeight );

                    }
                }
            } );
        }

        /**
         * Adjust menu so bottom does not exceed viewport
         */
        function adjustMenu( $submenu, offsetTop, viewportHeight ) {

            var adjustedHeight = viewportHeight - offsetTop - 20;

            $submenu.addClass( 'is-scrollable' );
            $submenu.css( 'max-height', adjustedHeight );

        }

        /**
         * Setup custom scroll bars
         */
        function setupScrollBars() {

            // $('.wp-submenu').enscroll( {
            //     verticalHandleClass: 'handle',
            //     scrollIncrement: 15,
            //     easingDuration: 300
            // } );

        }


    }

    /**
     * Clicking events
     */
    function clickingEvents() {

        disableSubHeadings();


        /**
         * Disable submenu label clicks
         */
        function disableSubHeadings() {

            var $subHeading = $('a.wp-has-submenu');

            $subHeading.on( 'click', function( event ) {

                event.preventDefault();

            } );
        }

    }

} )( jQuery );


