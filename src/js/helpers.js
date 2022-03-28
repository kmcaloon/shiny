export const prefetchLinks = prefetchItems => {

  const { effectiveType } = navigator.connection;
  if( effectiveType !== '4g' && effectiveType !== '5g' ) {
    return;
  }

  for( let item of prefetchItems ) {

    document.head.innerHTML += `<link rel="prefetch" href="${item.dataset.prefetchUrl}">`;

  }

}
