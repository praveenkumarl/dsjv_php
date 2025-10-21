<?php 
include "lib.php"; 

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Login</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
			
		});
		

function init(){
	document.getElementById("phone").focus();
}

function validateForm() {
  let p = document.getElementById("phone").value;

  if (p == "") {
    document.getElementById("error").innerHTML = "Phone# must be filled out";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  else if(parseInt(p)<1000000000)
  {
	document.getElementById("error").innerHTML = "Invalid phone#";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  else if(document.getElementById("loginpass").value.length ===0)
  {  
	document.getElementById("error").innerHTML = "Password empty";
	document.getElementById('loginpass').style.borderColor = "red";
    return false;
  }
}

	function onlyNumberKey(evt) {
          
        // Only ASCII character in that range allowed
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
            return false;
        return true;
    }
	
	function showRegistrationPage(){
		var url = 'new_registration.php'
		window.open(url,'_self');
	}
		
    </script>
</head>

<body onLoad="init();">

<div class="title"></div>

<div id="fullpage">
   <div class="section fp-auto-height-responsive" id="section0">
		<div class='row'>
			<div class='column-menu'>
				<span class="notranslate">
					<button class="menu" onClick='window.open("Jamakol_input.php","_self")' disabled>Jamakol/ஜாமக்கோல்</button>
					<button class="menu" onClick='window.open("pmr_Jamakol_input.php","_self")' disabled>PMR Jamakol/PMR ஜாமக்கோல்</button>
					<button class="menu" onClick='window.open("Birth_input.php","_self")' disabled>Birth Chart/பிறப்பு ஜாதகம்</button>
					<button class="menu" onClick='window.open("Matching_input.php","_self")' disabled>Matching/பொருத்தம்</button>
					<button class="menu" onClick='window.open("planet_kaaragam.php","_self")' disabled>Planet Karaga/கிரக காரகம்</button>
					<button class="menu" onClick='window.open("raasi_kaaragam.php","_self")' disabled>Rasi Karagaராசி காரகம்</button>
					<button class="menu" onClick='window.open("hora.php","_self")' disabled>Horai/ஹோரை</a></li>
					<button class="menu" onClick='window.open("star_thaara_palan.php","_self")' disabled>Thaara Balam/தாரா பலன்</button>
				</span>
			</div>
			<div class='column-body'>
			<BR><BR>
			<div class="container">
			
				<center><div class="divTable blueTable" id="chart" style="width:360px;">
					<form name="login" action="athenticate.php" method="post">
						<table style="width:90%; text-align: left">
							<tr>
								<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">Login form</P></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*phone:</H5></td>
								<td><input type="tel" name="phone" id="phone" autocomplete="off" style="background:#ffffff" placeholder="Phone number..." onkeypress="return onlyNumberKey(event)" value='<?php echo $phone; ?>' ></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*password:</H5></td>
								<td><input type="password" name="loginpass" id="loginpass" autocomplete="off" style="background:#ffffff" placeholder="Type password..."  /></td>
							</tr>
							<tr style="background-color: #FFFFFF">
										<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
									</tr>
							<tr  style="background-color: #FFFFFF">
								<td align="center">
									<input type="button" value="Register" class="primary wide" style="text-align:center; font-weight:bold; background:#660000" onclick="showRegistrationPage();">
								</td>
								<td align="center">
									<input type="submit" value="Submit" class="primary wide" style="text-align:center; font-weight:bold; background:#660000" onclick="return validateForm();">
								</td>
							</tr>
						</table>
					</form>
				</div></center>
		  </div>
		  </div>
		  </div>
	</div>	 
</div>

</body>

</html>