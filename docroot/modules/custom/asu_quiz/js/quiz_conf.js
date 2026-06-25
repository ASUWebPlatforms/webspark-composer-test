(function($) {

    Drupal.behaviors.svgtoogle_conf = {
        attach: function() {
            var flag2 = true;
            var vflag = true;
           /* if (flag2) {
                $('#quiz_image_banner').stop().delay(1000).animate({
                    'opacity' : 0,
                    queue: 'my-animation'
                }).dequeue('my-animation');
                flag2 = false;
            }*/
            $('.quiz_conf_h1_div').delay(1300).fadeIn();
            if (vflag) {
                $('#quiz_conf_top_conatiner').animate({ 'marginTop': '-180px' }, 1500, 'linear');
                $('.quiz_conf_h1_div').animate({ 'opacity': 0 }, 1300, 'linear');

                vflag = false;
            }


            //If persona results precentage is 0, then hide that div
            $('.personality').each(function(){
                var title_data = $(this).attr('data-original-title');
                if (title_data == '0%') {
                    $(this).children('.percent_value').html('');
                    $(this).attr('data-original-title','');
                    $(this).attr('data-title','0%');
                    $(this).css({'color': '#808080'});
                }
            });


            //Add extra css to each persona type div

            if ($('#focused_futurist').attr('data-title') != '0%') {
                $('#focused_futurist').parent('div').css({'border-top': '3px #FF7F32 solid'});
            }

            if ($('#deep_diver').attr('data-title') != '0%') {
                $('#deep_diver').parent('div').css({'border-top': '3px #00A3E0 solid'});
            }

            if ($('#trailblazer').attr('data-title') != '0%') {
                $('#trailblazer').parent('div').css({'border-top': '3px #FFC627 solid'});
            }

            if ($('#natural_networker').attr('data-title') != '0%') {
                $('#natural_networker').parent('div').css({'border-top': '3px #8C1D40 solid'});
            }

            if ($('#superfan').attr('data-title') != '0%') {
                $('#superfan').parent('div').css({'border-top': '3px #78BE20 solid'});
            }
			$('#trailblazer').hover(function(event){
			//$(document).on('mouseover','#trailblazer',function(){
					 $('svg').find(".donut-segment:not('.trailblazer')").attr("stroke", "#d2d3d4");
                    $(this).siblings().stop().animate({opacity: 0.4}, 100);
                },

                function(event) {
                    $('svg').find(".donut-segment:not('.trailblazer')").fadeOut(0, function() {
                        $('svg').find(".focused_futurist").attr("stroke", "#FF7F32");
                        $('svg').find(".natural_networker").attr("stroke", "#8C1D40");
                        $('svg').find(".deep_diver").attr("stroke", "#00A3E0");
                        $('svg').find(".superfan").attr("stroke", "#78BE20");
                        // $(this).siblings().stop().animate({opacity: 1}, 300);
                        $(this).unbind(event);
                    }).fadeIn(0);
                    return false;
                });



            $('#focused_futurist').hover(function(event){
					console.log('hello div');
                    $('svg').find(".donut-segment:not('.focused_futurist')").attr("stroke", "#d2d3d4");
                    $(this).siblings().stop().animate({opacity: 0.4}, 100);
                    // $(this).unbind(event);
                },

                function(event) {
                    $('svg').find(".donut-segment:not('.focused_futurist')").fadeOut(0, function() {
                        $('svg').find(".natural_networker").attr("stroke", "#8C1D40");
                        $('svg').find(".deep_diver").attr("stroke", "#00A3E0");
                        $('svg').find(".superfan").attr("stroke", "#78BE20");
                        $('svg').find(".trailblazer").attr("stroke", "#FFC627");
                        $(this).unbind(event);
                    }).fadeIn(0);
                    return false;
                }
            );


            $('#deep_diver').hover(function(event){

                    $('svg').find(".donut-segment:not('.deep_diver')").attr("stroke", "#d2d3d4");
                    $(this).siblings().stop().animate({opacity: 0.4}, 100);
                    // $(this).unbind(event);
                },

                function(event) {
                    $('svg').find(".donut-segment:not('.deep_diver')").fadeOut(0, function() {
                        $('svg').find(".natural_networker").attr("stroke", "#8C1D40");
                        $('svg').find(".focused_futurist").attr("stroke", "#FF7F32");
                        $('svg').find(".superfan").attr("stroke", "#78BE20");
                        $('svg').find(".trailblazer").attr("stroke", "#FFC627");
                        $(this).unbind(event);
                    }).fadeIn(0);
                    return false;
                }
            );

            $('#natural_networker').hover(function(event){

                    $('svg').find(".donut-segment:not('.natural_networker')").attr("stroke", "#d2d3d4");
                    $(this).siblings().stop().animate({opacity: 0.4}, 100);
                    // $(this).unbind(event);
                },
                function(event) {
                    $('svg').find(".donut-segment:not('.natural_networker')").fadeOut(0, function() {
                        $('svg').find(".deep_diver").attr("stroke", "#00A3E0");
                        $('svg').find(".focused_futurist").attr("stroke", "#FF7F32");
                        $('svg').find(".superfan").attr("stroke", "#78BE20");
                        $('svg').find(".trailblazer").attr("stroke", "#FFC627");
                        $(this).unbind(event);
                    }).fadeIn(0);
                    return false;
                }
            );


            $('#superfan').hover(function(event){

                    $('svg').find(".donut-segment:not('.superfan')").attr("stroke", "#d2d3d4");
                    $(this).siblings().stop().animate({opacity: 0.4}, 100);
                    // $(this).unbind(event);
                },
                function(event) {
                    $('svg').find(".donut-segment:not('.superfan')").fadeOut(0, function() {
                        $('svg').find(".deep_diver").attr("stroke", "#00A3E0");
                        $('svg').find(".focused_futurist").attr("stroke", "#FF7F32");
                        $('svg').find(".natural_networker").attr("stroke", "#8C1D40");
                        $('svg').find(".trailblazer").attr("stroke", "#FFC627");
                        $(this).unbind(event);
                    }).fadeIn(0);
                    return false;
                }
            );



            //code to add place holder to select options
            //term field
            $('#edit-submitted-start-term').prepend('<option disabled="" selected="" value="0">Start term</option>');
            $("#edit-submitted-start-term option[value='']").remove();

            //student type field
            $('#edit-submitted-student-type').prepend('<option disabled="" selected="" value="0">I am a..</option>');
            $("#edit-submitted-student-type option[value='']").remove();

            //country field
            $('#edit-submitted-country').prepend('<option disabled="" selected="" value="0">Country</option>');
            $("#edit-submitted-country option[value='']").remove();

            /** code to hide show Quiz RFI form fields based on user selections **/
            var ffu = $('input[name="first_name_from_url"]').val();
            //console.log(ffu);
            $('#edit-info').addClass('hide');
            $('#edit-processed-text-02').addClass('hide');
            $('#edit-actions').addClass('hide');
            //console.log(ffu.length);
            $(document).on('change','input[name="do_the_above_results_sound_like_you_"]', function(){
                var change_val = $(this).val();
                console.log(change_val);
                if(ffu.length === 0) {

                    if ((change_val == "Yes") || (change_val == "100")) {
                        $('#edit-info').removeClass('hide');
                        $('#edit-processed-text-02').removeClass('hide');
                        $('#edit-actions').removeClass('hide');
                    }
                    if (change_val == "No") {
                        $('#edit-info').addClass('hide');
                        $('#edit-processed-text-02').addClass('hide');
                        $('#edit-actions').addClass('hide');
                    }
                }
                else{
                    console.log(change_val);
                    if ((change_val == "Yes") || (change_val == "100")) {
                        //$('#edit-info').removeClass('hide');
                        $('#edit-processed-text-02').removeClass('hide');
                        $('#edit-actions').removeClass('hide');
                    }
                    if (change_val == "No") {
                        $('#edit-info').addClass('hide');
                        $('#edit-processed-text-02').removeClass('hide');
                        $('#edit-actions').addClass('hide');
                    }

                }
            });



            /** quiz rfi form code **/
            $('#webform-submission-quiz-rfi-form-add-form').find('.form-actions').hide();
            var initial_persona_match = $('input[type=radio][name="do_the_above_results_sound_like_you_"]:checked').val();
            if(initial_persona_match != null){
                show_submit_button(initial_persona_match);
            }
            $('input[type=radio][name="do_the_above_results_sound_like_you_').change(function() {
                var changed_value = $(this).val();
                show_submit_button(changed_value);
            });

            function show_submit_button(changed_value){
                if((changed_value == "100") || (changed_value == "Yes")){
                    $('#webform-submission-quiz-rfi-form-add-form').find('.form-actions').show();
                }
                else{
                    $('#webform-submission-quiz-rfi-form-add-form').find('.form-actions').hide();
                }
            }

            var persona_value = Cookies.get('persona');
            var persona = '';

            if(persona_value == "deep_diver"){
                persona = "deep diver";
            }
            if(persona_value == "trailblazer"){
                persona = "trailblazer";
            }
            if(persona_value == "focused_futurist"){
                persona = "focused futurist ";
            }
            if(persona_value == "natural_networker"){
                persona = "natural networker";
            }
            if(persona_value == "superfan"){
                persona = "superfan";
            }
            $('.quiz-conf-persona').text(persona);

            /** code to add class to hero image container to make it full screen width */
            $('.rfi_conf_image').parents('.container').addClass('rfi_top_container');
        }
    };
})(jQuery);