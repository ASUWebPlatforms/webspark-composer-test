(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.faqs = {
    attach: function (context, settings) {
		
	  //Code addded by Archana - 12/05/24
	  if ($('#views-exposed-form-faqs2-block-1').has('.uds-form').length === 0) {
  			$('#views-exposed-form-faqs2-block-1').addClass('uds-form');
	  }
            
      //Code addded by Archana - 12/05/24
      if ($('#views-exposed-form-faqs2-block-1').has('.uds-form').length === 0) {
          $('#views-exposed-form-faqs2-block-1').addClass('uds-form');
      }

      // Accordion for "Types of loans"
      $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-header > legend').wrap("<h5>");
      $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-header > h5 > legend > span.fieldset-legend').wrap('<a aria-controls="accordion-content-topics" aria-expanded="true" class="" data-ga="This card unfolds" data-ga-event="collapse" data-ga-name="onclick" data-ga-region="main content" data-ga-section="default" data-ga-type="click" data-target="#accordion-content-topics" data-toggle="collapse" href="#accordion-content-topics" id="card" role="button">');
      $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-header > h5 > legend > span.fieldset-legend').wrap('<a aria-controls="accordion-content-topics" aria-expanded="true" class="" data-ga="This card unfolds" data-ga-event="collapse" data-ga-name="onclick" data-ga-region="main content" data-ga-section="default" data-ga-type="click" data-target="#accordion-content-topics" data-toggle="collapse" href="#wrap1" id="card" role="button">');
      //$('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-header > h5 > legend > a').once().append("<svg class=\"svg-inline--fa fa-chevron-up fa-w-14\" aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon=\"chevron-up\" role=\"img\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" data-fa-i2svg=\"\"><path fill=\"currentColor\" d=\"M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z\"></path></svg>");
	  $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-header > h5 > legend > a').each(function () {
	    if ($(this).find('svg.fa-chevron-up').length === 0) {
			$(this).append("<svg class=\"svg-inline--fa fa-chevron-up fa-w-14\" aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon=\"chevron-up\" role=\"img\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 448 512\" data-fa-i2svg=\"\"><path fill=\"currentColor\" d=\"M240.971 130.524l194.343 194.343c9.373 9.373 9.373 24.569 0 33.941l-22.667 22.667c-9.357 9.357-24.522 9.375-33.901.04L224 227.495 69.255 381.516c-9.379 9.335-24.544 9.317-33.901-.04l-22.667-22.667c-9.373-9.373-9.373-24.569 0-33.941L207.03 130.525c9.372-9.373 24.568-9.373 33.941-.001z\"></path></svg>");
		}
	  });

      $('fieldset[data-drupal-selector="edit-field-topic-target-id"]').addClass('card-foldable group1');
      $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-body').addClass('collapse show');
      $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-body').attr('id', 'accordion-content-topics');

      $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-header').click(function() {

        $('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-body').toggle();
        if($('fieldset[data-drupal-selector="edit-field-topic-target-id"] > .card-body:visible').length != 0)
        {
          $(this).addClass('show');
        }
        else
        {
          $(this).removeClass('show');
        }

      });

      // Move description ("Have a question we don't cover?") out of accordion
      var $description = $('fieldset[data-drupal-selector="edit-field-topic-target-id"] small');
      $description.parent().after($description);

      $('small').addClass('group1 mb-2');
      //$('.group1').once().wrapAll('<div id="wrap1"/>');
		const elements = document.querySelectorAll('.group1');
	  if (elements.length && !document.getElementById('wrap1')) {
			const wrapper = document.createElement('div');
			wrapper.id = 'wrap1';
			elements[0].parentNode.insertBefore(wrapper, elements[0]);
			elements.forEach(el => wrapper.appendChild(el));
	  }	

      // How many checked
      var n = $("input:checked").length;
      // Add "Filters"
      //$('#wrap1').once().prepend("<h3 class='mt-0'>Filters <span id='numOfChecked'>(" + n + ")</span></h3>");
	  if ($('#wrap1').find('h3.mt-0').length === 0) {
		  $('#wrap1').prepend("<h3 class='mt-0'>Filters <span id='numOfChecked'>(" + n + ")</span></h3>");
	  }	
		
      // Styling purpose
      $('small').removeClass('text-muted');

      // Added on 7/30/2024
      $(document).ready(function() {
        $(window).one('load', function() {
          // Get URL param: tid and filter
          var urlParams = new URLSearchParams(window.location.search);
          // Select "Payments and billing" when URL param is passed: ?tid=117 --- Added on 7/30/2024.
          if(urlParams.has("tid")) {
            // Get a specific parameter value
            let tid = urlParams.get('tid');
            $('input[name="field_topic_target_id[' + tid + ']"]').prop('checked', true).trigger('change');
          }

          // Add text to keyword search when URL param is passed: ?filter=how%20do%20I%20submit%20my%20required%20sponsorship%20documentation&opennid=3286
          if(urlParams.has("filter")) {
            // Get a specific parameter value
            let filter = urlParams.get('filter');
            $('input[name="title"]').val(filter).trigger('change');
          }

          // Lastly, open the accordion.
          if(urlParams.has("opennid")) {
            // Get a specific parameter value
            var opennid = urlParams.get('opennid');

            $( document ).on( "ajaxComplete", function( event, request, settings ) {
              //opennid = urlParams.get('opennid');
              if(!$('#accordion-content-3286').hasClass('show')) {
                $('a[aria-controls="accordion-content-' + opennid + '"]').get(0).click();
              }
            } );
          }
        });
      });

    }
  };
})(jQuery, Drupal, drupalSettings);