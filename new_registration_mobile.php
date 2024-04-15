<?php 
include_once "lib.php";

$key = isset($_GET['key']) ? $_GET['key'] : "-1";

displayRegistrationMsg();
return; //users are restricted to register on their own due to multiple phone registration. New page created for admin to register for users.
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
      <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
		
		$(document).ready(function(){
		$('.search-box input[type="text"]').on("keyup input", function(){
			/* Get input value on change */
			var inputVal = $(this).val();
			var resultDropdown = $(this).siblings(".result");
			if(inputVal.length>2){
				$.get("backend-search.php", {term: inputVal}).done(function(data){
					// Display the returned data in browser
				   resultDropdown.html(data);
				});
			} else{
				resultDropdown.empty();
			}
		});
		
		// Set search input value on click of result item
			$(document).on("click", ".result div", function(){
				$(this).parents(".search-box").find('input[type="text"]').val($(this).text());
				$(this).parent(".result").empty();
			});
		});
		

function init(){
	document.getElementById("name").focus();
}

function validateForm() {

  let n = document.getElementById("name").value;
  let p = document.getElementById("phone").value;
  
  if (n == "") {
    document.getElementById("error").innerHTML = "Name must be filled out";
	document.getElementById('name').style.borderColor = "red";
    return false;
  }
  
  if (p == 0) {
    document.getElementById("error").innerHTML = "Phone# must be filled out";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  
  if(p<1000000000)
  {
	document.getElementById("error").innerHTML = "Invalid phone#";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  else if(p.includes('+')==true)
  {
	document.getElementById("error").innerHTML = "Do not enter +";
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
		
    </script>
</head>

<body onLoad="init();">

<div class="title"></div>

<div id="fullpage">
   <div class="section fp-auto-height-responsive" id="section0">
		<div class='row'>
			<div class='column-menu'>
				<span class="notranslate">
					<button class="menu" onClick='window.open("new_loginPage.php","_self")'>Login</button>
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
					<form name="register" action="registerToDB.php?key=<?php echo $key; ?>" method="post">
						<table style="width:90%; text-align: left">
							<tr>
								<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">Registration</P></td>
							</tr>
									
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*Name:</H5></td>
								<td><input type="text" name="name" id="name" autocomplete="off" style="background:#ffffff" placeholder="Enter name..." />
								</td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*phone:</H5></td>
								<td><input type="tel" name="phone" id="phone" autocomplete="off" style="background:#ffffff" placeholder="Phone number..." onkeypress="return onlyNumberKey(event)" /></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">*Place :</H5></td>
								<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff" placeholder="Search place..." onfocus=this.value='' onblur="placeOnBlur();" /><div class="result"></div>
								</td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
							</tr>
							<tr  style="background-color: #FFFFFF">
								<td colspan="2" align="center"><input type="submit" value="Submit" class="primary wide" style="text-align:center; font-weight:bold; background:#660000" onclick="return validateForm();"></td>
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