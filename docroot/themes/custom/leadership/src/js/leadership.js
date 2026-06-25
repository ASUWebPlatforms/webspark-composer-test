/**
 * @file
 * Global utilities.
 *
 */


(function (Drupal, $, core) {

  'use strict';


 
  Drupal.behaviors.leadership = {
    attach: function(context, settings) {
          
      // Video Modal Close Effect
      var url = $('#videoModal iframe').attr('src');


      $('.close').click(function() {
          $('#videoModal').hide();
        $('#videoModal iframe').attr('src', '');
      });

      $('.close').click(function() {
        $('#videoModal').show();
        $('#videoModal iframe').attr('src', url);
      });
      
      $(".first-word").html(function(){
        var text= $(this).text().trim().split(" ");
        var first = text.shift();
        return (text.length > 0 ? "<span class='underline-gold'>"+ first + "</span> " : first) + text.join(" ");
      });

    }
  };


})(Drupal, once);


