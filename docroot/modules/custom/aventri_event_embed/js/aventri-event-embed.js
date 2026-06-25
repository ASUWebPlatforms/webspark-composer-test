jQuery( document ).ready(function($) {
  function checkIframeLoaded() {
    var aventriFrame = $('.aventri-widget iframe');
    var aventriFrameDoc = aventriFrame[0].contentDocument;
    var iframeHeight = aventriFrame.css('height');

    if( aventriFrameDoc.readyState  === 'complete' &&  parseInt(iframeHeight, 10) > 150) {
      iframeHeight = parseInt(iframeHeight, 10) - 192;

      $('.page-wrapper-webspark').css('height', 'calc(100% + ' + iframeHeight.toString() + 'px');
      return;
    }
    window.setTimeout(checkIframeLoaded, 300);
  }
  checkIframeLoaded();
});
