(function ($) {

  Drupal.behaviors.asu_campus_results = {
    attach: function () {
    //jQuery(document).ready(function ($) {
    var site_host = document.location.hostname;
    var full_site = 'https://' + site_host;
    let flag = false;

    if (flag === false) {
       flag = true;
        $('.dynamic_campus').find('.next_campus').hide();
        $('.next_campus').off('click').on('click', function (e) {
            e.preventDefault();
            $(this).find('svg').toggleClass('fa-minus')
            $(this).parents('.intro_of_campus').siblings().find('.next_campus').children('svg').toggleClass('fa-plus');
            var all_classes = $(this).attr("class");
            var classArr = all_classes.split(/\s+/);
            //console.log(classArr);
            //var nodeid = classArr[1];
            //console.log(nodeid);
            //console.log(classArr[1]);
            var result_url = full_site + "/campusfit/confirmation/json?nid=" + classArr[2];
            //console.log(result_url);
            var campus_val = classArr[1];
            $.ajax({
                url: result_url,
                dataType: "text",
                contentType: 'application/json',
                cache: false,
                async: false,
                    success: function (data) {
                        var jsonData = $.parseJSON(data);
                        var jsonIntlString = jsonData.resultsData;
                        if(data.length > 20){
                            //console.log(jsonIntlString);
                            $('#campus-rhs').html(jsonIntlString['body']);
                            // updateDegreeLink(campus_val);
                        }
                        else{
                            $('#campus-rhs').html('');
                        }

                    },
                    error: function () {
                        $('#campus-rhs').html('');

                    }
                });
            });
        }

        $('.duplicate_campus_link').off('click').on('click', function (e) {
            e.preventDefault();
            var nid_class = $(this).parent('span').attr("class");
            var sid = getUrlParameter('sid');
                //console.log(nid_class);
                //console.log(sid);
                var classList = $(this).attr("class");
                console.log(classList);
                var classArrT = classList.split(/\s+/);
                var campus_name = classArrT[8];
                var result_nid_url = full_site + "/campusfit/confirmation/json?nid=" + nid_class + "&sid=" + sid + "&campus=" + campus_name;
                $.ajax({
                    url: result_nid_url,
                    dataType: "text",
                    contentType: 'application/json',
                    cache: false,
                    async: true,
                    success: function (ndata) {
                        var jsonnidData = $.parseJSON(ndata);
                        var jsonnidString = jsonnidData.resultsData;
                        if(ndata.length > 20){
                            $('#top_campus_node').html(jsonnidString['body']);
                            Drupal.attachBehaviors();
                        }
                        else{
                            $('#top_campus_node').html('');
                        }
                    },
                    error: function () {
                        $('#campus-rhs').html('');

                        }
                    });
            });

             //code to get sid from url
             function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(window.location.search);

                return results ? decodeURIComponent(results[1].replace(/\+/g, ' ')) : '';
            }

            // Usage

             //hide show email conformation form when email copy button is clicked
            /*$('.fit_email_result').click(function () {
                //console.log('email clicked');
                $('#email_confirm_form').toggleClass('fit-group', 5000);
            });

            $('.fit-email-confirm-button').click(function () {
                $('.fit_email_result').hide();
                $('#email_confirm_form').hide();
            });

         //function to update "Explore degrees" link
             function updateDegreeLink(campus_name) {

                var degree_link = '';
                //console.log(campus_name);
                var url = window.location.search.substring(1);
                var hashes = url.split('&');
                var stypeval = hashes[2].split('=');
                if(stypeval[1] == "Earn%20an%20advanced%20degree%20%28masters%2C%20PhD%2C%20etc.%29."){
                    if(campus_name == "Tempe"){
                        degree_link = "https://degrees.apps.asu.edu/masters-phd/major-list/Campus/TEMPE";
                    }
                    if(campus_name == "Downtown"){
                        degree_link = "https://degrees.apps.asu.edu/masters-phd/major-list/Campus/DTPHX";
                    }
                    if(campus_name == "Poly"){
                        degree_link = "https://degrees.apps.asu.edu/masters-phd/major-list/Campus/POLY";
                    }
                    if(campus_name == "West"){
                        degree_link = "https://degrees.apps.asu.edu/masters-phd/major-list/Campus/WEST";
                    }
                }

                if(stypeval[1] == "Earn%20a%20bachelor%E2%80%99s%20degree."){
                    if(campus_name == "Tempe"){
                        degree_link = "https://degrees.apps.asu.edu/bachelors/major-list/Campus/TEMPE";
                    }
                    if(campus_name == "Downtown"){
                        degree_link = "https://degrees.apps.asu.edu/bachelors/major-list/Campus/DTPHX";
                    }
                    if(campus_name == "Poly"){
                        degree_link = "https://degrees.apps.asu.edu/bachelors/major-list/Campus/POLY";
                    }
                    if(campus_name == "West"){
                        degree_link = "https://degrees.apps.asu.edu/bachelors/major-list/Campus/WEST";
                    }
                    if(campus_name == "havasu"){
                        degree_link = "https://havasu.asu.edu/degrees";
                    }

                }
                $('.degree_link').attr('href',degree_link);

            }*/

     //}
    }
  }

})(jQuery, Drupal, drupalSettings);