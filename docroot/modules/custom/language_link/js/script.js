(function ($,Drupal,drupalSettings, once) {
    Drupal.behaviors.language_link = {
      attach: function(context,setting) {
        var base_url = window.location.origin + "/"
        var menuKeys = drupalSettings.language_link.menu.keys
        var menuValues = drupalSettings.language_link.menu.value
        var translationLinksContainer = $(once('language_link', '.header-dropdown-7', context)).find('.nav-link').find('a')

        for (let i = 0; i < translationLinksContainer.length; i++) {
          const element = translationLinksContainer[i]
          element.href = base_url + menuValues[i];
          element.innerHTML = menuKeys[i];
        }
      }
    }
})(jQuery,Drupal,drupalSettings,once);
