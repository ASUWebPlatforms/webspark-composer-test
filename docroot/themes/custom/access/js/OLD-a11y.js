/**
 * JS for Webspark subtheme
 */
(function ($, Drupal) {

  Drupal.behaviors.access = {
    attach: function (context, settings) {
			
			/* theming audit result-report page */
			$('.entityform-audit .field-type-list-text .field-item').each(function(){
					if($(this).text() === 'Fail'){
						$(this).parent().parent().parent().parent().addClass('red');
					}
					if($(this).text() === 'Pass'){
						$(this).parent().parent().parent().parent().addClass('green');
					} 
			});

    	/* quicktabs pagination */
    	$('.quicktabs-tabpage').each(function(){
    		$('.block',this).append('<ul class="tablinks clearfix"><li class="first"><a class="tablink-prev" href="#">« Prev</a></li><li class="second"><a class="tablink-next" href="#">Next »</a></li></ul>')
    	});
        /* remove previous button on first tabbed content*/
    	$('.quicktabs-tabpage:first-child .block .tablinks li.first').remove();
        /* remove next button on last tabbed content*/
    	$('.quicktabs-tabpage:last-child .block .tablinks li.second').remove();

    /* change "#quicktabs-tab-white_paper-" to the href for your quicktabs links.*/
		$('.tablink-next').each(function(i){
			i++
			$(this).click(function(event){
    			event.preventDefault();
    			$('#quicktabs-tab-audit_quicktab-' + i).click();
    		})
		})
		$('.tablink-prev').each(function(i){
			i++
			var prev = i-1;
			$(this).click(function(event){
    			event.preventDefault();
    			$('#quicktabs-tab-audit_quicktab-' + prev).click();
    		})
		})
		
		$(".month-calendar .date-prev a:contains('« Prev')").html("‹");
		$(".month-calendar .date-next a:contains('Next »')").html("›");
		$( ".field-name-field-calendar-audience ul li:not(:last)" ).append( ", " );

			
		}
	}
	
})(jQuery, Drupal);
