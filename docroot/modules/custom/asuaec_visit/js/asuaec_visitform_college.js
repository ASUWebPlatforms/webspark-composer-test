(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.collegejs = {
    attach: function (context, settings) {


      /////////////////////////////////////////////////////////////
      // College JS
      /////////////////////////////////////////////////////////////

      // Get URL param
      var urlParams = new URLSearchParams(window.location.search);
      var source = urlParams.get('source');
      var int = urlParams.get('int');

      if (source != 'gradevent') { // When visitor came from ?source=gradevent, don't show College dropdown and Major dropdown. Changed on 9/14/2021.

        // Get campuses that were selected
        var visitsArray = JSON.parse(sessionStorage.getItem("visits"));
         // console.log("visitsArray:", visitsArray);

        // Clear dropdowns


        var campusArray = [];
        var campusesString = '';

        if(visitsArray != null) {

          for (i = 0; i < visitsArray.length; i++) {

            // Iterate object
            for (var key in visitsArray[i]) {

              //					console.log(key, visitsArray[i][key]);
              if(key == "campus"){
                campusArray[i] = visitsArray[i]['campus'];

                var theCampus = visitsArray[i]['campus'];
                switch(theCampus){
                  case 'Polytechnic':
                    theCampus = 'POLY';
                    break;
                  case 'West':
                    theCampus = 'WEST';
                    break;
                  case 'Tempe':
                    theCampus = 'TEMPE';
                    break;
                  case 'Downtown Phoenix':
                    theCampus = 'DTPHX';
                    break;
                  case 'ASU California Center in downtown L.A.':
                    theCampus = 'LOSAN';
                    break;
                } // END OF switch
                if(campusesString == '') {
                  campusesString = theCampus;
                } else {
                  campusesString += '|' + theCampus;
                }

              }
            } // END OF for (var key in visitsArray[i])

          } // END OF for (i = 0; i < visitsArray.length; i++)
          //		console.log("campusArray", campusArray);
          //		console.log("campusesString", campusesString);

        } // END OF if(visitsArray != null) {

        var interest = sessionStorage.getItem("interest");
        var person_type = sessionStorage.getItem("persontype");
        //console.log("interest: " + interest);
        //console.log("person type: " + person_type);

        var grad_ugrad = 'ugrad';
        if(person_type == "Graduate student") {
          grad_ugrad = 'grad';
        } else {
          grad_ugrad = 'ugrad';
        }

        // Display College/Major dropdown lists only for Grad
        if(person_type == "Graduate student") {

          if(campusArray.length > 0){

            var college_data = $("input[name='submitted[college]']").val();
            var major_data =  $("input[name='submitted[major]']").val();

            // check if the college field is empty
            if($('#dynamic-edit-submitted-1-college').length == 0) {
              $("input[name='college']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="college-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>Select college of interest</label><select id="dynamic-edit-submitted-1-college" class="custom-select form-select form-control" name="college1"></select></div>');
            }

            if($('#dynamic-edit-submitted-major').length == 0) {
              $("input[name='major']").before('<div class="js-form-item form-item js-form-type-select form-group"><label><svg id="major-required-svg" title="Required" class="svg-inline--fa fa-circle fa-w-16 uds-field-required" aria-labelledby="svg-inline--fa-title" data-prefix="fa" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title">Required</title><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path></svg>Select program of interest</label><select id="dynamic-edit-submitted-major" class="custom-select form-select form-control" name="degree1"></select></div>');
            }

            // fill the college options
           var collUrl = "/admin/asuaec_json/json/get_colleges_by_multi_campuses_and_interest/" + campusesString + "/" + interest + "/" + grad_ugrad;
            $("#dynamic-edit-submitted-1-college").removeOption(/./);
            $("#dynamic-edit-submitted-1-college").addOption({'0':'- Select college of interest -'});
            $("#dynamic-edit-submitted-1-college").ajaxAddOption(collUrl , null, false);

            // Bind
            // $("#dynamic-edit-submitted-1-college").bind("change", function(){ collegeChanged(); })
            //$("#dynamic-edit-submitted-1-college").once().on("change", function(){ collegeChanged(); })
            $(once('visitformcollegejs', "#dynamic-edit-submitted-1-college")).on('change', function() { // D10 change 
               collegeChanged(); 
            });
          }  // END of if(campus_data.length > 0)

        } // END OF if(person_type == "Graduate Student")





        function collegeChanged(){

          var dynamic_college_data = $("#dynamic-edit-submitted-1-college").val();

          // var degreeUrl = "/college-major/json?op=get_majors_by_multi_campuses_and_college&campus=" + campusesString + "&college=" + dynamic_college_data + "&op4=" + grad_ugrad;
          var degreeUrl = "/admin/asuaec_json/json/get_majors_by_multi_campuses_and_college/" + campusesString + "/" + dynamic_college_data + "/" + grad_ugrad;

          if( dynamic_college_data == 1){
            $("input[name='college']").val("");
            $("#dynamic-edit-submitted-major").attr("disabled", true);
          }

          else{

            $("input[name='college']").val( $("#dynamic-edit-submitted-1-college").val()); // Save the selected college in the webform "college" field
            // fill the degrees options
            $("#dynamic-edit-submitted-major").removeOption(/./);
            $("#dynamic-edit-submitted-major").addOption({'0':'- Select program of interest -'});
            $("#dynamic-edit-submitted-major").ajaxAddOption(degreeUrl , null, false);
            // $("#dynamic-edit-submitted-major").bind("change", function(){ degreeChanged(); })
            //$("#dynamic-edit-submitted-major").once().on("change", function(){ 
            $(once('visitformcollegejs', "#dynamic-edit-submitted-major")).on('change', function() { // D10 change   
              degreeChanged(); 
            });
          }
        }


        function degreeChanged(){
          if($("#dynamic-edit-submitted-major option:selected") == 0){
            $("input[name='major']").val("");
          }
          else{
            $("input[name='major']").val($ ("#dynamic-edit-submitted-major").val()); // Save the degees/major options in webform "major" field
          }
        }






        /////////////////////////////////////////////////////////////
        // Reload handling
        // For dynamic ajax dropdowns, prepopulate previously selected values that were entered before validation.
        /////////////////////////////////////////////////////////////

        function onbeforeunload() {
          // Get the current URL.
          var currentUrl = window.location.href;
          // Get the previous URL.
          var previousUrl = sessionStorage.getItem("previousUrl");

          // Check if the current URL is the same as the previous URL.
          if (currentUrl !== previousUrl) {
            // The page has not been reloaded.
            return null;
          } else {
            // The page has been reloaded.

            // Get the POST data from the form.
            var postData = $("#webform-submission-visit-form-node-24-add-form").serialize();
            // Split the POST data into an array.
            var dataArray = postData.split("&");

            // Loop through the data array and print each name-value pair.
            var visitor_type = '';
            var college = '';
            var major = '';
            var time_to_wait = 1000;
            for (var i = 0; i < dataArray.length; i++) {
              var nameValuePair = dataArray[i].split("=");
              // console.log(nameValuePair[0] + " = " + nameValuePair[1]);
              if(nameValuePair[0] == 'visitor_type') {
                visitor_type = decodeURI(nameValuePair[1]);
              }
              if(nameValuePair[0] == 'college') {
                college = decodeURI(nameValuePair[1]);
              }
              if(nameValuePair[0] == 'major') {
                major = decodeURI(nameValuePair[1]);
              }
            }
            if(visitor_type == "Graduate student") {
              // Fill college
              setTimeout(function(){
                // Fill college
                $("#dynamic-edit-submitted-1-college").selectOptions(college, true);
                $('#dynamic-edit-submitted-1-college').change();

                // Fill major
                setTimeout(function(){
                  $("#dynamic-edit-submitted-major").selectOptions(major, true);
                  $('#dynamic-edit-submitted-major').change();
                }, time_to_wait);
              }, time_to_wait);
            } // END OF if(type_of_visitor != "Graduate Student")

            return "The page has been reloaded.";
          }
        }
        onbeforeunload();
        // Set the previous URL.
        sessionStorage.setItem("previousUrl", window.location.href);


      } // END OF if (source != 'gradevent') {







    }
  };
})(jQuery, Drupal, drupalSettings);
