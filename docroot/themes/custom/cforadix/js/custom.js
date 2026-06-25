// js for the facilities block on /construction page
(function ($) {
  var count = 1;
  $(".select-a-location-list-container a").each(function () {
    if ($(this).hasClass('collapsed')) {
      $(this).attr({
        'href': "#CollapseBody" + count,
        'id': "child" + count,
        'aria-controls': 'CollapseBody' + count
      });
      $(this).closest('.card').find('.card-body').attr({
        'id': "CollapseBody" + count,
        'aria-labelledby': "child" + count
      });
      count = count + 1;
    }
  });

  $(document).ready(function () {

    var maroon1 = $("span.btn.btn-maroon").parent();
    $(maroon1).addClass("btn");
    $(maroon1).addClass("btn-maroon");
    $("a.btn.btn-maroon span").removeClass("btn btn-maroon");

    var dark1 = $("span.btn.btn-dark").parent();
    $(dark1).addClass("btn");
    $(dark1).addClass("btn-dark");
    $("a.btn.btn-dark span").removeClass("btn btn-dark");

    var gray1 = $("span.btn.btn-gray").parent();
    $(gray1).addClass("btn");
    $(gray1).addClass("btn-gray");
    $("a.btn.btn-gray span").removeClass("btn btn-gray");

    var gold1 = $("span.btn.btn-gold").parent();
    $(gold1).addClass("btn");
    $(gold1).addClass("btn-gold");
    $("a.btn.btn-gold span").removeClass("btn btn-gold");


    var dark = $("span.btn.btn-dark").contents();
    $(dark).addClass("btn");
    $(dark).addClass("btn-dark");
    $("span.btn.btn-dark").replaceWith(dark);

    var maroon = $("span.btn.btn-maroon").contents();
    $(maroon).addClass("btn");
    $(maroon).addClass("btn-maroon");
    $("span.btn.btn-maroon").replaceWith(maroon);

    var gray = $("span.btn.btn-gray").contents();
    $(gray).addClass("btn");
    $(gray).addClass("btn-gray");
    $("span.btn.btn-gray").replaceWith(gray);

    var gold = $("span.btn.btn-gold").contents();
    $(gold).addClass("btn");
    $(gold).addClass("btn-gold");
    $("span.btn.btn-gold").replaceWith(gold);

    
  });


  // $(document).ready(function () {

  //   var dark = jQuery("span.btn.btn-dark").contents();
  //   $(dark).addClass("btn");
  //   $(dark).addClass("btn-dark");
  //   jQuery("span.btn.btn-dark").replaceWith(dark);

  //   var gray = jQuery("span.btn.btn-gray").contents();
  //   $(gray).addClass("btn");
  //   $(gray).addClass("btn-gray");
  //   jQuery("span.btn.btn-gray").replaceWith(gray);

  //   var maroon = jQuery("span.btn.btn-maroon").contents();
  //   $(maroon).addClass("btn");
  //   $(maroon).addClass("btn-maroon");
  //   jQuery("span.btn.btn-maroon").replaceWith(maroon);

  //   var gold = jQuery("span.btn.btn-gold").contents();
  //   $(gold).addClass("btn");
  //   $(gold).addClass("btn-gold");
  //   jQuery("span.btn.btn-gold").replaceWith(gold);

  // });

  $(document).ready(function () {
        var i=0;
        $(".group-contact-info .layout__region").each(function () {
          if ($(this).children(".block").length == 0) {
            $(this).hide();
            $(".group-contact-info").hide();
          } else {
            if(i ==0){
              $(".group-contact-info .layout__region").before("<h3>Contact</h3>");
              i++;
            }
            
          }
        });

      });

})(jQuery  );

(function ($) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      if (context !== document) {
        return;
      }

      /*
      $(document).ready(function () {
        var i=0;
        $(".group-contact-info .layout__region").each(function () {
          if ($(this).children(".block").length == 0) {
            $(this).hide();
            $(".group-contact-info").hide();
          } else {
            if(i ==0){
              $(".group-contact-info .layout__region").before("<h3>Contact</h3>");
              i++;
            }
            
          }
        });

      }); */





    }
  };

})(jQuery);

(function ($) {
  $(document).ready(function () {
    $("#sidebar-parent #mainCard").attr("href","javascript:void(0);");
    $('#sidebar-parent #mainCardBody .collapsed').attr("href","javascript:void(0);");

    $("#sidebar-parent #mainCard").click(function(){
      if($("#sidebar-parent #mainCardBody").hasClass("show")){
        $("#sidebar-parent #mainCardBody").removeClass("show");
      }else{
        $("#sidebar-parent #mainCardBody").addClass("collapse");
        $("#sidebar-parent #mainCardBody").addClass("show");
      }
    });

    $('#sidebar-parent #mainCardBody .collapsed').click(function(){
      let parameter= "#sidebar-parent #mainCardBody #"+$(this).attr("aria-controls");
      console.log("show", parameter);
      if($(parameter).hasClass("show")){
        $(parameter).removeClass("show");
      }else{
        $(parameter).addClass("collapse");
        $(parameter).addClass("show");
      }
    });
  });
})(jQuery);

// current url for contant webform sumbission
(function ($){
  $(window).on('load', function () {
      // Contact webform
      var pathname = window.location.pathname;
      if (pathname === '/contact') {
        var referrer = document.referrer;
        $('input[name^="current_url"]').val(referrer);
      }
    });
})(jQuery);

(function ($) {
  $(document).ready(function () {
    setTimeout(function() {
    $("th").removeAttr("contenteditable");
    $("td").removeAttr("contenteditable");    
    $("table").removeAttr("contenteditable");    
    $("thead").removeAttr("contenteditable");    
    $("tbody").removeAttr("contenteditable");
    console.log('testing contenteditable');
    }, 1000);
  });
})(jQuery);

let flagForDropDown=false;
let previousDropDown=null;

(function ($){
  
  $('.btn.btn-outline-dark.dropdown-toggle.dropdown-toggle-split').on('click',function(){
    if(flagForDropDown){
      console.log(flagForDropDown,previousDropDown);
      console.log(!previousDropDown.isEqualNode($($(this).parent()).children()[2]));
      if(previousDropDown!=null){
        $(previousDropDown).removeClass("members_dropdown_fix");
        // console.log(previousDropDown);
        if(!previousDropDown.isEqualNode($($(this).parent()).children()[2])){
        flagForDropDown=false;
        }else{
        setTimeout(function(){
          flagForDropDown=false;
        },0)
      }
      }
    }
    if(!flagForDropDown){
      $($($(this).parent()).children()[2]).addClass("members_dropdown_fix");
      previousDropDown=$($(this).parent()).children()[2]
      flagForDropDown=true;
    }
  })

})(jQuery);