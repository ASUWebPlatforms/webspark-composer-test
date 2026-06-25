// (function ($, Drupal, drupalSettings) {
//   Drupal.behaviors.apr = {
//     attach: function (context, settings) {
jQuery(document).ready(function ($) {

      // Styling
      $(".ui-accordion").addClass("accordion");

      $(".ui-accordion-header").once().each(function (index ) {
        $(this).addClass("accordion-header");
      });

      $(".ui-accordion-content").once().each(function (index ) {
        $(this).addClass("accordion-body");
      });

      // // Remove .groupedbyyear .accordion-item from inner View - Added on 2/9/2024.
      // $(".view-academic-program-review > .view-content > .groupedbyyear.accordion-item > .ui-accordion-content > .groupedbyyear.accordion-item").each(function (index) {
      //   console.log("cstest inside");
      //   $(this).removeClass("accordion-item");
      // });
      $(".ui-accordion-content").find(".accordion-item").each(function () {
        $(this).removeClass("accordion-item");
      });


      $(".ui-accordion-header-icon.ui-icon.ui-icon-triangle-1-s").each(function(index) {
        $(this).prepend("<svg class=\"svg-inline--fa fa-chevron-up fa-w-14\" aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon=\"chevron-up\" role=\"img\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" data-fa-i2svg=\"\"><path fill=\"currentColor\" d=\"M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z\"></path></svg>");
      });
      $(".ui-accordion-header-icon.ui-icon.ui-icon-triangle-1-e").each(function(index) {
        $(this).prepend("<svg class=\"svg-inline--fa fa-chevron-up fa-w-14\" aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon=\"chevron-up\" role=\"img\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" data-fa-i2svg=\"\"><path fill=\"currentColor\" d=\"M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z\"></path></svg>");
      });

      // Add Css class: apr-first-item - Use CSS to display the first Specific programs. Hide the rest.
      // CSS is in View's Header: /admin/structure/views/view/academic_program_review
      $('.view-academic-program-review > .view-content > .groupedbyyear.accordion-item').each(function(i){
        $(this).find('.ui-accordion-content').find('.groupedbyyear').each(function(j){
          $(this).find('.apr-grouping').each(function(k) {
            $(this).find('.inner-group').each(function(l){
              if ( l === 0 ) {
                $(this).addClass('apr-first-item');
              }
            });
          });
        });
      });

//     }
//   };
// })(jQuery, Drupal, drupalSettings);
});
