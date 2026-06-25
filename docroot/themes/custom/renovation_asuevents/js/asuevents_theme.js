/**
 * @file
 * ASU Events behaviors.
 */

jQuery(document).ready(function ($) {
  'use strict';

      // move quicktabs tabs to header
      $('.views-element-container .view-header').append($('.quicktabs-tabs'));

      // wrap quicktabs tabs in div
      $( "ul.quicktabs-tabs" ).wrap( "<div></div>" );

      // add View title before quicktabs tabs
      $( "ul.quicktabs-tabs" ).before( "<div class='qt-title'>View</div>" );

      // grid and list icons
      $('.quicktabs-loaded:contains("Grid")').html("<span class='grid-icon'><img src='/themes/custom/renovation_asuevents/assets/border-all-solid.svg' alt='Grid view icon'></span>");
      $('.quicktabs-loaded:contains("List")').html("<span class='list-icon'><img src='/themes/custom/renovation_asuevents/assets/list-ul-solid.svg' alt='List view icon'></span>");

      // Add class to filter title
      $('.formatted-text h3:contains("Filter your results")').addClass('filter-title');

      // skip to content point to search box
      $('a.nav-link:contains("Skip to main content")').replaceWith("<a class='nav-link sr-only sr-only-focusable' href='#edit-searchtext--hero'>Skip to main content</a>");

      // move date range fields into Date div, under relative date field
      $("fieldset#edit-eventdate-wrapper--2").appendTo("fieldset#edit-reldate--wrapper");

      // add clear filters link below apply filters button
      $( "#edit-actions" ).after( "<div class='clear-filters'><a href='/#results'>Clear all filters</a></div>" );

      // Check url for searchText value to add to search box
      const evurl = window.location.search;
      const searchbox = new URLSearchParams(evurl);
      var searchvalue = searchbox.get('searchText');
      // if searchvalue is null, set to empty string
      if (searchvalue == null) {
        searchvalue = '';
      }

      // add search box to hero section
      $('<form action="" method="get" id="views-exposed-form-events-grid-block-2" accept-charset="UTF-8" class="contextual-region" data-once="form-updated" data-drupal-form-fields="edit-searchtext--2,edit-searchtext--hero,edit-category-1401--2,edit-category-1400--2,edit-category-1402--2,edit-category-1403--2,edit-category-1404--2,edit-category-1405--2,edit-category-1406--2,edit-category-1407--2,edit-category-1408--2,edit-category-1409--2,edit-category-1410--2,edit-category-1411--2,edit-category-1412--2,edit-category-1413--2,edit-foryou-1388--2,edit-foryou-1391--2,edit-foryou-1389--2,edit-foryou-1392--2,edit-foryou-1393--2,edit-foryou-1394--2,edit-foryou-1395--2,edit-foryou-1396--2,edit-foryou-1397--2,edit-foryou-1390--2,edit-foryou-1398--2,edit-foryou-1399--2,edit-reldate-1--2,edit-reldate-2--2,edit-reldate-3--2,edit-eventdate-min--2,edit-eventdate-max--2,edit-location-2042--2,edit-location-1348--2,edit-location-777--2,edit-location-717--2,edit-location-2041--2,edit-location-1217--2,edit-location-101--2,edit-location-1052--2,edit-location-2043--2,edit-location-58--2,edit-location-1884--2,edit-location-1384--2,edit-location-236--2,edit-location-1886--2,edit-location-26--2,edit-location-108--2,edit-location-994--2,edit-location-143--2,edit-location-154--2,edit-location-2045--2,edit-reset--2"><div class="js-form-item form-item js-form-type-textfield form-item-searchtext-hero js-form-item-searchtext-hero form-group"><label for="edit-searchtext--hero">Search events by name or keyword</label><input placeholder="Football, graduation, etc." data-drupal-selector="edit-searchtext-hero" type="text" id="edit-searchtext--hero" name="searchText" value="' + searchvalue +'"  size="30" maxlength="128" class="form-control"><input data-drupal-selector="edit-submit-events-grid-2" type="submit" id="start-search" value="Search now" class="button js-form-submit form-submit btn-maroon btn btn-primary"></div></form>').appendTo(".uds-hero-md .content");

      // onclick event for search button, send value of search box to #edit-searchtext--2
      $('#start-search').click(function() {
        var searchValue = $('#edit-searchtext-hero').val();
        $('#edit-searchtext--2').val(searchValue);
      });

      // EVNTWEB-16 - Add reset "x" to search box
      $('input#edit-searchtext--hero').attr('type', 'search');

      // close for mobile
      if ($(window).width() < 768) {
        $('#start-search').addClass('btn-md');
        $('details.form-item').removeAttr('open');
        $('details.form-item summary').attr('aria-expanded', 'false');
        // In each details element, if any of it's child checkboxes is checked, open the details element
        $('details.form-item').each(function() {
          if ($(this).find('input[type="checkbox"]:checked').length > 0) {
            $(this).attr('open', 'open');
            $(this).find('summary').attr('aria-expanded', 'true');
          }
        });
      }

      // Change input tag type to date
      $('input#edit-eventdate-min').attr('type', 'date');
      $('input#edit-eventdate-max').attr('type', 'date');

      // Change labels for date range fields
      $('.form-item-eventdate-min label').text('Start date');
      $('.form-item-eventdate-max label').text('End date');

      // check url for queries and hide feature, set search box value to url value,
      // open date range, format and set pills

      if (window.location.href.indexOf("?") > -1) {
        $('.featured-event').addClass('hide-feature');
        //$('#edit-submit-events-grid--2').prop('disabled', false);

        // Search text pills
        const eventurl = window.location.search;
        const searchme = new URLSearchParams(eventurl);

        // Setup variables
        const searchtext = searchme.get('searchText');
        const mindate = searchme.get('eventDate[min]');
        const maxdate = searchme.get('eventDate[max]');

        // Set search box value to url value when query is present
        $('#edit-searchtext-hero').val(searchtext);

        // If min date or max date are present
        // open date range box, format and set date pills
        if (mindate || maxdate) {
          $('details#edit-reldate-collapsible').attr('open', '');
          $('details#edit-reldate-collapsible summary').attr('aria-expanded', 'true');

          if ((mindate && maxdate) && (mindate != maxdate)) {
            // convert datetime to M j, Y and fix one day offset
            const newmindate = new Date(mindate.replace(/-/g, '\/'));
            const mini = newmindate.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            const newmaxdate = new Date(maxdate.replace(/-/g, '\/'));
            const maxi = newmaxdate.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            // print the new formatted date in the pills
            $('.event-pills ul')
            .append('<li>' + mini + ' - ' + maxi + '</li>')
          }

          // Max date only or both match
          if (maxdate && (!mindate) || (mindate == maxdate)) {
            // convert datetime to M j, Y and fix one day offset
            const newmaxdate = new Date(maxdate.replace(/-/g, '\/'));
            const maxi = newmaxdate.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            $('.event-pills ul')
            .append('<li>' + maxi + '</li>')
          }

        }

        // Search text to gold header
        if (searchtext) {
          $('.block-inline-blocktext-content .uds-highlighted-heading h2 span.highlight-gold').html('Events matching &ldquo;' + searchtext + '&rdquo;');
        }

        // Add category pills
        if ($('input[type=checkbox]:checked').length > 0) {
          $('input[type=checkbox]:checked').each(function() {
            let value = $(this).attr('id');
            let checkText = $('label[for=' + value + ']').text();
            $('.event-pills ul').append('<li>' + checkText + '</li>');
          });
        }

      }
      // if no filters selected, add "none" to pills
      if ($('.event-pills ul li').length == 0) {
        $('.event-pills ul').append('<span>None</span>');
      }

      // change search button to btn-md for mobile
      if ($(window).width() < 768) {
        $('#edit-submit-search').addClass('btn-md');
      }

        // set input date type min value to now
        $('input#edit-eventdate-min').attr('min', new Date().toISOString().split("T")[0]);
        $('input#edit-eventdate-max').attr('min', new Date().toISOString().split("T")[0]);

       // #edit-actions on click event if only min date has value, copy that value to max date
       $('#edit-actions').click(function() {
        if ($('#edit-eventdate-min').val() && !$('#edit-eventdate-max').val()) {
          $('#edit-eventdate-max').val($('#edit-eventdate-min').val());
        } else {
          $('#edit-eventdate-max').val();
        }
      });

      // Attach a change event handler to #edit-eventdate-min
        $('#edit-eventdate-min').change(function() {
          var minDateValue = $(this).val();
          // Update #edit-eventdate-max 'min' attribute
          $('#edit-eventdate-max').attr('min', minDateValue);
      // Check if #edit-eventdate-max is empty and then set its value
        if (!$('#edit-eventdate-max').val()) {
          $('#edit-eventdate-max').val($(this).val());
        }
        else $('#edit-eventdate-max').val($('#edit-eventdate-min').val());
      });

      // #edit-actions on click event if only min date has value, copy that value to max date
      $('#edit-actions').click(function() {
        if ($('#edit-eventdate-min').val() && !$('#edit-eventdate-max').val()) {
          $('#edit-eventdate-max').val($('#edit-eventdate-min').val());
        } else {
          $('#edit-eventdate-max').val();
        }
      });

      // AP style for times
      $('.smart-date--time').each(function() {
        var aptime = $(this)[0].innerText;
        aptime = aptime.replaceAll('pm', 'p.m.').replaceAll('am', 'a.m.').replaceAll(':00', '');
        $(this).text(aptime);
      });

      // ICS fix for add to calendar
      $("a.myCal").each(function() {
        var href = $(this).attr('href');
        href = href.replace("text/calendar", "data:text/calendar");
        $(this).attr('href', href);
      });

});
