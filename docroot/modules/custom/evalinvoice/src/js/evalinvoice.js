
function SendLinkByMail(href) {
  
    var subject= "Evaluation Invoice Approval";
    var name= "<?php echo $Name ?>";
    var project= "<?php echo $project ?>";
    var list= "<?php echo substr($list, 1) ?>";
    var d = new Date();
    var year = d.getFullYear();
    var month = d.getMonth();
    var date = d.getDate();
    var hr = d.getHours();
    month+= 1;
  
    var ampm = "am";
    if( hr > 12 ) {
      hr -= 12;
      ampm = "pm";
    }
  
    var min = d.getMinutes();
    var body = "";
    body+= "Hello "
    body+= ",\r\n ";
    body+= "Please click \"send\" to confirm the approval of the invoice for"
    body+= encodeURIComponent(project);
    body+= " (";
    body+= encodeURIComponent(list);
    body+= ") has been approved on ";
    body+= encodeURIComponent(month);
    body+= "/";
    body+= encodeURIComponent(date);
    body+= " ";
    body+= encodeURIComponent(hr);
    if( min > 9 ) {
      body+= ":";
    }
    if( min < 10 ) {
      body+= ":0";
    }
    body+= encodeURIComponent(min);
    body+= " ";
    body+= encodeURIComponent(ampm);
  
    var uri = "mailto:ProgEval.UOEEE@asu.edu"
    uri+="?subject=";
    uri+= encodeURIComponent(subject);
    uri+= "&body=";
    uri+= encodeURIComponent(body);
    window.location.href = uri;
  
  }