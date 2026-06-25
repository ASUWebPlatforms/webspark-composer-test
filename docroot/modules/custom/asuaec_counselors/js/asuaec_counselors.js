(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.counselors = {
    attach: function (context, settings) {
      // JS for "Counselors section /counselors -> Newsletter node"

      // Move img src and Title into ASU Hero
      // Hero image
      var img_src = $('article.newsletter > .layout__fixed-width .layout__region--first > .block > img').attr('src');
      console.log("img src:", img_src);
      if(img_src) {
        $('article.newsletter img.hero').attr('src', img_src);
      }

      // H1
      var h1_text = $('article.newsletter > .layout__fixed-width .layout__region--first > .block > h1').html();
      console.log("h1 text:", h1_text);
      $('.uds-hero-md > h1 > span.highlight-gold').html(h1_text);

    }
  };
})(jQuery, Drupal, drupalSettings);