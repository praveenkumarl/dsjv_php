<?php
session_start();

$month = $_POST['month'];
$year = $_POST['year'];
$place = isset($_POST['place']) ? $_POST['place'] : $_COOKIE['panchang_place'];
$TZ = explode('|',$place)[1];

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
	
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/onepage_panchang_style.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <script src="html2pdf.bundle.min.js" type="text/javascript"></script>
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
	
	generatePanchang();

  function generatePDF() {
		// Choose the element that our invoice is rendered in.
		const element = document.getElementById('onepage');
		
		const pdfname = 'panchang.pdf'
		
		var opt = {
		  margin:       0,
		  filename:     pdfname,
		  image:        { type: 'png', quality: 1.0 },
		  html2canvas:  { scale: 1 },
		  jsPDF:        { unit: 'in', format: 'A4', orientation: 'portrait' }
		};
		
		// Choose the element and save the PDF for our user.
		html2pdf().from(element).set(opt).save();
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
					
						document.writeln('<div class="divTableRow">');
							document.writeln('<div class="divTablePanchangMedium" style="width:80px">'+formatDate(newdate)+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:50px">'+panchang.Day.name+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Nakshatra.name+" "+formatDateTime(panchang.Nakshatra.end)+' வரை'+displayNextNak(newdate,panchang.Nakshatra)+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Tithi.name+' '+formatDateTime(panchang.Tithi.end)+' வரை'+displayNextThithi(newdate,panchang.Tithi)+'</div>');
							//document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.end)+' வரை'+displayNextKar(newdate,panchang.Karna)+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.start)+" முதல் "+formatDateTime(panchang.Karna.end)+' வரை'+displayNextKar(newdate,panchang.Karna)+'</div>');
							//document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.start)+" முதல் "+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
						document.writeln('</div>');

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
	var naks = ["அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி"];
		if(nak.end.getDate() == today.getDate()){
			return " பிறகு "+naks[naks.indexOf(nak.name)+1];
		}
		else{
			return "";
		}
	}
	
	function displayNextKar(today,kar){
	var kars = [ "பவ", "பாலவ", "கௌலவ", "தைதுலை", "கரசை", "வணிசை", "பத்தரை", "சகுனி", "சதுஷ்பாத", "நாகவ", "கிம்ஸ்துக்கினம்"];
		if(kar.end.getDate() == today.getDate()){
			return " பிறகு "+kars[kars.indexOf(kar.name)+1];
		}
		else{
			return "";
		}
	}
	
	function displayNextThithi(today,thithi){
	var thithis = ["பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "பௌர்ணமி", "பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "அமாவாசை"];
		if(thithi.end.getDate() == today.getDate()){
			return " பிறகு "+thithis[thithis.indexOf(thithi.name)+1];
		}
		else{
			return "";
		}
	}
	
	function displayNextYoga(today,yoga){
	var yogas = ["விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
                            "ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி"];
		if(yoga.end.getDate() == today.getDate()){
			return " பிறகு "+yogas[yogas.indexOf(yoga.name)+1];
		}
		else{
			return "";
		}
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
