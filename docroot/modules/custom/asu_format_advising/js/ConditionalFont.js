(function (Drupal) {
  Drupal.behaviors.conditionalFontSize = {
    attach: function (context, settings) {
      var selectElement = document.querySelector('.form-item-field-approved-font select');

      if (selectElement) {
        selectElement.addEventListener('change', function () {
          var value = this.value;
          var options = document.querySelectorAll('.form-item-field-font-size select option');

          options.forEach(function (option) {
            option.style.display = 'none'; // Hide the option
            option.removeAttribute('selected');
          });

          var showOptionValue;
          if (value === '1' || value === '5' || value === '6' || value === '8') {
            showOptionValue = '10';
          } else if (value === '2' || value === '4') {
            showOptionValue = '11';
          } else if (value === '3' || value === '7') {
            showOptionValue = '12';
          }

          if (showOptionValue) {
            var showOption = document.querySelector('.form-item-field-font-size select option[value="' + showOptionValue + '"]');
            if (showOption) {
              showOption.style.display = 'block'; // Show the option
              showOption.setAttribute('selected', 'selected');
            }
          }
        });
      }
    }
  };
})(Drupal);
