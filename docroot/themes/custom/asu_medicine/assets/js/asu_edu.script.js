/**
 * @file
 * Asu.edu behaviors.
 */

(function ($, Drupal) {

    'use strict';
  
    /**
     * ASU.edu theme related behaviors.
     */
    Drupal.behaviors.asuEdu = {
      attach: function (context, settings) {
  
      //Data Layer
       var pushGAEvent = (args) => {
          const { dataLayer } = window;
          if (dataLayer) dataLayer.push(args);
       };
    
      $(once('imageCarousel', '.image-carousel', context))
          .each(function () {
            var ArrowDisabler = function (Glide, Components, Events) {
              return {
                mount() {
                  // Only in effect when rewinding is disabled
                  if (Glide.settings.rewind) {
                    return
                  }
                  Glide.on(['mount.after', 'run'], () => {
                    // Filter out arrows_control
                    for (let controlItem of Components.Controls.items) {
                      if (controlItem.className !== 'glide__arrows') {
                        continue
                      }
                      // Set left arrow state
                      var left = controlItem.querySelector('.glide__arrow--left')
                      if (left) {
                        if (Glide.index === 0) {
                          left.classList.add("glide__arrow--disabled");
                          //left.setAttribute('disabled', '') // Disable on first slide
                        } else {
                          left.classList.remove("glide__arrow--disabled");
                          //left.removeAttribute('disabled') // Enable on other slides
                        }
                      }
                      // Set right arrow state
                      var right = controlItem.querySelector('.glide__arrow--right')
                      if (right) {
                        if (Glide.index === Components.Sizes.length - Glide.settings.perView) {
                          right.classList.add("glide__arrow--disabled");
                          //right.setAttribute('disabled', '') // Disable on last slide
                        } else {
                          right.classList.remove("glide__arrow--disabled");
                          //right.removeAttribute('disabled') // Disable on other slides
                        }
                      }
                    }
                  })
                }
              }
            }
  
            //Image Carousel Data Layer
            const elements = document.querySelectorAll('[data-ga-image-carousel]');
            elements.forEach((element) =>
              element.addEventListener('click', () => {
                const event = element
                  .getAttribute('data-ga-image-carousel-event')
                  .toLowerCase();
                const type = element
                  .getAttribute('data-ga-image-carousel-type')
                  .toLowerCase();
                const section = element
                  .getAttribute('data-ga-image-carousel-section')
                  .toLowerCase();
                const text = element.getAttribute('data-ga-image-carousel').toLowerCase();
                const component = element.getAttribute(
                  'data-ga-image-carousel-component'
                );
                const args = {
                  event,
                  type,
                  section,
                  text,
                  ...(component && {
                    component: component.toLowerCase(),
                  }),
                  action: 'click',
                  name: 'onclick',
                  region: 'main content',
                };
                pushGAEvent(args);
              })
            );
  
            //Ranking Carousel Data layer
            const rankingsElements = document.querySelectorAll('[data-ga-rankings-carousel]');
            rankingsElements.forEach((element) =>
              element.addEventListener('click', () => {
                const event = element
                  .getAttribute('data-ga-rankings-carousel-event')
                  .toLowerCase();
                const action = element
                  .getAttribute('data-ga-rankings-carousel-action')
                  .toLowerCase();
                const name = element
                  .getAttribute('data-ga-rankings-carousel-name')
                  .toLowerCase();
                const type=  element
                  .getAttribute('data-ga-rankings-carousel-type')
                  .toLowerCase();
                const region = element
                  .getAttribute('data-ga-rankings-carousel-region')
                  .toLowerCase();
                const section = element
                  .getAttribute('data-ga-rankings-carousel-section')
                  .toLowerCase();
                const text =  element.getAttribute('data-ga-rankings-carousel')
                  .toLowerCase();
                const component = element
                  .getAttribute('data-ga-rankings-carousel-component');
  
                const args = {
                  event,
                  action,
                  name,
                  type,
                  region,
                  section,
                  text,
                  ...(component && {
                    component: component.toLowerCase(),
                  }),
                };
  
                pushGAEvent(args);
              })
            );
  
            const rankingsFormElements = document.querySelectorAll('[data-ga-rankings-carousel-form-name]');
              rankingsFormElements.forEach((element) =>
                element.addEventListener('change', (e) => {
                  const event = element
                    .getAttribute('data-ga-rankings-carousel-form-event')
                    .toLowerCase();
                  const action = element
                    .getAttribute('data-ga-rankings-carousel-form-action')
                    .toLowerCase();
                  const name = element
                    .getAttribute('data-ga-rankings-carousel-form-name')
                    .toLowerCase();
                  const type=  element
                    .getAttribute('data-ga-rankings-carousel-form-type')
                    .toLowerCase();
                  const region = element
                    .getAttribute('data-ga-rankings-carousel-form-region')
                    .toLowerCase();
                  const section = element
                    .getAttribute('data-ga-rankings-carousel-form-section')
                    .toLowerCase();
                  const text =  element.nodeName.toLowerCase() === 'select' ?
                    e.target.options[e.target.selectedIndex].text.toLowerCase() :
                    e.target.value;
                  const component = element
                    .getAttribute('data-ga-rankings-carousel-form-component');
  
                  const args = {
                    event,
                    action,
                    name,
                    type,
                    region,
                    section,
                    text,
                    ...(component && {
                      component: component.toLowerCase(),
                    }),
                  };
  
                  pushGAEvent(args);
                })
              );
  
            new Glide($(this)[0], {
              type: "slider", // No wrap-around.
              focusAt: 0,
              bound: true, // Only if type slider with focusAt 0
              rewind: false, // Only if type slider
              keyboard: true, // Left/Right arrow key support for slides - true is default. Accessible?
              startAt: 0,
              swipeThreshold: 80, // Distance required for swipe to change slide.
              dragThreshold: 120, // Distance for mouse drag to change slide.
              perTouch: 1, // Number of slides that can be moved per each swipe/drag.
              peek: 0,
              gap: 0,
              perView: 1,
            }).mount({ArrowDisabler});
          });
        
        $(once('imageCarouselCta', '.image-carousel-cta', context))
          .each(function () { 
            var ArrowDisabler = function (Glide, Components, Events) {
              return {
                mount() {
                  // Only in effect when rewinding is disabled
                  if (Glide.settings.rewind) {
                    return
                  }
                  Glide.on(['mount.after', 'run'], () => {
                    // Filter out arrows_control
                    for (let controlItem of Components.Controls.items) {
                      if (controlItem.className !== 'glide__arrows') {
                        continue
                      }
                      // Set left arrow state
                      var left = controlItem.querySelector('.glide__arrow--left')
                      if (left) {
                        if (Glide.index === 0) {
                          left.classList.add("glide__arrow--disabled");
                          //left.setAttribute('disabled', '') // Disable on first slide
                        } else {
                          left.classList.remove("glide__arrow--disabled");
                          //left.removeAttribute('disabled') // Enable on other slides
                        }
                      }
                      // Set right arrow state
                      var right = controlItem.querySelector('.glide__arrow--right')
                      if (right) {
                        if (Glide.index === Components.Sizes.length - Glide.settings.perView) {
                          right.classList.add("glide__arrow--disabled");
                          //right.setAttribute('disabled', '') // Disable on last slide
                        } else {
                          right.classList.remove("glide__arrow--disabled");
                          //right.removeAttribute('disabled') // Disable on other slides
                        }
                      }
                    }
                  });
                }
              }
            }
  
            var resizeModifier = function (Glide, Components, Events) {
              return {
                mount() {
                  var ctaValues = [];
                  Components.Html.slides.forEach(function (slide) {
                    ctaValues.push(slide.querySelector('a.btn').innerHTML);
                  });
  
                  Glide.on(['resize', 'mount.after'], () => {
                    Components.Html.slides.forEach(function (slide, index) {
                      var value = Drupal.t('Visit');
                      if (Components.Sizes.width > 1260) {
                        // Keep the original value
                        value = ctaValues[index];
                      }
                      slide.querySelector('a.btn').innerHTML = value;
                    });
                  })
                }
              }
            }
            const viewPortWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            const headerGap = (viewPortWidth - 1200) / 2;
            new Glide($(this).find('.one-university-carousel', context)[0], {
              type: "slider", // No wrap-around.
              focusAt: 0,
              bound: true, // Only if type slider with focusAt 0
              rewind: false, // Only if type slider
              gap: 24, // Space between slides... may be impacted by viewport size.
              keyboard: true, // Left/Right arrow key support for slides - true is default. Accessible?
              startAt: 0,
              swipeThreshold: 80, // Distance required for swipe to change slide.
              dragThreshold: 120, // Distance for mouse drag to change slide.
              perTouch: 1, // Number of slides that can be moved per each swipe/drag.
              peek: headerGap,
              perView: 1,
              breakpoints: {
                1260: {
                  peek: 32,
                },
              }
            }).mount({ ArrowDisabler, resizeModifier });
  
          });
  
          /*
          $(once('chartsGraphs', 'uds-charts-and-graphs-container', context))
          .each(function () {
            var $percentage = $(this).attr('data-number');
            var ctx = $(this).find('canvas', context);
            const config = {
              type: 'doughnut',
              data: {
                datasets: [
                  {
                    data: [$percentage, 100 - $percentage],
                    backgroundColor: ['#ffc627', '#fafafa'],
                  },
                ],
              },
              options: {
                cutout: '70%',
                //responsive: false, // remove if want static size
                tooltips: {enabled: false},
                events: [],
                //maintainAspectRatio: false, // remove if want static size
              },
            };
            var myChart = new Chart(ctx, config);
          });
  
  //      Add paralax effect
  
      $(once('newsCardsLayout', '.asu-alumni .6-6', context))
          .each(function () {
            var $container = $(this);
            if ($container.find('.block-inline-blockevents', context).length == 1) {
              $container.find('.layout__region--first', context).removeClass('col-md-6').addClass('col-md-5');
              $container.find('.layout__region--second', context).removeClass('col-md-6').addClass('col-md-7');
            }
          });
        */
  
        // WWWCMS-52 add datalayer to links
        $(once('gtmInPerson', '#nav-in-person', context))
          .each(function () {
            var ga = $(this).find('a', context);
            ga.each(function() {
              var $a = $(this);
              $a.attr({
                'data-ga-future-event': 'link',
                'data-ga-future-action': 'click',
                'data-ga-future-name': 'onclick',
                'data-ga-future-type': 'internal link',
                'data-ga-future-region': 'main content',
                'data-ga-future-section': 'i am a future in-person:',
                'data-ga-future-text': $a.text().toLowerCase()
              }); 
            })
          });
          
        // WWWCMS-52 add datalayer to links
        $(once('gtmOnline', '#nav-online', context))
          .each(function () {
            var gb = $(this).find('a', context);
            gb.each(function() {
              var $b = $(this);
              $b.attr({
                'data-ga-future-event': 'link',
                'data-ga-future-action': 'click',
                'data-ga-future-name': 'onclick',
                'data-ga-future-type': 'internal link',
                'data-ga-future-region': 'main content',
                'data-ga-future-section': 'i am a future online:',
                'data-ga-future-text': $b.text().toLowerCase()
              });
            })
          });
          
        // WWWCMS-83 - Hide Delete tab on homepage
        $('.asu-home .block--asu-edu-tabs li.nav-item a.nav-link:contains("Delete")')
          .parent()
          .css('display', 'none');
  
        // Spanish lang button datalayer
        $(once('esLang', '#es-lang-button', context))
        .each(function () {
          var gc = $(this).find('a', context);
          gc.each(function() {
            var $c = $(this);
            $c.attr({
              'data-ga-future-event': 'link',
              'data-ga-future-action': 'click',
              'data-ga-future-name': 'onclick',
              'data-ga-future-type': 'internal link',
              'data-ga-future-region': 'main content',
              'data-ga-future-section': 'i am a future:',
              'data-ga-future-text': $c.text().toLowerCase()
            });
          })
        });
  
        // Add uds-full-width to emergency banner div
        $('#block-emergencybanner')
          .addClass('uds-full-width');
  
          // Future Data layer
          const futureElements = document.querySelectorAll('[data-ga-future-text]');
          futureElements.forEach((element) =>
            element.addEventListener('click', function () {
                const event = element
                  .getAttribute('data-ga-future-event')
                  .toLowerCase();
                const action = element
                  .getAttribute('data-ga-future-action')
                  .toLowerCase();
                const name = element
                  .getAttribute('data-ga-future-name')
                  .toLowerCase();
                const type = element
                  .getAttribute('data-ga-future-type')
                  .toLowerCase();
                const region = element
                  .getAttribute('data-ga-future-region')
                  .toLowerCase();
                const section = element
                  .getAttribute('data-ga-future-section')
                  .toLowerCase();
                const text = element
                  .getAttribute('data-ga-future-text')
                  .toLowerCase();
  
                const args = {
                  event,
                  action,
                  name,
                  type,
                  region,
                  section,
                  text,
                };
  
                pushGAEvent(args); 
  
              })
          );
  
      }
    };
  
  }(jQuery, Drupal));
  