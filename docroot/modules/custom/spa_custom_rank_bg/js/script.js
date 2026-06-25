(function ($,Drupal,drupalSettings) {
    Drupal.behaviors.spa_custom_rank_bg = {
      attach: function(context,setting) {
        $('#rank-band-bg-section').parents().eq(3).addClass('bg-rank');
        $('#rank-band-bg').parents().eq(4).addClass('bg-rank');
      }
    }
})(jQuery,Drupal,drupalSettings);
