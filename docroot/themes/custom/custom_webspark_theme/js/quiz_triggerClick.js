
(function($) {
  Drupal.behaviors.triggerClick = {
    attach: function() {
     //console.log('hello');
     $('.webform-submission-quiz-form').find('.form-actions').hide();
     $('.form-check-input').change(function(){
       var per_text = $('.inner_percent').text();
       if(per_text == "91%"){
          $('.webform-button--submit').trigger('click');
       }
       else{
          $('.webform-button--next').trigger('click');
       }
       
     });
    /** code to move legend above the options **/ 
    var leg_text = $('.fieldset-legend').text();
    var percent_text = $('.webform-progress__percentage').html();

        $('.fieldset-legend').hide();
        console.log(leg_text);
        if($('.custom-legend').length == 0){
            $('.js-form-type-radio').first().before('<div class="custom-legend">'+leg_text+'</div>');
            $('.js-form-type-radio').last().after('<div class="custom-webform-percentbar"><div class="inner_percent">'+percent_text +'</div></div>');
            var inner_percent_text = $('.inner_percent').text();
            var percent_only = $(".inner_percent").find('span').html().replace(/\(.*\)/g,'');
            if($('.inner_percent_value').length == 0) {
                $(".inner_percent").html('<span class="inner_percent_value">' + percent_only + '</span>');
                $('.inner_percent_value').css({'width': percent_only});
            }
            $('.card-header').remove();
            $('.js-webform-radios').addClass('quiz-webform-radios');
        }

        /* set the height of the image to card body height */
        var card_ht = $('fieldset').height();
        console.log(card_ht);
        $('.description').find('img').height(card_ht+'px');
  }
 };
 
})(jQuery);