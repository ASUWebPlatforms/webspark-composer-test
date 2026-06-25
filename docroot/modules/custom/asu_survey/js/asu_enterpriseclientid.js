(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.enterpriseclientid = {
        attach: function (context, settings) {


            /* This function gets the client Ids from GA accounts
             * Author: David Lemus - EdPlus @ ASU
             * @asuonline account Id: UA-141599-1 //<--- This is used only at ASU Online sites.
             * @asu enterprise account ID: UA-42798992-4
             */
            function getClientId() {

                if (typeof ga !== "undefined") {
                    ga(() => {
                    // ga(function() {
                        let cidE = "";
                        let cidA = "";
                        let gaIds = ga.getAll();
                        let i, size, match;
                        for (i = 0, size = gaIds.length, match = 0; i < size; i++) {
                            if (gaIds[i].get('trackingId') === 'UA-141599-1' && cidE === "") { // This 'if' will never run.

                                //The field name sent to the Lead API should be clientid
                                //You can use an existing field or create it dynamicaly
                                cidE = gaIds[i].get('clientId');
                                //e.g. $("#clientid").val(gaIds[i].get('clientId'));
                                $("input[name='asuonline_enterpriseclientid']").val(cidE);
                            } else if (gaIds[i].get('trackingId') === 'UA-42798992-4' && cidA === "") {

                                //The field name sent to the Lead API should be enterpriseclientid
                                //You can use an existing field or create it dynamicaly
                                cidA = gaIds[i].get('clientId');
                                //console.log("cidA", cidA);
                                //e.g. $("#enterpriseclientid").val(gaIds[i].get('clientId'));
                                $("input[name='asuonline_enterpriseclientid']").val(cidA);
                            }
                        }
                    });
                }
            }
            getClientId();

            /* Sometimes there were cases that analytics.js is not loaded and didn't grab the clientId. Therefore, we try to grab the clientId again when Submit button was clicked.  */
            $( ".webform-button--submit" ).submit(function( event ) {
                if($("input[name='asuonline_enterpriseclientid']").val() == null || $("input[name='asuonline_enterpriseclientid']").val() == '') {
                    getClientId();
                }
            });

        }
    };
})(jQuery, Drupal, drupalSettings);