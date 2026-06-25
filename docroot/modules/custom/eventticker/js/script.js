//There are changes for accessibility purposes made to slick.js and slick.min.js, do not overwrite them.
jQuery( document ).ready(function($) {
  $('.sdaInner').slick({
    slidesToShow: 5,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 5000,
    infinite: true,
    arrows: true,
    respondTo: 'slider',
    focusOnSelect: false,
    prevArrow: "<button type=\"button\" aria-label=\"Moves the carousel backwards to see previous events\" class=\"slick-prev\"><span class=\"fas fa-chevron-left\"></span></button>",
    nextArrow: "<button type=\"button\" aria-label=\"Moves the carousel forward to see more events\" class=\"slick-next\"><span class=\"fas fa-chevron-right\"></span></button>",
    responsive: [
      {
        breakpoint: 960,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });
  $('#slickPlaypause').on('click', function(e) {
    e.preventDefault;
    if(!$('#slickPlaypause').hasClass('pause')) {
      $('.sdaInner').slick('slickPause');
      $('#slickPlaypause').toggleClass('pause');
      $('#slickPlaypause svg').attr('class', 'svg-inline--fa fa-play');
    }
    else {
      $('.sdaInner').slick('slickPlay');
      $('#slickPlaypause').toggleClass('pause');
      $('#slickPlaypause svg').attr('class', 'svg-inline--fa fa-pause');
    }
  })
});
