<?php 
require "lib.php";
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>PK Astro Jamakol</title>
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
        if(inputVal.length>1){
            $.get("backend-search_planet_karagam.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
               resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
    });
    
    // Set search input value on click of result item
    $(document).on("click", ".result p", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".result").empty();
    });
});

function validateForm() {

  let d = document.getElementById("planet").value;
  let kara = document.getElementById("kaaragam").value;
  
  if (d == "") {
    document.getElementById("error").innerHTML = "Planet is required";
	document.getElementById('planet').style.borderColor = "red";
    return false;
  }
  
  
  if (kara == "") {
    document.getElementById("error").innerHTML = "Kaaragam is required";
	document.getElementById('kaaragam').style.borderColor = "red";
    return false;
  }

}

function init(){

}
		
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>



	<div class="column-menu">
		<?php displayUserInfo($_SESSION['userinfo']); ?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
		</div>
		<div class='mobile'>
			<?php include "planetKaragamMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
		<BR><BR>
		<div class="heading">கிரக காரகம்/Planet Karaga </div>
		<BR>
		<div class="bodytext">Search Planet Karakas here and help in adding if anything is missing</div>
			<form name="saveNewKaragam" action="savePlanetKaaragamToDB.php" method="post">
				<table style="margin-left:1px; text-align: left;width:fit;">
					<tr>
						<td><div style="color:#660000; font-size:14px;font-weight:bold;">*காரகம்/Kaaragam:</div></td>
						<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="border:1px solid lightgray;font-size:14px;font-weight:bold;color:black;background:#FDE966;" placeholder="Search kaaragam/காரகம்..." onfocus=this.value='' onblur="placeOnBlur();" /><div class="result"></div></td>
					</tr>
					<tr>
						<td colspan=2><div class="bodytext">Please help in adding if something is missing</div></td>
					</tr>
					<tr style="background:none">
						<td><H5 style="color:#000000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px;background:#FFFFFF">*Planet</H5></td>
						
						<td><select name="planet" id="planet" style="font-size:12px;background:#FDE966">
									  <option value="Sun" selected>Sun / சூரியன்</option>
									  <option value="Moon">Moon / சந்திரன்</option>
									  <option value="Mars">Mars / செவ்வாய்</option>
									  <option value="Mercury">Mercury / புதன்</option>
									  <option value="Jupitor">Jupitor / குரு</option>
									  <option value="Venus">Venus / சுக்ரன்</option>
									  <option value="Saturn">Saturn / சனி</option>
									  <option value="Ragu">Ragu / ராகு</option>
									  <option value="Kethu">Kethu / கேது </option>
									</select></td>
					</tr>
					<tr style="background:none">
						<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px;background:#FFFFFF">*Kaaragam</H5></td>
						<td><input type="text" name="kaaragam" id="kaaragam" autocomplete="off" style="border:1px solid lightgray;font-size:14px;font-weight:bold;color:black;background:#FDE966;" placeholder="Enter kaaragam/காரகம்..." /></td>
					</tr>
					<tr style="background:none">
						<td colspan="2" align="center"><input type="submit" value="Add" class="primary wide" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();"></td>
					</tr>
					
					<tr style="background:none">
						<td colspan='2' align='center'><div id='error' style='color:red;'></div></td>
					</tr>
				</table>
			</form>
		</div>
	</div>

</body>

</html>