function doStayAlive() {
  var request = new XMLHttpRequest();

  request.open('GET', 'stayalive.php', true);
  request.send();
}

timerStayAlive = setInterval(doStayAlive, 300000);  // 5 minutes