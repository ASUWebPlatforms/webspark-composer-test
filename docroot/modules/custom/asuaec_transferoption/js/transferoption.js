(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.transferoption = {
    attach: function (context, settings) {
      // Add Agreements in Degree detail page.

      // Get base URL/domain
      var baseUrl   = window.location.origin;   // Returns base URL (https://example.com)
      // Get AcadPlan (degree code) from URL
      var url      = window.location.href;     // Returns full URL (https://example.com/path/example.html)
      url = url.split("/");
      var acadPlan = url[5];
      // Get Community college
      var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
          sParameterName = sURLVariables[i].split('=');

          if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
          }
        }
        return false;
      };
      var commCollege = getUrlParameter('comm-college');

      // Ajax
      var agreementtable = '';
      $.ajax({
        type: "POST",
        url: baseUrl + '/views/ajax',
        cache: false,
        data: {
          view_name: 'transfer_option_search',
          view_display_id: 'block_2', //your display id
          view_args: acadPlan + '/' + commCollege, // your views arguments. Key contains campus.
        },
        dataType: "json",
        async: true,

        success: function (response) {
          if (response[3] !== undefined) {

            // Contains Body of the Campus description node
            agreementtable = '<section id="agreement-table"><h2><span class="highlight-gold">Major Map</span></h2>' +
                '<p>A major map outlines the degree’s requirements for graduation.</p>' +
                '<p>Course Plan for Pathway Programs</p>' +
                '<p>Click on the academic year that you signed the pathway agreement. This will take you to the course plan that provides a list of required courses for graduation. If you have not already signed up for the pathway program, please complete the sign-up form and submit it to your community college advisor.</p>' +
                response[3].data + '</section>';

          } // END OF if (response[3] !== undefined)
        } // END OF success: function (response)

      }); // END OF $.ajax

      $(document).ajaxStop(function () {
// Comment it out on 5/9/2022. This was the cause of the issue! Issue: Degree detail page intermittently fails to load.
//        do {
//          // Wait -- Make sure the React finishes running.
//        } while (!$('section.intro').length);
        if ($('#agreement-table').length == 0) {
          $(agreementtable).insertAfter($('section.intro'));
        }

      });

    }
  };
})(jQuery, Drupal, drupalSettings);