(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.adminpanel = {
    attach: function (context, settings) {

      //------------------------------------
      // Edit Event Instance page

      // Close "Field inheritance" accordion on load since it is confusing.
      $("#field-inheritance--wrapper").removeAttr('open');

      // Hide Placeholder fields
      // $("#eventinstance-visit-event-edit-form > #edit-field-placeholder-display-title-wrapper").hide();
      // $("#eventinstance-visit-event-edit-form > #edit-field-placeholder-message-text-wrapper").hide();
      // $("#eventinstance-visit-event-edit-form > #edit-field-placeholder-for-descr-html-wrapper").hide();
      // $("#eventinstance-visit-event-edit-form > #edit-field-placeholder-for-addtour2-wrapper").hide();
      // $("#eventinstance-visit-event-edit-form > #edit-field-placeholder-for-barrett-wrapper").hide();
      // $("#eventinstance-visit-event-edit-form > #edit-field-parent-event-series-id-wrapper").hide();
      // $("#eventinstance-visit-event-edit-form > #edit-field-placeholder-num-of-reg-wrapper").hide();


      // Populate Month dropdown and select appropriate month from Month dropdown
      /**
       * populateMonthDropdown -- Similar function exists in asuaec_visit.js at line 498. Only last part of which month to select in dropdown is different.
       */
      function populateMonthDropdown() {
        let startDate = new Date(); // Start date(month) of the dropdown list
        //let startDate = new Date(2018, 11, 1); // This is for testing. Rememeber that JavaScript counts months from 0 to 11. January is 0. December is 11.
        let startMonth = startDate.getMonth();
        let new_month_options = [];
        let endDate = new Date(2023, 11, 15) ; // <-- Up to which month to display. Change month here and also in Evolution JS block at Line 24. JavaScript counts months from 0 to 11. January is 0. December is 11.
        let endMonth = endDate.getMonth();

        // Display from this month to endMonth
        let howManyMonth = 12; // Display 4 months by default
        if(endMonth >= startMonth) {
          howManyMonth = endMonth - startMonth + 1;
        } else if (startMonth > endMonth) {
          howManyMonth = (12 - startMonth) + endMonth + 1;
        }
        //console.log("howManyMonth", howManyMonth);
        howManyMonth = 12; //<---For Admin panel, just display 12 month.

        for (i = 0; i < howManyMonth; i++) {
          if(i == 0) {
            startDate.setMonth(startDate.getMonth());
          } else {
            // If it is 31st of March, May, August, October
            if(startDate.getDate() == 31 && (startDate.getMonth() == 2 || startDate.getMonth() == 4 || startDate.getMonth() == 7 || startDate.getMonth() == 9)) {
              startDate.setDate(startDate.getDate() + 30);
            }
            // If it is 31st of January
            else if(startDate.getDate() == 31 && startDate.getMonth() == 0) {
              startDate.setDate(startDate.getDate() + 28);
            }
            // If it is Jan 29-31
            else if((startDate.getDate() == 29 && startDate.getMonth() == 0) || (startDate.getDate() == 30 && startDate.getMonth() == 0) || (startDate.getDate() == 31 && startDate.getMonth() == 0)) {
              startDate.setDate(startDate.getDate() + 25);
            }
            // The rest
            else {
              startDate.setDate(startDate.getDate() + 31);
            }
          }
          //console.log(startDate.toLocaleDateString());
          new_month_options[i] = createStringYearMonth(startDate);

        } // END OF for (i = 0; i < howManyMonth; i++)
        //console.log("new month options", new_month_options);

        let options = '<option value="">Jump to month</option>';
        $.each(new_month_options, function( index, value ){
          //console.log( "index", index, "value", value );
          let myArray = value.split('-');
          // Prepare month name
          monthName = '';
          switch(myArray[1]) {
            case "01":
              monthName = "January";
              break;
            case "02":
              monthName = "February";
              break;
            case "03":
              monthName = "March";
              break;
            case "04":
              monthName = "April";
              break;
            case "05":
              monthName = "May";
              break;
            case "06":
              monthName = "June";
              break;
            case "07":
              monthName = "July";
              break;
            case "08":
              monthName = "August";
              break;
            case "09":
              monthName = "September";
              break;
            case "10":
              monthName = "October";
              break;
            case "11":
              monthName = "November";
              break;
            case "12":
              monthName = "December";
          }
          let valuewithouthypen = value.replace('-','');
          options += '<option value="' + valuewithouthypen + '">' + monthName + ' ' + myArray[0] + '</option>';
        });  // END OF $.each(new_month_options, function( index, value )
        // Remove all options from the select list
        $("select[name='new-month']").empty();
        // Add new options
        $("select[name='new-month']").html(options);

        // Grab calendar_timestamp from URL and select the month from the dropdown list.
        // Get URL param
        let urlParams = new URLSearchParams(window.location.search);
        let calendar_timestamp = urlParams.get('calendar_timestamp');
        // console.log("calendar_timestamp", calendar_timestamp);
        let calendar_timestamp_js = parseInt(calendar_timestamp) * 1000; // Need to do *1000 for JS timestamp.
        // console.log("calendar_timestamp_js", calendar_timestamp_js);

        // Convert calendar_timestamp_js to YYYYMM
        let date = new Date(calendar_timestamp_js);
        // console.log("date", date);
        let yyyy_hypen_mm = createStringYearMonth(date);
        // console.log("yyyy_hypen_mm", yyyy_hypen_mm);
        var yyyymm = yyyy_hypen_mm.replace(/-/g, "");
        // console.log("yyyymm", yyyymm);
        // Select the month from the dropdown.
        $("select[name='new-month'] option[value='" + yyyymm + "']").prop('selected', true);

      } // END OF function populateMonthDropdown()

      /**
       * createStringYearMonth -- Exact same function exists in asuaec_visit.js at line 478.
       *
       * Build YYYY-MM string
       */
      function createStringYearMonth(theDate) {

        let currentYear = theDate.getFullYear();
        let currentMonth = theDate.getMonth() + 1;
        let currentMonth_leadingzero = '';
        if(currentMonth < 10) {
          currentMonth_leadingzero = '0' + currentMonth.toString();
        } else {
          currentMonth_leadingzero = currentMonth;
        }
        let retValue = currentYear + '-' + currentMonth_leadingzero;
        // console.log("retValue", retValue)
        return currentYear + '-' + currentMonth_leadingzero;

      } // END OF function createStringYearMonth(theDate)


      //-------Next/Prev link -------//
      function adjustNextPrevLink(nextPrev) {
        let path_array = window.location.pathname.split("/");
        // console.log("path array:", path_array); // path_array[1] has adminpanel-calendar-expasu-tempe-v2.
        let href = '';
        if(nextPrev == 'next'){
          href = $('li.pager__item.pager__next > a').attr('href');
        } else if (nextPrev == 'prev') {
          href = $('li.pager__item.pager__previous > a').attr('href');
        }
        if(href.startsWith("?")) {
          let tempArray = href.split('&');
          // console.log('temmpArray 0:', tempArray[0]); // ?calendar_timestamp=1709276400
          // get timestamp
          let timestamp = Number(tempArray[0].split('=')[1]);
          let date = new Date(timestamp*1000); // Make it milisecond
          let month = date.getMonth();
          let month_prepared = month + 1;
          let month_prepared_final = '';
          if(month_prepared < 10) {
            month_prepared_final = '0' + month_prepared;
          } else {
            month_prepared_final = month_prepared;
          }
          let year = date.getFullYear();
          if(nextPrev == 'next'){
            $('li.pager__item.pager__next > a').attr('href', '/' + path_array[1] + '/' + year + month_prepared_final + href);
          } else if (nextPrev == 'prev') {
            $('li.pager__item.pager__previous > a').attr('href', '/' + path_array[1] + '/' + year + month_prepared_final + href);
          }
        }
      }


      //------ Main flow starts here --------//
      populateMonthDropdown();

      // Bring Prev/Next above calendar
      $('.calendar-view-pager').insertBefore($('.view-calendar'));
      // Move month dropdown after pager.
      $('#month-dropdown').insertBefore($('.calendar-view-pager'));

      adjustNextPrevLink('next');
      adjustNextPrevLink('prev');






    }
  };
})(jQuery, Drupal, drupalSettings);