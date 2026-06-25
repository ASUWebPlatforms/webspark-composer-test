jQuery(document).ready(function ($) {
    // Clear JS Session Variables
    sessionStorage.clear();

		$('.sundevil-comingsoon').parent('a').removeAttr("href");
		$('.sundevil-btn').on('click', function(){
			var pname = $(this).attr('name');
			var allData = pname.split('|');
			//var personType = allData[0];
      var ptype = allData[0];
			//var ptype = $(this).parents('.views-field-title').siblings('.views-field-field-visitor-type').find('.visitor-type').text();
      // console.log("ptype:", ptype);

			// Attempt#2: Chinese character issue on 7/17/2024
			// if ptype is translated into a different language by browser
			let ptypeNewFinal = '';
			if(ptype == "High school junior" || ptype == "High school sophomore" || ptype == "High school senior" || ptype == "College transfer" || ptype == "Other" ) {
				ptypeNewFinal = ptype;

			}
      // else {
			// 	// Look at data-persontype
			// 	let ptypeNew = $(this).parents('.views-field-title').siblings('.views-field-field-visitor-type').find('.visitor-type').data('persontype');
			// 	if(ptypeNew !== 'undefined') {
			// 		ptypeNewFinal = ptypeNew.replace(/-/g, " ");
			// 	}
			// }

			let SundevilJsonDataObj = {
			  "eventtype":  allData[1],
			  "timestamp":allData[2],
			  "timestamp2":allData[3],
			  "eventdisplaytitle": allData[4],
			  "campus" : allData[5]	,
			  "from" : allData[6],
			  "to" : allData[7]
			};
			let visitsArray = [];
			visitsArray.push(SundevilJsonDataObj);
			// Save the Json to sessionStorage
			sessionStorage.setItem('visits', JSON.stringify(visitsArray));
			//sessionStorage.setItem('persontype', ptype); // Changed on 11/28/2023 to resolve Chinese character issue. // Attempt#2: Chinese character issue on 7/17/2024
			//sessionStorage.setItem('persontype', personType);
			sessionStorage.setItem('persontype', ptypeNewFinal);
		})

    // Add CSS class to table - V2
    $(".view-new-sun-devil-days-v2 > .view-content > table").removeClass('cols-10');
    $(".view-new-sun-devil-days-v2 > .view-content > table").addClass('cols-12 table table-bordered');


  // Add CSS class to table - V3
    $(".view-new-sun-devil-days-v3 > .view-content > table").removeClass('cols-10');
    $(".view-new-sun-devil-days-v3 > .view-content > table").addClass('cols-12 table table-bordered');
    $(document).ajaxComplete(function(){
      $(".view-new-sun-devil-days-v3 > .view-content > table").removeClass('cols-10');
      $(".view-new-sun-devil-days-v3 > .view-content > table").addClass('cols-12 table table-bordered');
    });


});
