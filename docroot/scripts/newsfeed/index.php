<?php
// Turn off all error reporting
error_reporting(0);
?>
<!doctype html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">    
	<link rel="stylesheet" href="/scripts/owl/owl.carousel.min.css">
    <link rel="stylesheet" href="/scripts/owl/owl.theme.default.min.css">
    <script src="/scripts/owl/owl.carousel.min.js"></script>
	</head>
	<body>
    <?php    
    $rss = new DOMDocument();
	$rss->load('https://asunow.asu.edu/feeds/sandra-day-oconnor-college-law');
	$feed = array();
	foreach ($rss->getElementsByTagName('node') as $node) {
		$item = array ( 
            'nid' => $node->getElementsByTagName('nid')->item(0)->nodeValue,
			'path' => $node->getElementsByTagName('path')->item(0)->nodeValue,
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
            'image_url' => $node->getElementsByTagName('image_url')->item(0)->nodeValue,
            'image_alt' => $node->getElementsByTagName('image_alt')->item(0)->nodeValue,
            'teaser' => $node->getElementsByTagName('teaser')->item(0)->nodeValue,
            'body' => $node->getElementsByTagName('body')->item(0)->nodeValue,
			);
		array_push($feed, $item);
	}

    echo '<div id="newscar" class="owl-carousel">';
	$limit = 3;
	for($x=0;$x<$limit;$x++) {
        $nid = $feed[$x]['nid'];
		$path = $feed[$x]['path'];
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
        $image_url = $feed[$x]['image_url'];
        $image_alt = $feed[$x]['image_alt'];
        $teaser = strip_tags($feed[$x]['teaser']);
        $body = $feed[$x]['body'];
		
		$teaserCut = substr($teaser, 0, 120);
		$endPoint = strrpos($teaserCut, ' ');
		$teaser = $endpoint? substr($teaserCut, 0, $endpoint) : substr($teaserCut, 0);
        if ($nid == 0) { echo '<div style="display: none;"></div>';}
         else {
        echo '<div>
        <img src="'.$image_url.'" alt="'.$image_alt.'" />
        <h3 class="newstitle">'.$title.'</h3>
        <p class="newsteaser">'.$teaser.'...</p>
        <a class="btn btn-maroon btn-md btn-more" href="'.$path.'">Read more</a>
        </div>';
         }
	}
		echo '</div>';
?>

    <script>
		
		jQuery.noConflict();
(function ($) {
	$(document).ready(function () {
		'use strict';

		$(document).ready(function () {
			$(".owl-carousel").owlCarousel({
				margin: 10,
				nav: true,
				dots: false,
				responsive:{
        			0:{
            			items:1
        			},
        			480:{
            			items:1
        			},
        			768:{
            			items:3
        			}
    			}
			});
		});

	});
})(jQuery);
    </script>
	</body>
</html>