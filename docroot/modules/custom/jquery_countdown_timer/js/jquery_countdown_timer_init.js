(function ($, Drupal, drupalSettings) {
    "use strict";
    /**
     * Attaches the JS countdown behavior
     */
    Drupal.behaviors.jsCountdownTimer = {
        attach: function (context) {
            $.each(drupalSettings.countdown, function (key, value) {
                var note = $('#jquery-countdown-timer-note-' + key),
                        ts = new Date(value.unixtimestamp * 1000);

                $(context).find('#jquery-countdown-timer-' + key).once('jquery-countdown-timer-' + key).countdown({
                    timestamp: ts,
                    font_size: value.fontsize,
                    callback: function (weeks, days, hours, minutes, seconds) {
                        var dateStrings = new Array();
                        dateStrings['@weeks'] = Drupal.formatPlural(weeks, '1 week', '@count weeks');
                        dateStrings['@days'] = Drupal.formatPlural(days, '1 day', '@count days');
                        dateStrings['@hours'] = Drupal.formatPlural(hours, '1 hour', '@count hours');
                        dateStrings['@minutes'] = Drupal.formatPlural(minutes, '1 minute', '@count minutes');
                        dateStrings['@seconds'] = Drupal.formatPlural(seconds, '1 second', '@count seconds');
                        var message = Drupal.t('@weeks, @days, @hours, @minutes, @seconds left', dateStrings);
                        note.html(message);
                    }
               });
            });
        }
    };
})(jQuery, Drupal, drupalSettings);
