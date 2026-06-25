(function (Drupal, once, window, document) {
  Drupal.behaviors.mapNavigation = {
    attach: function (context) {

      window.P7_JumpMenu = function (selObj, restore) {
        var theFullString = selObj.options[selObj.selectedIndex].value;

        if (restore) selObj.selectedIndex = 0;
        var theLength = theFullString.length;
        var endPos = theFullString.lastIndexOf("~");
        var theUrl, theTarget;

        if (endPos > 0) {
          theUrl = theFullString.substring(0, endPos);
        } else {
          theUrl = theFullString;
        }

        endPos++;
        if (endPos < theLength) {
          theTarget = theFullString.substring(endPos, theLength);
        } else {
          theTarget = "window:Main";
        }

        if (theTarget === "window:New") {
          window.open(theUrl, '_blank');
        } else if (theTarget === "window:Main") {
          window.location.href = theUrl;
        } else {
          parent.frames[theTarget].location = theUrl;
        }
      };

      window.P7_JumpMenuGo = function (selName, restore) {

        var selObj = document.querySelector('form#form1 select[name="' + selName + '"]');

        if (!selObj) {
          return;
        }

        window.P7_JumpMenu(selObj, restore);
      };

      once('mapNavigation', 'input[name="Button1"]', context).forEach((element) => {
        element.addEventListener('click', function () {
          window.P7_JumpMenuGo('menu', 0);
        });
      });
    }
  };
})(Drupal, once, window, document);
