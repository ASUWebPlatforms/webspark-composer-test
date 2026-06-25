/**
 * @file
 * Global utilities.
 *
 */


(function ($,Drupal) {

  'use strict';

  Drupal.behaviors.leadership = {
    attach: function(context, settings) {

      var path = window.location.pathname;
        // Sets the default program based on the select option key value. Key is numeric and
        // automatically assigned based on order in select list (Entity select field)
        switch (path) {
            case "/masters-programs/accounting":
            case "/masters-programs/accounting/academics":
            case "/masters-programs/accounting/experience": 
            case "/masters-programs/accounting/application-process":
            case "/masters-programs/accounting/cost-financial-aid": 
            case "/masters/accountancy":
                $('[name="ps_acad_plan_descr"]').val("1"); //Master of Accountancy and Data Analytics
                break;
            case "/masters-programs/online-macc":
            case "/masters-programs/online-macc/academics":
            case "/masters-programs/online-macc/experience": 
            case "/masters-programs/online-macc/application-process":
            case "/masters-programs/online-macc/cost-financial-aid": 
            case "/masters/online-accountancy":
                $('[name="ps_acad_plan_descr"]').val("28"); //Master of Accountancy and Data Analytics - Online
                break;
            case "/masters-programs/ai-business":
            case "/masters-programs/ai-business/academics":
            case "/masters-programs/ai-business/experience": 
            case "/masters-programs/ai-business/application-process":
            case "/masters-programs/ai-business/cost-financial-aid": 
            case "/masters/ai-business":
                $('[name="ps_acad_plan_descr"]').val("29"); //Master of Science in Artificial Intelligence in Business
                break;
            case "/masters-programs/accounting-los-angeles":
            case "/masters-programs/accounting-los-angeles/academics":
            case "/masters-programs/accounting-los-angeles/experience":
            case "/masters-programs/accounting-los-angeles/application-process":
            case "/masters-programs/accounting-los-angeles/cost-financial-aid":
                $('[name="ps_acad_plan_descr"]').val("7"); //M.S. Business Analytics - L.A.
                break;
            case "/masters-programs/business-analytics":
            case "/masters-programs/business-analytics/academics":  
            case "/masters-programs/business-analytics/experience":  
            case "/masters-programs/business-analytics/application-process":  
            case "/masters-programs/business-analytics/cost-financial-aid":  
            case "/masters/ms-business-analytics-china":
            case "/masters/ms-business-analytics":  
            case "/masters/business-analytics":            
                $('[name="ps_acad_plan_descr"]').val("2"); //M.S. Business Analytics
                break;
            case "/masters-programs/online-business-analytics":
            case "/masters-programs/online-business-analytics/academics":  
            case "/masters-programs/online-business-analytics/experience":  
            case "/masters-programs/online-business-analytics/application-process":  
            case "/masters-programs/online-business-analytics/cost-financial-aid": 
            case "/masters/online-business-analytics":  
                $('[name="ps_acad_plan_descr"]').val("6"); //Business Analytics-Online
                break;
            case "/masters-programs/business-analytics-los-angeles":
            case "/masters-programs/business-analytics-los-angeles/academics":  
            case "/masters-programs/business-analytics-los-angeles/experience":  
            case "/masters-programs/business-analytics-los-angeles/application-process":  
            case "/masters-programs/business-analytics-los-angeles/cost-financial-aid":     
                $('[name="ps_acad_plan_descr"]').val("7"); //M.S. Business Analytics - L.A.
                break;
            case "/masters-programs/finance":
            case "/masters-programs/finance/academics":  
            case "/masters-programs/finance/experience":  
            case "/masters-programs/finance/application-process":  
            case "/masters-programs/finance/cost-financial-aid": 
            case "/masters/ms-finance-china": 
            case "/masters/ms-finance":
            case "/masters/finance":
                $('[name="ps_acad_plan_descr"]').val("8"); //M.S. Finance
                break;
            case "/masters-programs/global-logistics":
            case "/masters-programs/global-logistics/academics":  
            case "/masters-programs/global-logistics/experience":  
            case "/masters-programs/global-logistics/application-process":
            case "/masters/ms-global-logistics-china":
            case "/masters-programs/global-logistics/cost-financial-aid":  
            case "/masters/global-logistics": 
                $('[name="ps_acad_plan_descr"]').val("9"); //M.S. Global Logistics
                break;
            case "/masters-programs/information-management":
            case "/masters-programs/information-management/academics":  
            case "/masters-programs/information-management/experience":  
            case "/masters-programs/information-management/application-process":  
            case "/masters-programs/information-management/cost-financial-aid":  
            case "/masters/ms-information-management-china":  
            case "/masters/information-technology": 
                $('[name="ps_acad_plan_descr"]').val("10"); //M.S. Information Systems Management
                break;
            case "/masters-programs/management":
            case "/masters-programs/management/academics":  
            case "/masters-programs/management/experience":  
            case "/masters-programs/management/application-process":  
            case "/masters-programs/management/cost-financial-aid":   
                $('[name="ps_acad_plan_descr"]').val("11"); //M.S. Management
                break;
            case "/masters-programs/real-estate-development":
            case "/masters-programs/real-estate-development/academics":  
            case "/masters-programs/real-estate-development/experience":  
            case "/masters-programs/real-estate-development/application-process":  
            case "/masters-programs/real-estate-development/cost-financial-aid":   
            case "/masters/real-estate-development":
                $('[name="ps_acad_plan_descr"]').val("12"); //Master of Real Estate Development
                break;
            case "/masters-programs/supply-chain":
            case "/masters-programs/supply-chain/academics":  
            case "/masters-programs/supply-chain/experience":  
            case "/masters-programs/supply-chain/application-process":  
            case "/masters-programs/supply-chain/cost-financial-aid": 
            case "/masters/supply-chain":  
                $('[name="ps_acad_plan_descr"]').val("27"); //M.S. Supply Chain Management
                break;
            case "/masters-programs/taxation":
            case "/masters-programs/taxation/academics":  
            case "/masters-programs/taxation/experience":  
            case "/masters-programs/taxation/application-process":  
            case "/masters-programs/taxation/cost-financial-aid": 
            case "/masters/taxation":  
                $('[name="ps_acad_plan_descr"]').val("13"); //Master of Taxation and Data Analytics
                break;
            case "/masters-programs/economics":
            case "/masters-programs/economics/academics":  
            case "/masters-programs/economics/experience":  
            case "/masters-programs/economics/application-process":  
            case "/masters-programs/economics/cost-financial-aid":   
            case "/masters/economics":            
                $('[name="ps_acad_plan_descr"]').val("30"); //M.S. Economics
                break;
            case "/mba-programs/part-time":
            case "/mba-programs/part-time/academics":  
            case "/mba-programs/part-time/experience":  
            case "/mba-programs/part-time/application-process":  
            case "/mba-programs/part-time/cost-financial-aid":  
            case "/mba/evening":
                $('[name="ps_acad_plan_descr"]').val("14"); //W. P. Carey MBA - Evening
                break;
            case "/mba-programs/executive":
            case "/mba-programs/executive/academics":  
            case "/mba-programs/executive/experience":  
            case "/mba-programs/executive/application-process":  
            case "/mba-programs/executive/cost-financial-aid": 
            case "/mba/executive-mba": 
                $('[name="ps_acad_plan_descr"]').val("15"); //W. P. Carey MBA - Executive
                break;
            case "/mba-programs/egade-executive-mba":
            case "/mba-programs/egade-executive-mba/academics":  
            case "/mba-programs/egade-executive-mba/application-process":  
            case "/mba-programs/egade-executive-mba/program-cost": 
            case "/mba/egade-w-p-carey-executive-mba":   
                $('[name="ps_acad_plan_descr"]').val("16"); //W. P. Carey MBA - Custom
                break;
            case "/mba-programs/full-time":
            case "/mba-programs/full-time/academics":  
            case "/mba-programs/full-time/experience":  
            case "/mba-programs/full-time/application-process":  
            case "/mba-programs/full-time/cost-financial-aid":  
            case "/mba-programs/full-time/career-management": 
            case "/mba/fulltime":
                $('[name="ps_acad_plan_descr"]').val("18"); //MBA Full Time
                break;
            case "/mba-programs/fast-track":
            case "/mba-programs/fast-track/academics":  
            case "/mba-programs/fast-track/experience":  
            case "/mba-programs/fast-track/application-process":  
            case "/mba-programs/fast-track/cost-financial-aid":   
                $('[name="ps_acad_plan_descr"]').val("17"); //W. P. Carey MBA - Fast-Track
                break;
            case "/mba-programs/online":
            case "/mba-programs/online/academics":  
            case "/mba-programs/online/experience":  
            case "/mba-programs/online/application-process":  
            case "/mba-programs/online/cost-financial-aid":  
            case "/mba/online-program":
                $('[name="ps_acad_plan_descr"]').val("19"); //W. P. Carey MBA - Online
                break;
            case "/undergraduate-degrees/request-info":
            case "/undergraduate-degress":  
                $('[name="ps_acad_plan_descr"]').val("5"); //Undergraduate Degrees
                break;
            case "/graduate-certificates/real-estate": 
                $('[name="ps_acad_plan_descr"]').val("22"); //Real Estate Certificate
                break;
    }

      var referrer = document.referrer;
      var referrer_url = '';

      //referrer cookie functionality if the cookie is not set
      if(!Cookies.get('referring')){
        //if the referrer does not contain wpcarey set it to document.referrer
          if(referrer != '' && referrer.indexOf('wpcarey') == -1){
              referrer_url = referrer;
          //else set it to blank
          }else{
              referrer_url = '';
          }

            Cookies.set('referring', referrer_url,{ expires: 1, path: '/' });
      }

      if(!Cookies.get('landing_page')){
        Cookies.set('landing_page',document.location.href,{expires: 30, path: '/'});
      }

      function getUrlVars() {
          var vars = {};
          var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
              vars[key] = value;
          });

          return vars;
      }

      var url_variables = getUrlVars();

      if(url_variables.utm_source){
          Cookies.set('utm_source',url_variables.utm_source,{ expires: 30, path: '/' });
      }
      if(url_variables.utm_medium){
          Cookies.set('utm_medium',url_variables.utm_medium,{ expires: 30, path: '/' });
      }
      if(url_variables.utm_campaign){
          Cookies.set('utm_campaign',url_variables.utm_campaign,{ expires: 30, path: '/' });
      }
      if(url_variables.utm_term){
          Cookies.set('utm_term',url_variables.utm_term,{ expires: 30, path: '/' });
      }


      //this is done to make sure the ga is on the page.
           $(window).bind("load", function() {

               var enterpriseclientid = ga.getAll()[0].get('clientId');

               if(enterpriseclientid){
                   Cookies.set('enterpriseclientid',enterpriseclientid,{expires: 1,path: '/'});
               }
           });

    }
  };

/**
   * Recaptcha bug fix with ajax rendring form.
   */
Drupal.behaviors.recapcha_ajax_behaviour = {
    attach: function(context, settings) {
      if (typeof grecaptcha != "undefined") {
        var captchas = document.getElementsByClassName('g-recaptcha');
        for (var i = 0; i < captchas.length; i++) {
          var site_key = captchas[i].getAttribute('data-sitekey');
          if (!$(captchas[i]).html()) {
            grecaptcha.render(captchas[i], { 'sitekey' : site_key});
          }
        }
      }
    }
  }

})(jQuery, Drupal);
