(function ($) {
    $(document).ready(function () {
      $('.slick-carousel').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: false,
        autoplaySpeed: 2000,
        dots: true,
        arrows: true,
        prevArrow: '<button type="button" class="slick-prev"><svg class="svg-inline--fa fa-chevron-left arrow-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-left" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg=""><path fill="currentColor" d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"></path></svg></button>',
        nextArrow: '<button type="button" class="slick-next"><svg class="svg-inline--fa fa-chevron-right arrow-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg=""><path fill="currentColor" d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"></path></svg></button>',
        appendArrows: $('.custom-arrows'),
        responsive: [
          {
            breakpoint: 1024,
            settings: {
              slidesToShow: 2,
            },
          },
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 1,
            },
          },
        ],
      });

      jQuery( document ).ready(function() {
        $(".colorbox-load").colorbox({iframe:true, width:"884", height:"540"});
      });


    });
  })(jQuery);