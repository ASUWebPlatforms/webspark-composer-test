(function ($,Drupal,drupalSettings) {
    Drupal.behaviors.socialwork_custom_rank_bg = {
      attach: function(context,setting) {
        $('#rank-band-bg').parents().eq(4).addClass('bg-rank');
      }
    }
})(jQuery,Drupal,drupalSettings);
