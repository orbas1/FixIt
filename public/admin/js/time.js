// time
function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    h = h ? h : 12;
    m = checkTime(m);
    var t = setTimeout(startTime, 500);
}

function checkTime(i) {
    if (i < 10) {
        i = "0" + i
    };
    return i;
}



function time() {
  var span = document.getElementById('current-time');
  if(span){
    var d = new Date();
    var m = d.getMinutes();
    var h = d.getHours();
    span.textContent = 
      ("0" + h).substr(-2) + ":" + ("0" + m).substr(-2);
  }
}

setInterval(time, 1000);