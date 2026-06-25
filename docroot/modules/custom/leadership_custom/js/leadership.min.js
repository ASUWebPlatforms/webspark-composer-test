var setDate = new Date();
var hrs = setDate.getHours();

var livetime;

if (hrs < 15)
    livetime = 'Good Morning';
else if (hrs >= 15 && hrs <= 21)
    livetime = 'Good Evening';
else if (hrs >= 21 && hrs <= 24)
    livetime = 'Good Evening';

document.getElementById('liveTime').innerHTML = livetime;