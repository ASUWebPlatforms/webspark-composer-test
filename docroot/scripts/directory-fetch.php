<?php
$url = "https://asudir-solr.asu.edu/asudir/directory/select?q=*&fq=deptids:1411&rows=600&wt=json";
$json = file_get_contents($url);
file_put_contents("/sites/default/files/","/sites/default/files/isearch.json");
?>