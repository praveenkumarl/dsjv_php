//to avoid session timout, send a msg to server
var refreshTime = 600000; // every 10 minutes in milliseconds
window.setInterval( function() {
$.ajax({
	cache: false,
	type: "GET",
	url: "refreshSession.php",
	success: function(data) {
	}
});
}, refreshTime );