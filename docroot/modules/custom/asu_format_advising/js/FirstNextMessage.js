(function (Drupal, once) {
  Drupal.behaviors.firstNextStep = {
    attach: function (context, settings) {
      once('firstNextStep', '#edit-next', context).forEach(function (button) {
        button.addEventListener('click', function () {
          const retVal = confirm("Please verify that all information presented is exactly as it appears on your official ASU record. If there us an error with anything shown below, please contact your advisor before continuing. Click 'OK' when ready to proceed.");

          if (retVal == true) {
            return true;
          } else {
            return false;
          }
        });
      });
    }
  };
})(Drupal, once);
