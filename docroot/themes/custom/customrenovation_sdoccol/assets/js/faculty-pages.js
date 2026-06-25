(function ($, Drupal, once) {
    Drupal.behaviors.myBehavior = {
        attach: function (context, settings) {
            $(document).once('myBehavior').ajaxComplete(function (e, xhr, settings) {
                $('.person-profession h4').each(function () {
                    var text = $(this).text();
                    text = text.replace(', Faculty', '');
                    $(this).text(text);
                });
            });
        }
    };
})(jQuery, Drupal, once);