
/*(function($) {

  Drupal.behaviors.asu_courses = {
   attach: function () {
          const value = $('#export_page_div').parent('.progress__label').siblings('.progress__percentage').text();

		  if (value === '100%') {
			console.log('complete');  
			window.location.href = '/admin/content/download_courses';
		  } 
	   
   }
  }
 
})(jQuery, Drupal, drupalSettings);*/

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.exportDatajs = {
    attach: function (context, settings) {
		console.log(drupalSettings.asu_courses_export);
      const downloadUrl = drupalSettings.asu_courses_export.downloadUrl;
      const redirectUrl = drupalSettings.asu_courses_export.redirectUrl;
		console.log(downloadUrl);
      if (downloadUrl) {
        // Create an invisible iframe to trigger download
        $('<iframe>', {
          src: downloadUrl,
          style: 'display:none;'
        }).appendTo('body');
      }

      // Redirect after a few seconds
      setTimeout(() => {
        window.location.href = redirectUrl;
      }, 10000); // Adjust timing as needed
    }
  };
})(jQuery, Drupal, drupalSettings);


