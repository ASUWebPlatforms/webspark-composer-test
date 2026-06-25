(function (Drupal, $, once) {
  Drupal.behaviors.youhaveselected = {
    attach(context) {

      // Global
      schedulingYourTourPath = '/scheduling-your-tour';
      additionalCampusPath = '/additional-campus';
      evolutionFormPath = '/registration-form';
      evolutionOtherFormPath = '/registration-form-0';

      visitsArray = [];

      visitsArray = JSON.parse(sessionStorage.getItem("visits"));
      // console.log("visitsArray", visitsArray);

      //$(window).once().on('load' , function(){            
      // Can not use `window` or `document` directly. 
      if (once('off-canvas', 'html').length) { // D10 change
        $(window).on('load', function() { 
          theMain();
        });
      }

      // Bind 'Search' button
      //$("#search").once().on("click", function(){ 
      $(once('youhaveselectedjs', "#search", context)).each(function () { // D10 change  
        $(this).on("click", function(){
          searchClicked2();
        });
      });  

      // Main flow
      function theMain() {
        // If personType is not set in JS Storage, clear JS Storage.
        personType = sessionStorage.getItem("persontype");
        if(personType == null) {
          sessionStorage.clear();
        }

        visitsArray = JSON.parse(sessionStorage.getItem("visits"));
        //console.log("visitsArray", visitsArray);
        if(visitsArray == null) {
          visitsArray = [];
        }

        // If in the following pages and if there is no selected campus tour yet, take it back to "Scheduling Your Tour" page.
        if(window.location.pathname == additionalCampusPath || window.location.pathname == evolutionFormPath || window.location.pathname == evolutionOtherFormPath) {
          if(visitsArray.length == 0) {
            window.location.href = schedulingYourTourPath;
          }
        }

        if(visitsArray.length > 0) {
          $('#result').html("<p><strong>You have selected the following:</strong></p>");
        } else { // If visitsArray is empty, clear the output of "You have selected..."
          $('#result').html('');
        }

        for (i = 0; i < visitsArray.length; i++) {
          var output2 = '';
          var output_addtours = '';
          var output_addtours_barrett = '';
          var addToursArrayOfObj = [];
          var eventtype = '';

          // Iterate object
          for (var key in visitsArray[i]) {
            //console.log(key, visitsArray[i][key]);

            //------- Event type ---------------//
            if(key == 'eventtype') {
              eventtype = visitsArray[i]['eventtype'];
            }

            //------- Additional tour ----------//
            addtourArray = [];
            if(key == 'addtour') {
              for(j = 0; j < visitsArray[i]['addtour'].length; j++) {
                addtourArray.push(visitsArray[i]['addtour'][j]);
              } // END OF for(j = 0; j < visitsArray[i]['addtour'].length; j++)
            } // END OF if(key == 'addtour')

            var myArray = [];
            for(k = 0; k < addtourArray.length; k++) {

              // Get Entity ID
              myArray = addtourArray[k].split("|");
              // console.log("myArray:", myArray);

              //Then read the values from the array where 0 is the first array element
              let theeventid = myArray[0]
              let thestarttimestamp = myArray[1];
              let theendtimestamp = myArray[2];
              let thedisplaytitle = myArray[3];

              let date_start = new Date((thestarttimestamp - 7*60*60)*1000).toLocaleTimeString('en-US', {timeZone: 'UTC', hour: '2-digit', minute:'2-digit'}).replace(/(:\d{2}| )$/, "").toLowerCase();
              let formattedTime_start = date_start;
              // console.log("formattedTime_start:", formattedTime_start);

              // End time
              let date_end = new Date((theendtimestamp - 7*60*60)*1000).toLocaleTimeString('en-US', {timeZone: 'UTC', hour: '2-digit', minute:'2-digit'}).replace(/(:\d{2}| )$/, "").toLowerCase();
              //var formattedTime_end = formatAMPM(date_end);
              let formattedTime_end = date_end;
              // console.log("formattedTime_end:", formattedTime_end);

              // addToursObj[myArray[0]] = myArray[1] + '|' + displaytitle + ' - ' + formatTime(formattedTime_start, formattedTime_end);
              addToursArrayOfObj.push({eventid: theeventid, starttimestamp: thestarttimestamp, output: 'Optional session &mdash; ' + thedisplaytitle + ' - ' + formatTime(formattedTime_start, formattedTime_end)});

            } // END OF for(k = 0; k < addtourArray.length; k++)
           // console.log("addToursArrayOfObj");
           // console.log(addToursArrayOfObj);


            //--------- Barrett Tour under Exp ASU ----------//
            addtourBarrettArray = [];
            if(key == 'addtour_barrett') {
              for(j = 0; j < visitsArray[i]['addtour_barrett'].length; j++) {
                addtourBarrettArray.push(visitsArray[i]['addtour_barrett'][j]);
              } // END OF for(j = 0; j < visitsArray[i]['addtour'].length; j++)
            } // END OF if(key == 'addtour_barrett')
            // console.log("addtourBarrettArray:", addtourBarrettArray);

            var myArray2 = [];
            for(k = 0; k < addtourBarrettArray.length; k++) {
              myArray2 = [];
              let theeventid2 = '';
              let thestartTimestamp2 = '';
              let theendTimestamp2 = '';
              let thedisplaytitle2 = '';

              // Get Start time and End time based on barrett1|52300|1518474600|1518479100
              myArray2 = addtourBarrettArray[k].split("|");
              // console.log("myArray2:", myArray2);

              //Then read the values from the array where 0 is the first
              theeventid2 = myArray2[0]
              thestartTimestamp2 = myArray2[1];
              theendTimestamp2 = myArray2[2];
              thedisplaytitle2 = myArray2[3]; // Barrett custom title - Added on 4/1/2022

              // Start time

              // Create a new JavaScript Date object based on the timestamp
              // multiplied by 1000 so that the argument is in milliseconds, not seconds.

              let date_start = new Date((thestartTimestamp2 - 7*60*60)*1000).toLocaleTimeString('en-US', {timeZone: 'UTC', hour: '2-digit', minute:'2-digit'}).replace(/(:\d{2}| )$/, "").toLowerCase();
              let formattedTime_start = date_start;
              // console.log("formattedTime_start:", formattedTime_start);

              // End time
              let date_end = new Date((theendTimestamp2 - 7*60*60)*1000).toLocaleTimeString('en-US', {timeZone: 'UTC', hour: '2-digit', minute:'2-digit'}).replace(/(:\d{2}| )$/, "").toLowerCase();
              //var formattedTime_end = formatAMPM(date_end);
              let formattedTime_end = date_end;
              // console.log("formattedTime_end:", formattedTime_end);

              // addToursObj[myArray2[1]] = myArray2[2] + '|' + thedisplaytitle2 + " - " + formatTime(formattedTime_start, formattedTime_end);
              addToursArrayOfObj.push({eventid: theeventid2, starttimestamp: thestartTimestamp2, output: 'Optional session &mdash; ' + thedisplaytitle2 + " - " + formatTime(formattedTime_start, formattedTime_end)});

            } // END OF for(k = 0; k < addtourBarrettArray.length; k++)
            // console.log("addToursObj:", addToursObj); //<--- Include both Additional tour and Barrett under Exp ASU.

          } // END OF for (var key in visitsArray[i])

          // console.log("addToursArrayOfObj:", addToursArrayOfObj); //<--- Include both Additional tour and Barrett under Exp ASU.
          // Sort chronologically
          addToursArrayOfObj.sort(function(a,b){
            return a.starttimestamp - b.starttimestamp;
          });

          var output_addtours_all = '';
          var formattedTime = '';
          var eventDescription = '';
          addToursArrayOfObj.forEach(function(item, index) {
            output_addtours_all += item.output + '<br />';
          });
          
          let campus_display_name = visitsArray[i]['campus'];
          if(campus_display_name == 'West') {
            campus_display_name = 'West Valley'; // Changed on 10/23/2023.
          }

          //      console.log("vdate:", visitsArray[i]['vdate']);
          //      console.log("timestamp:", visitsArray[i]['timestamp']);
			
			
		  // Make the date in AZ time - 7/18/2024

		  let thedate = new Date(visitsArray[i]['timestamp'] * 1000);
		  let options = { 
							timeZone: 'America/Phoenix',
							timeZoneName: "short",
							month: 'long', 
							day: 'numeric',
							year: 'numeric'
						};
		  let formattedDate = thedate.toLocaleString('en-US', options); 
		  let formattedDateFinal = formattedDate.substring(0, formattedDate.length - 6)
			
          //output2 += "<div class='campus-visit'><div class='campus-visit-outerinnerwrap'><strong><span class='maroon'>" + campus_display_name + " campus</span> for " + formatDate(visitsArray[i]['timestamp']) + "</strong><br />"; // Fixed Timezone issue on 7/18/2024.
          output2 += "<div class='campus-visit'><div class='campus-visit-outerinnerwrap'><strong><span class='maroon'>" + campus_display_name + " campus</span> for " + formattedDateFinal + "</strong><br />";
          output2 += "<div class='campus-visit-innerwrap ml-2'>";

          // Time format
          //      console.log("from time testing: ", visitsArray[i]['from']);
          //      console.log("to time testing: ", visitsArray[i]['to']);
          formattedTime = formatTime(visitsArray[i]['from'], visitsArray[i]['to']);
          // console.log("eventdisplaytitle:", visitsArray[i]['eventdisplaytitle']);
          // output2 += visitsArray[i]['eventdisplaytitle'] + ' - ' + formattedTime + '<br />';
          output2 += visitsArray[i]['eventdisplaytitle'];
          // For Self-guided, don't display time
          if(eventtype != 'Self-guided campus Tour') {
            output2 += ' - ' + formattedTime;
          }
          output2 += '<br />';

          output2 += output_addtours_all;
          output2 += eventDescription; // Added on 10/2/2020
          output2 += '</div>'; // END of <div class='campus-visit-innerwrap'>
          output2 += '</div>'; // END of <div class='campus-visit-outerinnerwrap'>
          // Delete button
          output2 += "<div class='campus-visit-delete'>";
          var theCampus = '';
          theCampus = visitsArray[i]['campus'];
          if(theCampus == "Downtown Phoenix") {
            theCampus = "Downtown-Phoenix";
          }
          output2 += "<button class='btn-gray btn btn-md mt-1 " + theCampus + "' id='delete-" + theCampus + "'>Delete</button>"; // Added id on 10/13/2020.
          output2 += "</div>"; // END of <div class='campus-visit-delete'>
          output2 += '</div>'; // END of <div class='campus-visit'>

          $('#result').append(output2);

        } // END OF for (i = 0; i < visitsArray.length; i++)

        // Bind 'Delete' button
        $(".campus-visit-delete .btn.Tempe").on("click", function(){ deleteClicked('Tempe'); });
        $(".campus-visit-delete .btn.West").on("click", function(){ deleteClicked('West'); });
        $(".campus-visit-delete .btn.Polytechnic").on("click", function(){ deleteClicked('Polytechnic'); });
        $(".campus-visit-delete .btn.Downtown-Phoenix").on("click", function(){ deleteClicked('Downtown-Phoenix'); });

        // Check sessionStorage
        var limit = 1024 * 1024 * 5; // 5 MB
        var remSpace = limit - unescape(encodeURIComponent(JSON.stringify(sessionStorage))).length;
        //console.log("remSpace: ", remSpace);

      } // END OF theMain()


      function searchClicked2() {
        theMain();
      }

      // Delete a campus visit from sessionStorage
      function deleteClicked(campus) {
//      console.log("campus:", campus);
//      console.log("visitsArray", visitsArray);
        if(campus == "Downtown-Phoenix"){
          campus = "Downtown Phoenix";
        }

        // Delete the campus in sessionStorage
        for(var i=0; i < visitsArray.length; i++) {
          if(visitsArray[i].campus == campus)
          {
            visitsArray.splice(i,1);
          }
        }
        // Save the updated VisitsArray to sessionStorage
        sessionStorage.setItem('visits', JSON.stringify(visitsArray));

        if((window.location.pathname == additionalCampusPath) || (window.location.pathname == evolutionFormPath) || (window.location.pathname == evolutionOtherFormPath)) {
          // Reload the page or redirect to Scheduling a Tour page
          if(visitsArray.length > 0) {
            // theMain();
            location.reload();
          } else {
            window.location.href = schedulingYourTourPath;
          }

        } else {
          // theMain();
          location.reload();
        }
      } // END OF deleteClicked()


      // Returns String "February 25, 2018"
      function formatDate(timestamp) {
        // Parameter dateString has YYYY-MM-DD.
        var visitDateObj = new Date(timestamp*1000);
        //console.log('visitDateObj', visitDateObj);

        var month = new Array();
        month[0] = "January";
        month[1] = "February";
        month[2] = "March";
        month[3] = "April";
        month[4] = "May";
        month[5] = "June";
        month[6] = "July";
        month[7] = "August";
        month[8] = "September";
        month[9] = "October";
        month[10] = "November";
        month[11] = "December";
        var visitMonth = month[visitDateObj.getMonth()];
        var visitDate = visitDateObj.getDate();
        var visitYear = visitDateObj.getFullYear();

        return visitMonth + ' ' + visitDate + ', ' + visitYear;
      }



      function isMSIE() {
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))  // If Internet Explorer, return version number
        {
          //alert(parseInt(ua.substring(msie + 5, ua.indexOf(".", msie))));
          return true;
        }
        else  // If another browser, return 0
        {
          //alert('otherbrowser');
          return false;
        }
      }


      // Parameters:
      //   startTime: 9:00 am or 9:00am
      //   endTime: 12:00 pm or 12:00pm
      // Returns String startTime a.m./p.m. &mdash; endTime a.m./p.m.
      function formatTime(startTime, endTime) {

        if(endTime == undefined) {
          endTime = '';
        }

        // Remove space if there is any
        startTime = startTime.trim();
        endTime = endTime.trim();
//      console.log("startTime2", startTime);
//      console.log("endTime2", endTime);

        if(startTime.charAt(0) == '0') {
          startTime = startTime.substring(1);
        }
        if(endTime.charAt(0) == '0') {
          endTime = endTime.substring(1);
        }

        // Change 12pm to Noon or noon.
        startTime = startTime.replace('12:00pm', 'Noon');
        endTime = endTime.replace('12:00pm', 'noon');

        var time_string_formatted = '';
        if(endTime != '') {
          time_string_formatted = startTime + ' &mdash; ' + endTime;
        } else { // When End time is not defined.
          time_string_formatted = startTime;
        }

        // Change pm to p.m. and am to a.m.
        time_string_formatted = time_string_formatted.replace(/pm/g, ' p.m.');
        time_string_formatted = time_string_formatted.replace(/am/g, ' a.m.');

        if ((time_string_formatted.match(/a.m./g) || []).length == 2) {
          // Replace 1st occurence
          time_string_formatted = time_string_formatted.replace(' a.m.', '');
        }
        if ((time_string_formatted.match(/p.m./g) || []).length == 2) {
          // Replace 1st occurence
          time_string_formatted = time_string_formatted.replace(' p.m.', '');
        }
        // Remove :00
        //time_string_formatted = time_string_formatted.replace(':00', '');
        time_string_formatted = time_string_formatted.replace(new RegExp(':00', 'g'), ''); // TODO: For some reason, in IE 11 removing ':00' is not working.

        return time_string_formatted;
      } // END OF formatTime(startTime, endTime)



    }
  };
})(Drupal, jQuery, once);