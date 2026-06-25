/**
 * @file
 * ASU.edu Components behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.asuEduComponents = {
    attach: function (context, settings) {

      //Data Layer
      var pushGAEvent = (args) => {
        const { dataLayer } = window;
        if (dataLayer) dataLayer.push(args);
      };

      $(once('gridLinksHeading', '.asu-edu-grid-links-heading a', context))
        .each(function() {
          var $text = $(this).text().trim();
          var $words = $text.split(/\s+/);
          var $modifiedText = '<h2 class="mt-0"><span class="highlight-gold">' + $words.slice(0, 1) + '</span></h2>' + $words.slice(1).join(" ");
          $(this).html($modifiedText);
        });
      
      // July 2022 - Edited the ID so this now works
      $(once('findDegreeForm', 'form#asu-edu-components-find-my-degree-program', context))
        .each(function() {
          $(this).find('select', context).removeClass('custom-select');
        });
      
      // Aug 2022 - Degree search form - Remove the undecided option when grad is selected
      $('input#edit-standing-graduate').click(function() {
        // in person dropdown
        document.querySelector('select[name="interestarea"]').querySelectorAll('option').forEach(option => {
          // remove exploratory and undecided
          if ((option.value == '14' || option.value == '14%20')) {
            option.remove();
          }
        });
        // online dropdown
        document.querySelector('select[name="interestarea_online"]').querySelectorAll('option').forEach(option => {
          // remove undecided
          if (option.value == 'undecided') {
            option.remove();
          }
        });
      });

      // Aug 2022 - Degree search form - Add undecided option when undergrad selected and doesn't exist for in person and online dropdowns
      $('input#edit-standing-undergrad').click(function() {
        let intexp = document.querySelector('div.form-item-interestarea option[value="14"]');
        let intund = document.querySelector('div.form-item-interestarea option[value="14%20"]');
        
        // use insertAfter to place in correct order
        if (!intexp) {
          $('<option value="14">Exploratory</option>').insertAfter('option[value="21"]');
        }
        if (!intund) {
          $('select[name="interestarea"]').append('<option value="14%20">Undecided</option>').once;
        }
        
        let intund_online = document.querySelector('div.form-item-interestarea-online option[value="undecided"]');
        if (!intund_online) {
          $('select[name="interestarea_online"]').append('<option value="undecided">Undecided</option>').once;
        }
      });

      // July 2022 - Reset button for degree search
      $('input.button--reset').click(function() {
        $('input.search-it').removeAttr('disabled');
        // Aug 2022 - Fix reset for text field per WWWCMS-49
        $('select#edit-interestarea').val('_none').change();
      });
      
      // Aug 2022 - Reset text field when interest area is selected
      $('select#edit-interestarea').change(function() {
        let interest_val = $('select#edit-interestarea').val();
        if (interest_val != '_none') {
          $('input.search-it').val('').change();
        }
      });

      // July 2022 - Reload window when using back button to reset form
      $(window, context).on('pageshow', function (event) {          
        if (event.persisted) {
          window.location.reload();
        }
      });

      $(once('carouselUpdate', 'body.asu-home .block-inline-blockcarousel-image .image-carousel', context))
        .each(function() {
          var carousel = $(this).find('.glide');
          if (carousel.length === 1) {
            var glide = new Glide(carousel[0]).mount();
            glide.update({ autoplay: 3000, animationDuration: 1500 });
          }
        });

      $(once('quoteSourceLink', 'body.asu-home .block-inline-blockasu-edu-animated-quote .source', context))
        .each(function() {
          $(this).html(function () {
            return $(this).html().replace(
              'ASU charter',
              '<a href="https://www.asu.edu/about/charter-mission-and-values" ' +
              'data-ga-animated-quote = "asu charter" ' +
              '>' + Drupal.t('ASU charter') + '</a>'
            );
          });
          const animatedQuote = document.querySelectorAll('[data-ga-animated-quote]');
          animatedQuote.forEach((element) =>
            element.addEventListener('focus', () => {
              const text =  element.getAttribute('data-ga-animated-quote')
                .toLowerCase();
              const args = {
                event: 'link',
                action: 'click',
                name: 'onclick',
                type: 'internal link',
                region: 'main content',
                section: 'the asu difference',
                text,
              };
              pushGAEvent(args);
            })
          );
        });
    }
  };

} (jQuery, Drupal));
