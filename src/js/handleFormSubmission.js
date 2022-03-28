window.handleFormSubmission = async e => {

  e.preventDefault();


  // Set event.
  if( typeof ga !== 'undefined' ) {

    const type = e.target.dataset.type == 'leadgen' ? 'Lead' : 'Contact';
    ga( 'send', {
      hitType:        'event',
      eventCategory:  'Form Interactions',
      eventAction:    'Form Submission',
      eventLabel: `${type} - ${e.target.dataset.title}`,
    } );
  }

  // Set submit to busy.
  const submitBtn = e.target.querySelector( `button[type="submit"]` );
  submitBtn.classList.add( 'is-busy' );
  submitBtn.disabled = true;

  const formID = e.target.dataset.form;
  const data = {
    source_url: window.location.href,
  };
  const adsource = Cookies.get( 'adsource' );
  if( !! adsource ) {
    data.adsource = adsource;
  }

  for( let [ key, field ] of Object.entries( e.target ) ) {
    if( isNaN( key ) ) {
      break;
    }

    let base64 = null;

    if( field.files ) {
    
      base64 = await convertFileToBase64( field.files[0] );
      console.log( { base64 } );
    }

    const { id, name, value } = field;
    data[name] = base64 || value;

  }


  sessionStorage.setItem( 'recent_submission', JSON.stringify( data ) );

  // const response = await fetch( `${HOME_URL}/wp-json/gf/v2/forms/${formID}/submissions`, {
  //   method: 'POST',
  //   headers: {
  //     'Content-Type': 'application/json',
  //   },
  //   body: JSON.stringify( data ),
  // } );

  const response = await fetch( `${API}/form/${formID}`, {
    method: 'POST',
    body: JSON.stringify( data ),
  } );

  const { status, statusText } = response;
  if( status !== 200 ) {
    return console.warn( statusText );
  }

  const responseData = await response.json();

  console.log( { responseData } );


  if( typeof( responseData ) != 'object' || ! responseData.id ) {
    return responseData;
  }


  // Ajax. 
  if( responseData.type === 'message' ) {

    // Filter the response data.
    responseData.message = responseData.message.replace( /\r\n/g, '<br/>' );

  
    const successMsg = document.createElement( 'div' );
    successMsg.classList.add( 'Form__confirm' );
    successMsg.classList.add( 'text-center' );
    successMsg.innerHTML = `
      <div 
      class="Form__confirm"
      data-confirmation="${formID}"
      >
        <span class="hd-sm px-4">${responseData.message}</span>
      </div>
    `;

    // Filter the html.
    const buttonLinks = successMsg.querySelectorAll( `strong > a` );
    if( buttonLinks ) {
      for( let el of buttonLinks ) {
        el.classList.add( 'Btn', 'is-md', 'is-blue' );
      }
    }

    // Insert message.
    e.target.parentNode.replaceChild( successMsg, e.target );

    // Prepopulate data.
    const messageForm = successMsg.querySelector( 'form' );
    if( !! messageForm ) {

      if( !! messageForm.dataset.prepopulate ) {

        const dynamicInputs = messageForm.querySelectorAll( `[data-prepopulated]` );
        const prepopulateData = JSON.parse( messageForm.dataset.prepopulate );

        for( let [ key, field ] of Object.entries( prepopulateData ) ) {
          
          let inputNum = field;
          inputNum = inputNum.replace( '{', '' ).replace( '}', '' );
          const string = inputNum.split( ':' );
          inputNum = string[1];

          // Find the value 
          let prevData = sessionStorage.getItem( 'recent_submission' );
          if( prevData ) {
            prevData = JSON.parse( prevData );
          }
          const inputVal = prevData[`input_${inputNum}`];
          //const input = messageForm.querySelector( `[name="input_${inputNum}]` );
          //input.value = inputVal;
          console.log( { dynamicInputs } );
          for( let input of dynamicInputs ) {
            if( !! input.value ) {
              continue;
            }
            input.value = inputVal;
          }

          console.log( { inputVal } );



        }



      }
    }
    

    // Scroll to message.
    const coords = document.querySelector( `[data-confirmation="${formID}"]` ).getBoundingClientRect();

    window.scrollTo( {
      left: 0, 
      top: coords.top + window.scrollY - 200,
      behavior: 'smooth',
    } );

  }
  // Page.
  else if( responseData.type === 'page' ) {

    let url = `${HOME_URL}/?p=${responseData.pageId}`;
    if( !! responseData.queryString ) {
      url += `&${responseData.queryString}`;
    }
    window.location.href = url;
  }

  // Redirect.
  else if( responseData.type === 'redirect' ) {

    let url = responseData.url;
    if( !! responseData.queryString ) {
      url += `?${responseData.queryString}`;
    }
    window.location.href = url;

  }

  return responseData;




}

function convertFileToBase64(file) {

  return new Promise( ( resolve, reject ) => {

    const reader = new FileReader();
    reader.onloadend = () => {
      // use a regex to remove data url part
      //const base64String = btoa( reader.result );

      resolve( reader.result );
  
    };
    reader.readAsDataURL( file );
  



  } )
  ;

  

}