jQuery(document).ready(
    function ($) {
        'use strict';
        $('#videoModal').on('hide.bs.modal', function (e) {
            $("#ytplayer").attr("src", $("#ytplayer").attr("src"));
        });

        $('img').click(function(){
            video = '<iframe src="'+ $(this).attr('data-video') +'"></iframe>';
            $(this).replaceWith(video);
        });
    }
);