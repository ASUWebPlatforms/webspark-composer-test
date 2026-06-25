(function ($) {

    $(document).ready(function(){
        var icon_Class="";
        var requiredIcon = '<svg class="svg-inline--fa fa-circle uds-field-required '+icon_Class+'" aria-labelledby="svg-inline--fa-title-kKTFCg3ROTFI" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-kKTFCg3ROTFI">Required</title><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg> ';

        // Adding Required dot(.) icon for the "Title"
        if($("#node-event-registration-form-edit-form").find("#edit-title-0-value").length || $("#node-event-registration-form-form").find("#edit-title-0-value").length){
            var title_text = $('.form-label[for="edit-title-0-value"]').html();
            $('.form-label[for="edit-title-0-value"]').html(requiredIcon + title_text);
            $("#edit-title-0-value").prop('required',true);
        }
        /*
        if($("#in-person-attendance-info-sec fieldset").find(".form-required").length){
            var title_text = $('#in-person-attendance-info-sec fieldset .form-required').html();
            $('#in-person-attendance-info-sec fieldset .form-required').html(requiredIcon+ title_text);
        }

        if($("#general-audience-info-sec fieldset").find(".form-required").length){
            var title_text = $('#general-audience-info-sec fieldset .form-required').html();
            $('#general-audience-info-sec fieldset .form-required').html(requiredIcon+ title_text);
        }
        */
        // Adding Required dot(.) icon for the "Event Date & Time"
        icon_Class='required-dot';
        var requiredIcon = '<svg class="svg-inline--fa fa-circle uds-field-required '+icon_Class+'" aria-labelledby="svg-inline--fa-title-kKTFCg3ROTFI" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-kKTFCg3ROTFI">Required</title><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg> ';
        if($(".date_and_time_widget").find(".label.form-required").length){
            var title_text = $('.date_and_time_widget .label.form-required').html();
            $('.date_and_time_widget .label.form-required').html(requiredIcon+ title_text);
            
        }

              
        function checkValue(data){
            if((data*1) < 10){
                return "0"+data;
            }else{
                return data;
            }            
        }

        // Adding value for the "Event Date & Time"
        if($("#node-event-registration-form-form").find("#edit-title-0-value").length){
            let req_date = new Date().getTime()+(45*24*60*60*1000);
            var day = new Date(req_date);
            var date = day.getFullYear()+"-"+checkValue(day.getMonth()+1)+"-"+checkValue(day.getDate());
            var time = checkValue(day.getHours())+":"+checkValue(day.getMinutes())+":00";
            $("#edit-field-event-date-0-value-date").val(date);
            $("#edit-field-event-date-0-value-time").val(time);
            $("#edit-field-event-date-0-end-value-date").val(date);
            $("#edit-field-event-date-0-end-value-time").val(time);
            // alert("elenent found");
        }
    });

   

    //#audience-info-section fieldset .form-required

    // Hide login button in the homepage hero block 

    $(document).ready(function(){
        
        if($("#asuHeader").find('a[href="/caslogout"]').length)
        {
            if($(document).find('.block-inline-blockhero a[href="/cas"]').length){
                $('.block-inline-blockhero a[href="/cas"]').css("display","none");
            }
        }
    });

    $(document).ready(function(){
        $("#close-info-banner").on("click", function(){
            $(".ws2-banner-info").hide();
        });
    });

    $(document).ready(function(){
        
        $(".node--unpublished").removeClass('bg-white');
    });
    
    $(document).ready(function(){
        // $(".form-item-field-save-as-draft").css('display', 'none');
        var pub_status=$("select[name='moderation_state[0][state]']").val();
        if(pub_status == "draft"){
            $("select[name='field_save_as_draft']").val(1).trigger('change');
        }else{
            $("select[name='field_save_as_draft']").val(0).trigger('change');
        }
        $("select[name='moderation_state[0][state]']").change(function(){
            var pub_status=$("select[name='moderation_state[0][state]']").val();
            var draft = $("select[name='field_save_as_draft']").val();
            if(pub_status == "draft"){
                
                $("select[name='field_save_as_draft']").val(1).trigger('change');
            }else{
                
                $("select[name='field_save_as_draft']").val(0).trigger('change');
            }
        });
        
    });
    
    

})(jQuery);




(function ($) {
   
    $(document).ajaxComplete(function(event, xhr, settings) {
        // console.log(settings.url);
        if (settings.url === "/node/add/event_registration_form?ajax_form=1&_wrapper_format=drupal_ajax" || settings.url === "/node/add/event_registration_form?ajax_form=1&_wrapper_format=drupal_ajax&_wrapper_format=drupal_ajax") {
            // This code will run whenever an AJAX request to "url1" or "url2" completes
            // console.log("Request to " + settings.url + " finished.");
            var icon_Class='required-dot';            
            var requiredIcon = '<svg class="svg-inline--fa fa-circle uds-field-required '+icon_Class+'" aria-labelledby="svg-inline--fa-title-kKTFCg3ROTFI" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-kKTFCg3ROTFI">Required</title><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg> ';
            
            if($(".date_and_time_widget").find(".label.form-required").length){
                var title_text = $('.date_and_time_widget .label.form-required').html();
                $('.date_and_time_widget .label.form-required').html(requiredIcon+ title_text);
                
            }
        }
    });


  })(jQuery);

