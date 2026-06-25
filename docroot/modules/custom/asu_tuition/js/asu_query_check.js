(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.asu_tuition_sqlConfirm = {
    attach: function (context, settings) {
      const btn = $("#execute-query", context);
      const textarea = $("#sql-query", context);
      const confirmValue = $('#confirm-truncate', context).val();
      console.log(confirmValue);

      if (btn.length && textarea.length) {
        btn.on("click", function (e) {
          const sql = textarea.val().trim().toUpperCase();

          if (sql.startsWith("TRUNCATE") && confirmValue == 0) {
            if (!confirm("⚠️ Are you sure you want to execute this TRUNCATE query?")) {
              e.preventDefault();
              return FALSE;
            }
          }
        });
      }
    },
  };

})(jQuery, Drupal, drupalSettings);
