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
	$rss->load('https://asuevents.asu.edu/feed2/sandra_day_oconnor_college_law');
	$feed = array();
	foreach ($rss->getElementsByTagName('node') as $node) {
		$item = array ( 
            'nid' => $node->getElementsByTagName('nid')->item(0)->nodeValue,
			'alias' => $node->getElementsByTagName('alias')->item(0)->nodeValue,
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
            'image_url' => $node->getElementsByTagName('image_url')->item(0)->nodeValue,
            //'body_summary' => $node->getElementsByTagName('body_summary')->item(0)->nodeValue,
            //'body' => $node->getElementsByTagName('body')->item(0)->nodeValue,
			'start_date' => $node->getElementsByTagName('start_date')->item(0)->nodeValue,
			'locations' => $node->getElementsByTagName('locations')->item(0)->nodeValue
			);
		array_push($feed, $item);
	}

    echo '<div class="owl-carousel">';
	$limit = 3;
	for($x=0;$x<$limit;$x++) {
        $nid = $feed[$x]['nid'];
		$alias = $feed[$x]['alias'];
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
        $image_url = $feed[$x]['image_url'];
        //$body_summary = strip_tags($feed[$x]['body_summary']);
        //$body = $feed[$x]['body'];
		$start_date = $feed[$x]['start_date'];
		$locations = $feed[$x]['locations'];
		
		//$body_summaryCut = substr($body_summary, 0, 500);
		//$endPoint = strrpos($body_summaryCut, ' ');
		//$body_summary = $endpoint? substr($body_summaryCut, 0, $endpoint) : substr($body_summaryCut, 0);
        if ($nid == 0) { echo '<div style="display: none;"></div>';}
         else {
        echo '<div class="event-item">
        <img src="'.$image_url.'" alt="'.$title.'" />
		<h3 class="eventtitle">'.$title.'</h3>
		<p class="startdate"><strong><i class="fa fa-calendar" aria-hidden="true"></i> '.$start_date.'</strong></p>
        <p class="location"><i class="fa fa-map-marker" aria-hidden="true"></i> '.$locations.'</p>
        <a class="btn btn-maroon btn-md btn-more" href="'.$alias.'" target="_blank">Learn more</a>
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
            			items:2
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