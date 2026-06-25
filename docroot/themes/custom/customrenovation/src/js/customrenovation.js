(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.customRenovation = {
    attach: function (context, settings) {
      // Academic units script
      $('.leader-pic:empty').parent('.leader-thumb').detach();
      $('.leader-thumb:nth-child(2)').next('.col-md-8').removeClass('col-md-8').addClass('col-md-12');
      $('.views-element-container .researchunits [data-toggle="collapse"]').addClass('collapsed');
      $('.views-element-container .researchunits [data-toggle="collapse"]').click(function() {
        if ($(this).hasClass('collapsed')) {
          $(this).parent().next('.leaders').hide();
        }
        else {
          $(this).parent().next('.leaders').show();
        }
      });
      $('.expcl').click(function() {
        $('.card.card-body.collapse.clearfix').removeAttr('style');
        $('[data-toggle="collapse"]').removeClass('collapsed');
        $('.card.card-body.collapse.clearfix').addClass('show');
        $('.lead-name').addClass('collapse');
        $(this).toggleClass('active');
        $('.collcl').toggleClass('active');
      });

      $('.collcl').click(function() {
        $('[data-toggle="collapse"]').addClass('collapsed');
        $('.card.card-body.clearfix.collapse.show').removeClass('show');
        $('.lead-name').removeClass('collapse');
        $(this).toggleClass('active');
        $('.expcl').toggleClass('active');
        $('.card.card-body.collapse.clearfix').removeAttr('style');
      });


      $(function() {
        // Fix displayed markup on image carousel
        $(".glide__slide figcaption .uds-caption-text p").each(function() {
          let text = jQuery(this).text();
          let replace = text.replace(/&lt;(.*?)&gt;/g, '');
          $(this).replaceWith(replace);
        });
        // Remove padding from main on last section with "last-bg" class.
        $('.last-bg').closest('main').removeClass('pb-5');
      });
    }
  };

})(jQuery, Drupal);