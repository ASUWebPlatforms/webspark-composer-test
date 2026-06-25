(function ($) {

    $(document).ready(function(){
        $("#edit-group-event-location").change(function(){
            var campus_loc =$("#edit-field-property-location-shs-0-0").val();
            console.log('campus locaction - ',campus_loc);
            if(campus_loc == "1190"){                
                $("select[name='field_off_campus_visible']").val('yes').trigger('change');
            }else{
                $("select[name='field_off_campus_visible']").val('no').trigger('change');
            }
        });
    });

})(jQuery);