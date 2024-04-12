<?php session_start();
require "lib.php";

$TZ = isset($_SESSION['TZ']) ? $_SESSION['TZ'] : "Asia/Kolkata";

$place = isset($_GET['place']) ? $_GET['place'] : $_COOKIE['panchang_place'];

date_default_timezone_set($TZ);

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$curr_month = date('m');

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Monthly Panchang</title>
    <meta name="description" content="One page panchang">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	 
    <script src="script.js"></script>
    <script type="text/javascript">
	
	function init(){
		const currentYear = new Date().getFullYear();
		document.getElementById("year").value=currentYear;
	}

	function onlyNumberKey(evt) {
		// Only ASCII character in that range allowed
		var ASCIICode = (evt.which) ? evt.which : evt.keyCode
		if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
			return false;
		return true;
	}
	
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
		
		function goBack(){
			window.open('new_panchang.php','_self');
		}
		
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

	<div class='column-menu' style="margin-left:0px">
	<?php displayUserInfo($_SESSION['userinfo']);?>
		<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
	</div>
	<div class='mobile'>
		<?php include "OnePagePanchangMenu_mobile.php"; ?>
	</div>
	</div>
	<div class='column-body'>
				<BR>
				<div class="heading">*பஞ்சாங்கம் உள்ளீடு</div>
				<BR>
				<form name="panchangForm" action="gen_panchang_pdf.php" method="post" target="_self">
				<table width="350px">
					
					<tr style="background:none;">
						<td  style="vertical-align:middle">
							<DIV class='divPanchang' style='border:none'>*மாதம்</div>
						</td>
						<td style="vertical-align:middle">
							<select name="month" id="month" style="width:fit-content;font-size:12px;background:#FDE966;font-weight:bold" onChange="formSubmitDropdown()">
								<option value="1" <?php if($curr_month==1){echo 'selected';} ?> >ஜனவரி</option>
								<option value="2" <?php if($curr_month==2){echo 'selected';} ?> >பிப்ரவரி</option>
								<option value="3" <?php if($curr_month==3){echo 'selected';} ?> >மார்ச்</option>
								<option value="4" <?php if($curr_month==4){echo 'selected';} ?> >ஏப்ரல்</option>
								<option value="5" <?php if($curr_month==5){echo 'selected';} ?> >மே</option>
								<option value="6" <?php if($curr_month==6){echo 'selected';} ?> >ஜூன்</option>
								<option value="7" <?php if($curr_month==7){echo 'selected';} ?> >ஜூலை</option>
								<option value="8" <?php if($curr_month==8){echo 'selected';} ?> >ஆகஸ்ட்</option>
								<option value="9" <?php if($curr_month==9){echo 'selected';} ?> >செப்டம்பர்</option>
								<option value="10" <?php if($curr_month==10){echo 'selected';} ?> >அக்டோபர்</option>
								<option value="11" <?php if($curr_month==11){echo 'selected';} ?> >நவம்பர்</option>
								<option value="12" <?php if($curr_month==12){echo 'selected';} ?> >டிசம்பர்</option>
							</select>
									
						</td>
					</tr>
					<tr style="background:none;">
						<td  style="vertical-align:middle">
							<DIV class='divPanchang' style='border:none'>*வருடம்</div>
						</td>
							<td style="text-align:left">
								<input type="number" name="year" id="year" maxlength="4" autocomplete="off"style="width:80px;height: 2.75em;font-size:12px;background:#FDE966;font-weight:bold; text-align:center" placeholder="வருடம்..." onkeypress="return onlyNumberKey(event)" />
							</td>								
						</td>
					</tr>
					<tr style="background:none;">
						<td  style="vertical-align:middle">
							<DIV class='divPanchang' style='border:none'>*இடம்</div>
						</td>
						<td style="text-align:left">
							<div class="search-box"><input type="text" name="place" id="place" value='<?php echo $place; ?>' onFocus="this.value=''" autocomplete="off" style="width:200px;height: 2.75em;font-size:12px;background:#FDE966;font-weight:bold; text-align:center" placeholder="Search place..."/><div class="result"></div>
						</td>	
					</tr>
					<tr style="background:none;">
						<td></td>
						<td  style="text-align:left">
							<input type='submit' class="actionBtn" id='submit' value='Submit' />
							<input type='button' class="actionBtn" onclick="goBack()" value="Back" />
						</td>
					</tr>
				</table>
				</form>
	</div>	 

</body>

</html>
