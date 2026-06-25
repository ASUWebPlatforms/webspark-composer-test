(function ($, Drupal) {


    Drupal.behaviors.myModuleBehavior = {
        attach: function (context, settings) {

            // console.log("hello");

            CKEDITOR.on('dialogDefinition', function (ev) {
                var dialogName = ev.data.name;
                var dialogDefinition = ev.data.definition;

                if (dialogName == 'table') {
                    var info = dialogDefinition.getContents('info');
                    info.get('txtWidth')['default'] = '100%';
                }
            });
        }
    };
}(jQuery, Drupal));