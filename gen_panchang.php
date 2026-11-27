<?php
session_start();

require_once 'lib.php';

$month = isset($_POST['month']) ? (int)$_POST['month'] : (isset($_GET['month']) ? (int)$_GET['month'] : 1);
$year = isset($_POST['year']) ? (int)$_POST['year'] : (isset($_GET['year']) ? (int)$_GET['year'] : date('Y'));
$place = isset($_POST['place']) ? $_POST['place'] : (isset($_COOKIE['panchang_place']) ? $_COOKIE['panchang_place'] : '');
$TZ = 'Asia/Kolkata';
if ($place && strpos($place, '|') !== false) {
	$parts = explode('|', $place);
	if (isset($parts[1]) && $parts[1] !== '') $TZ = $parts[1];
}

// Determine language and initialize
$detected_lang = 'tamil';
if (isset($_POST['lang']) && $_POST['lang'] === 'english') {
	$detected_lang = 'english';
} elseif (isset($_GET['lang']) && $_GET['lang'] === 'english') {
	$detected_lang = 'english';
} elseif (isset($_SESSION['language'])) {
	$detected_lang = $_SESSION['language'];
} elseif (isset($_COOKIE['language'])) {
	$detected_lang = $_COOKIE['language'];
}
init_language($detected_lang);

$monthName = getMonthName($month,1); //1-tamil

function getMonthName($month,$lang) //1 - tamil
{
	$name_tamil_arr = ["dummy","ஜனவரி", "பிப்ரவரி", "மார்ச்", "ஏப்ரல்", "மே", "ஜூன்", "ஜூலை", "ஆகஸ்ட்", "செப்டம்பர்", "அக்டோபர்", "நவம்பர்", "டிசம்பர்"];
	
	if($lang == 1){
		return $name_tamil_arr[$month];
	}
}

date_default_timezone_set($TZ);

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Astrology">
	<meta name="pdf-exportable" content="true">
	
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/onepage_panchang_style.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <script src="html2pdf.bundle.min.js" type="text/javascript"></script>
	<script>
		var PDF_FILENAME = <?php echo json_encode('panchang_' . $month . '_' . $year . '.pdf'); ?>;
	</script>
	<script src="pdf_bridge.js?v=1" type="text/javascript"></script>
	<script src="panchang.js"></script>
</head>
<body>

<div id='onepage' style="width: min-content">
<div class="header-parent">
  <div class="header-left"><img src='images/dsjv_title.png' height="25px"></div>
  <!-- <div class="header-left"><button class="actionBtn" onclick="window.open('gen_panchang_form.php','_self')">Back</button></div>  -->
  <div class="header-center"><div class='divTableTextOfficeName'>பஞ்சாங்கம் (<?php echo "$monthName/$year/$place"; ?>)</div></div>
  <!-- <a href='#' onClick='generatePDF();'>download</a> -->
  <div class="header-right"><img src='images/pkastro_title.png' height="25px"></div>
</div>

<script>

	var timezone = '<?php echo $TZ; ?>';

	// localized labels from server
	var L_DATE = <?php echo json_encode(__('date')); ?>;
	var L_WEEKDAY = <?php echo json_encode(__('weekday')); ?>;
	var L_NAKSHATRA = <?php echo json_encode(__('nakshatra')); ?>;
	var L_TITHI = <?php echo json_encode(__('thithi')); ?>;
	var L_KARANAM = <?php echo json_encode(__('karanam')); ?>;
	var L_YOGA = <?php echo json_encode(__('yogam')); ?>;
	var L_FROM = <?php echo json_encode(__('from')); ?>;
	var L_TO = <?php echo json_encode(__('to')); ?>;
	var L_AFTER = <?php echo json_encode(__('after')); ?>;
	var L_PAKSHA_SUK = <?php echo json_encode(__('paksha_suk')); ?>;
	var L_PAKSHA_KRI = <?php echo json_encode(__('paksha_kri')); ?>;
	var L_PAKSHA_SUK_ABBR = <?php echo json_encode(__('paksha_suk_abbr')); ?>;
	var L_PAKSHA_KRI_ABBR = <?php echo json_encode(__('paksha_kri_abbr')); ?>;
	var IS_ENGLISH = <?php echo is_english() ? 'true' : 'false'; ?>;

	generatePanchang();

  function generatePDF() {
		generateAppPdf(typeof PDF_FILENAME !== 'undefined' ? PDF_FILENAME : 'panchang.pdf');
	}
	
	function getFirstDayOfMonth(month, year) {
			return new Date(year, month-1, 1);
			//return new Date(year, month-1, 1).toLocaleString('en-US', {
			//timeZone: timezone});  //need to -1 to get month
			//const updated_date = new Date(year, month-1, 1).toLocaleString('en-US', {
			//timeZone: timezone});
			
			//return updated_date;
	}
	
	function daysInMonth(month, year) {
		return new Date(year, month, 0).getDate();
	}
	
	function generatePanchang(){
		document.writeln('<BR>');
		document.writeln('<center>');
		document.writeln('<div class="divTable blueTable" style="height:100%;width:95%">');
			document.writeln('<div class="divTableRow">');
				document.writeln('<div class="divTablePanchangHeading">தேதி</div>');
				document.writeln('<div class="divTablePanchangHeading">வாரம்</div>');
				document.writeln('<div class="divTablePanchangHeading">நட்சத்திரம்</div>');
				document.writeln('<div class="divTablePanchangHeading">திதி</div>');
				document.writeln('<div class="divTablePanchangHeading">கரணம்</div>');
				document.writeln('<div class="divTablePanchangHeading">யோகம்</div>');
			document.writeln('</div>')
	
			//const year = 1976;
			//const month = 7;  //1 - jan
			const year = <?php echo $year; ?>;
			const month = <?php echo $month; ?>;  //1 - jan
			const total_days = daysInMonth(month, year);
			
			//var t= new getFirstDateOfMonth();
			const t = getFirstDayOfMonth(month, year);
			
			for(let i=0; i<total_days; i++)
			{	
				newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate()+i,t.getHours());
				
				newdate = changeTimezone(newdate,timezone);
				
				  panchang.calculate(newdate, function() {
					/*document.getElementById("day").innerHTML=panchang.Day.name;
					document.getElementById("tithi").innerHTML=panchang.Tithi.name+"("+panchang.Tithi.end+")";
					document.getElementById("nakshtra").innerHTML=panchang.Nakshatra.name+"("+panchang.Nakshatra.end+")";
					document.getElementById("karna").innerHTML=panchang.Karna.name+"("+panchang.Karna.end+")";
					document.getElementById("yoga").innerHTML=panchang.Yoga.name+"("+panchang.Yoga.end+")";
					document.getElementById("raasi").innerHTML=panchang.Raasi.name;
					document.getElementById("ayanamsa").innerHTML=panchang.Ayanamsa.name;*/
					
						var vDATE = (typeof L_DATE !== 'undefined') ? L_DATE : 'தேதி';
						var vWEEKDAY = (typeof L_WEEKDAY !== 'undefined') ? L_WEEKDAY : 'வாரம்';
						var vNAK = (typeof L_NAKSHATRA !== 'undefined') ? L_NAKSHATRA : 'நட்சத்திரம்';
						var vTITH = (typeof L_TITHI !== 'undefined') ? L_TITHI : 'திதி';
						var vKAR = (typeof L_KARANAM !== 'undefined') ? L_KARANAM : 'கரணம்';
						var vYOG = (typeof L_YOGA !== 'undefined') ? L_YOGA : 'யோகம்';
						var vFROM = (typeof L_FROM !== 'undefined') ? L_FROM : 'முதல்';
						var vTO = (typeof L_TO !== 'undefined') ? L_TO : 'வரை';
						var vAFTER = (typeof L_AFTER !== 'undefined') ? L_AFTER : 'பிறகு';

						var row = '<div class="divTableRow">';
						row += '<div class="divTablePanchangMedium" style="width:80px">' + formatDate(newdate) + '</div>';
						row += '<div class="divTablePanchangMedium" style="width:50px">' + panchang.Day.name + '</div>';
						// Nakshatra
						if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + panchang.Nakshatra.name + ' ' + vTO + ' ' + formatDateTime(panchang.Nakshatra.end) + (displayNextNak(newdate,panchang.Nakshatra) ? ' ' + vAFTER + ' ' + displayNextNak(newdate,panchang.Nakshatra) : '') + '</div>';
						} else {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + panchang.Nakshatra.name + ' ' + formatDateTime(panchang.Nakshatra.end) + ' ' + vTO + (displayNextNak(newdate,panchang.Nakshatra) ? ' ' + vAFTER + ' ' + displayNextNak(newdate,panchang.Nakshatra) : '') + '</div>';
						}

						// Thithi
						var tithiRaw = panchang.Tithi.name || '';
						var tithiBase = tithiRaw;
						var tithiPaksha = '';
						if (tithiRaw.indexOf('(') !== -1) {
							tithiBase = tithiRaw.split('(')[0];
							tithiPaksha = tithiRaw.split('(')[1].replace(')','');
						}
						var pakAbbr = '';
						if (tithiPaksha.indexOf('சுக்') !== -1) {
							if (typeof L_PAKSHA_SUK_ABBR !== 'undefined') pakAbbr = L_PAKSHA_SUK_ABBR;
							else if (typeof L_PAKSHA_SUK !== 'undefined') pakAbbr = L_PAKSHA_SUK;
						}
						if (tithiPaksha.indexOf('கிரு') !== -1) {
							if (typeof L_PAKSHA_KRI_ABBR !== 'undefined') pakAbbr = L_PAKSHA_KRI_ABBR;
							else if (typeof L_PAKSHA_KRI !== 'undefined') pakAbbr = L_PAKSHA_KRI;
						}
						var tithiDisplayName = tithiBase + (pakAbbr ? '(' + pakAbbr + ')' : (tithiPaksha ? '(' + tithiPaksha + ')' : ''));

						if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + tithiDisplayName + ' ' + vTO + ' ' + formatDateTime(panchang.Tithi.end) + (displayNextThithi(newdate,panchang.Tithi) ? ' ' + vAFTER + ' ' + displayNextThithi(newdate,panchang.Tithi) : '') + '</div>';
						} else {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + tithiDisplayName + ' ' + formatDateTime(panchang.Tithi.end) + ' ' + vTO + (displayNextThithi(newdate,panchang.Tithi) ? ' ' + vAFTER + ' ' + displayNextThithi(newdate,panchang.Tithi) : '') + '</div>';
						}

						// Karana (range)
						if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + panchang.Karna.name + ' ' + vFROM + ' ' + formatDateTime(panchang.Karna.start) + ' ' + vTO + ' ' + formatDateTime(panchang.Karna.end) + (displayNextKar(newdate,panchang.Karna) ? ' ' + vAFTER + ' ' + displayNextKar(newdate,panchang.Karna) : '') + '</div>';
						} else {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + panchang.Karna.name + ' ' + formatDateTime(panchang.Karna.start) + ' ' + vFROM + ' ' + formatDateTime(panchang.Karna.end) + ' ' + vTO + (displayNextKar(newdate,panchang.Karna) ? ' ' + vAFTER + ' ' + displayNextKar(newdate,panchang.Karna) : '') + '</div>';
						}

						// Yoga (range)
						if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + panchang.Yoga.name + ' ' + vFROM + ' ' + formatDateTime(panchang.Yoga.start) + ' ' + vTO + ' ' + formatDateTime(panchang.Yoga.end) + (displayNextYoga(newdate,panchang.Yoga) ? ' ' + vAFTER + ' ' + displayNextYoga(newdate,panchang.Yoga) : '') + '</div>';
						} else {
							row += '<div class="divTablePanchangMedium" style="width:160px">' + panchang.Yoga.name + ' ' + formatDateTime(panchang.Yoga.start) + ' ' + vFROM + ' ' + formatDateTime(panchang.Yoga.end) + ' ' + vTO + (displayNextYoga(newdate,panchang.Yoga) ? ' ' + vAFTER + ' ' + displayNextYoga(newdate,panchang.Yoga) : '') + '</div>';
						}

						row += '</div>';
						document.writeln(row);

				  });
				
			}
		
			document.writeln('</center>');
			document.writeln('</div>');
		document.writeln('</div>');
	}
	
	function changeTimezone(date, ianatz) {
	  // suppose the date is 12:00 UTC
	  var invdate = new Date(date.toLocaleString('en-US', {
		timeZone: ianatz
	  }));

	  // then invdate will be 07:00 in Toronto
	  // and the diff is 5 hours
	  var diff = date.getTime() - invdate.getTime();

	  // so 12:00 in Toronto is 17:00 UTC
	  return new Date(date.getTime() - diff); // needs to substract

	}
	
	function displayNextNak(today,nak){
		var localNaks = (typeof naks !== 'undefined' && Array.isArray(naks)) ? naks.slice() : ["அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி"];
		if (localNaks.length && localNaks[localNaks.length-1] !== localNaks[0]) localNaks.push(localNaks[0]);
		var idx = localNaks.indexOf(nak.name);
		if (nak.end.getDate() == today.getDate() && idx !== -1) {
			return localNaks[idx+1];
		}
		return "";
	}
	
	function displayNextKar(today,kar){
		var globalKarArr = (typeof karArr !== 'undefined' && Array.isArray(karArr)) ? karArr : null;
		var kars = globalKarArr ? globalKarArr : [ "பவ", "பாலவ", "கௌலவ", "த்தைதுலை", "கரசை", "வணிசை", "பத்தரை", "சகுனி", "சதுஷ்பாத", "நாகவ", "கிம்ஸ்துக்கினம்"];
		if (kars.length && kars[kars.length-1] !== kars[0]) kars.push(kars[0]);
		if(kar.end.getDate() == today.getDate()){
			var idx = kars.indexOf(kar.name);
			if (idx !== -1) return kars[idx+1];
		}
		return "";
	}
	
	function displayNextThithi(today,thithi){
		var localTith = (typeof tith !== 'undefined' && Array.isArray(tith)) ? tith : ["பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "பௌர்ணமி"];
		var thithis_suk = localTith.slice(0,15);
		var thithis_kri = localTith.slice(0,14).concat(["அமாவாசை"]);

		temp = (thithi.name).split('(');
		if(temp.length<2){
			temp[1]="";
		}

		var out = "";
		if(temp[1].includes('சுக்') && thithi.end.getDate() == today.getDate()){
			var idx = thithis_suk.indexOf(temp[0]);
			if (idx !== -1) out = thithis_suk[idx+1];
		}
		else if(temp[1].includes('கிரு') && thithi.end.getDate() == today.getDate()){
			var idx2 = thithis_kri.indexOf(temp[0]);
			if (idx2 !== -1) out = thithis_kri[idx2+1];
		}
		else{
			out = "";
		}

		var pakshaLabel = temp[1] || "";
		if (pakshaLabel.indexOf('சுக்') !== -1) {
			if (typeof L_PAKSHA_SUK_ABBR !== 'undefined') pakshaLabel = L_PAKSHA_SUK_ABBR;
			else if (typeof L_PAKSHA_SUK !== 'undefined') pakshaLabel = L_PAKSHA_SUK;
		}
		if (pakshaLabel.indexOf('கிரு') !== -1) {
			if (typeof L_PAKSHA_KRI_ABBR !== 'undefined') pakshaLabel = L_PAKSHA_KRI_ABBR;
			else if (typeof L_PAKSHA_KRI !== 'undefined') pakshaLabel = L_PAKSHA_KRI;
		}

		if(out.includes("பௌர்ணமி") || out.includes("அமாவாசை") || thithi.end.getDate() != today.getDate()){
			return out;
		}
		else if(out.length>0){
			return out + "("+pakshaLabel+")";
		}
		else{
			return out;
		}
	}
	
	function displayNextYoga(today,yoga){
		var localYogs = (typeof yog !== 'undefined' && Array.isArray(yog)) ? yog.slice() : ["விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
                            "ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி"];
		if (localYogs.length && localYogs[localYogs.length-1] !== localYogs[0]) localYogs.push(localYogs[0]);
		var yi = localYogs.indexOf(yoga.name);
		if(yoga.end.getDate() == today.getDate() && yi !== -1){
			return localYogs[yi+1];
		}
		return "";
	}
	
	function formatDate(date) {
	  date = changeTimezone(date,timezone);	
	  const year = date.getFullYear();
	  //const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
	  const month = date.toLocaleString('default', { month: 'short' }); // Get full month name
	  const day = String(date.getDate()).padStart(2, '0');	  

	  // Create the date string in the format: "YYYY-MM-DD"
	  const formatted = `${day}-${month}-${year}`;
	  return formatted;
	}
	
	function formatDateTime(date) {
	    date = changeTimezone(date,timezone);	
	  const year = date.getFullYear();
	  //const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
	  const month = date.toLocaleString('default', { month: 'short' }); // Get full month name
	  const day = String(date.getDate()).padStart(2, '0');
	  var hours = String(date.getHours()).padStart(2, '0');
	  const minutes = String(date.getMinutes()).padStart(2, '0');
	  const seconds = String(date.getSeconds()).padStart(2, '0');
	  
	  // Convert hours to AM/PM format
	  var ampm = hours >= 12 ? 'PM' : 'AM';
	  hours = (hours % 12) || 12; // Convert 0 to 12

	  // Create the datetime string in the format: "YYYY-MM-DD HH:MM:SS"
	  //const formattedDatetime = `${day}-${month}-${year} ${hours}:${minutes}:${seconds} ${ampm}`;
	  //const formattedDatetime = `${day}-${month} ${hours}:${minutes}:${seconds} ${ampm}`;
	  const formattedDatetime = `${day}-${month} ${hours}:${minutes} ${ampm}`;
	  return formattedDatetime;
	}
	
	function getFirstDateOfMonth() {
	  const currentDate = new Date();
	  const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
	  return firstDay;
	}
	
	function getLastDateOfMonth() {
	  const currentDate = new Date();
	  const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
	  return lastDay;
	}
	
	function getDaysInCurrentMonth() {
	  const currentDate = new Date();
	  const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
	  return lastDay.getDate();
	}
</script>

<BR><BR><BR>
<div class="PageFooter" style="width:-webkit-fill-available">&#169; Periyanayaki Astro Solutions</div>  
</body>
</html>
