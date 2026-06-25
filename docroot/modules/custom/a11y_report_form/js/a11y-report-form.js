/**
 * @file
 * Global utilities.
 *
 */

(function ($,Drupal,once) {
    'use strict';
    Drupal.behaviors.a11yreportcookie = {
      attach: function(context, settings) {
        var referrer = document.referrer;
        var referrer_url = '';
        var now = new Date();
        // 1 hour from now
        now.setTime(now.getTime() + (60*60*1000));
        //referrer cookie functionality if the cookie is not set
        if(!Cookies.get('referring')){
            if(referrer != ''){
                referrer_url = referrer;
            //else set it to blank
            }else{
                referrer_url = '';
            }
            Cookies.set('referring', referrer_url,{expires: 1, path: '/'});
        }
      const paramName = 'a11yref';
      const inputSelector = '#edit-url';
      const queryParams = new URLSearchParams(window.location.search);
      const hashParams = new URLSearchParams(window.location.hash.substring(1));
      const value = queryParams.get(paramName) || hashParams.get(paramName) || referrer_url;
      if (!value) {
        return;
      }
      const elements = once('a11yref-setter', inputSelector, context);
      elements.forEach(function (element) {
        $(element).val(value);
      });
        // if the url input is empty, default to the referrer
        // removing this because referrer is often stripped of path by the browser
        // if ((Cookies.get('referring') != '')){
        //   $('#edit-url').val(Cookies.get('referring'));
        // }
      }
    };
  })(jQuery, Drupal,once);



  