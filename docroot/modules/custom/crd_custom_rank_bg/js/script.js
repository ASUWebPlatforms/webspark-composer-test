(function ($,Drupal,drupalSettings) {
    Drupal.behaviors.crd_custom_rank_bg = {
      attach: function(context,setting) {
        $('#rank-band-bg').parents().eq(3).addClass('bg-rank');
      }
    }
})(jQuery,Drupal,drupalSettings);
