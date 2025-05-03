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
	
	function generatePanchangOrig(month,year){
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
	
			
			const total_days = daysInMonth(month, year);
			
			//var t= new getFirstDateOfMonth();
			const t = getFirstDayOfMonth(month, year);
			
			for(let i=0; i<total_days; i++)
			{	
				newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate()+i,t.getHours()+6); //adding 6 hours to start panchang generation at 6 AM
				
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
							//document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.end)+' வரை'+(newdate,panchang.Karna)+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.start)+" முதல் "+formatDateTime(panchang.Karna.end)+' வரை'+displayNextKar(newdate,panchang.Karna,panchang.Tithi.name)+'</div>');
							//document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
							document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.start)+" முதல் "+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
						document.writeln('</div>');

				  });
				
			}
		
			document.writeln('</div>');
			document.writeln('</center>');
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
	var naks = ["அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி","அசுவினி"];
		if(nak.end.getDate() == today.getDate()){
			return " பிறகு "+naks[naks.indexOf(nak.name)+1];
		}
		else{
			return "";
		}
	}
	
	function displayNextKar(today,kar,thithi){
		var thithis_suk = ["பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "பௌர்ணமி"];
		var kars = [ "பவ", "பாலவ", "கௌலவ", "தைதுலை", "கரசை", "வணிசை", "பத்தரை", "பவ"];
		var kars1 = [ "பவ", "பாலவ", "கௌலவ", "தைதுலை", "கரசை", "வணிசை", "பத்தரை", "சகுனி", "சதுஷ்பாத", "நாகவ", "கிம்ஸ்துக்கினம்", "பவ"];
		
		if(kar.end.getDate() == today.getDate() && !thithi.includes("அமாவாசை") && !thithi.includes("பிரதமை")){
			return " பிறகு "+kars[kars.indexOf(kar.name)+1];
		}
		else{
			return "";
		}
	}
	
	function displayNextThithi(today,thithi){
		var thithis_suk = ["பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "பௌர்ணமி"];
		var thithis_kri = ["பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "அமாவாசை"];
		
		temp = (thithi.name).split('(');
		
		if(temp.length<2){
			temp[1]="";
		}

		if(temp[1].includes('சுக்') && thithi.end.getDate() == today.getDate()){
			out = " பிறகு "+thithis_suk[thithis_suk.indexOf(temp[0])+1];
		}
		else if(temp[1].includes('கிரு') && thithi.end.getDate() == today.getDate()){
			out = " பிறகு "+thithis_kri[thithis_kri.indexOf(temp[0])+1];
		}
		else{
			out = "";
		}
		
		if(out.includes("பௌர்ணமி") || out.includes("அமாவாசை") || thithi.end.getDate() != today.getDate()){
			return out;
		}
		else if(out.length>0){
			return out + "("+temp[1];
		}
		else{
			return out;
		}
	}
	
	function displayNextYoga(today,yoga){
	var yogas = ["விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
                            "ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி","விஷ்கம்பம்"];
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
	
	function formatDateYYYYMMDD(date) {
	  date = changeTimezone(date,timezone);	
	  const year = date.getFullYear();
	  const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
	  //const month = date.toLocaleString('default', { month: 'short' }); // Get full month name
	  const day = String(date.getDate()).padStart(2, '0');	  

	  // Create the date string in the format: "YYYY-MM-DD"
	  const formatted = `${year}-${month}-${day}`;
	  return formatted;
	}
	
	function formatDateTime(date) {
		//date = changeTimezone(date,timezone);
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
	
	//This is new implementation of same above generatePanchangOrig replacing document.write which has some performance issues
	function generatePanchang(month, year) {
		let container = document.getElementById("panchangContainer");

		// If the container doesn't exist, create it and append it to the body (or another parent element)
		if (!container) {
			container = document.createElement("div");
			container.id = "panchangContainer";
			container.style.marginTop = "20px"; // Optional styling
			document.body.appendChild(container); // You can append it to another element instead of body
		}

		container.innerHTML = ""; // Clear previous content

		let html = `<br><center><div class="divTable blueTable" style="height:100%;width:95%">`;

		html += `
			<div class="divTableRow">
				<div class="divTablePanchangHeading">தேதி</div>
				<div class="divTablePanchangHeading">வாரம்</div>
				<div class="divTablePanchangHeading">நட்சத்திரம்</div>
				<div class="divTablePanchangHeading">திதி</div>
				<div class="divTablePanchangHeading">கரணம்</div>
				<div class="divTablePanchangHeading">யோகம்</div>
			</div>`;

		const total_days = daysInMonth(month, year);
		const t = getFirstDayOfMonth(month, year);

		for (let i = 0; i < total_days; i++) {
			let newdate = new Date(t.getFullYear(), t.getMonth(), t.getDate() + i, t.getHours() + 6);
			newdate = changeTimezone(newdate, timezone);

			panchang.calculate(newdate, function () {
				html += `
					<div class="divTableRow">
						<div class="divTablePanchangMedium" style="width:80px">${formatDate(newdate)}</div>
						<div class="divTablePanchangMedium" style="width:50px">${panchang.Day.name}</div>
						<div class="divTablePanchangMedium" style="width:160px">${panchang.Nakshatra.name} ${formatDateTime(panchang.Nakshatra.end)} வரை ${displayNextNak(newdate, panchang.Nakshatra)}</div>
						<div class="divTablePanchangMedium" style="width:160px">${panchang.Tithi.name} ${formatDateTime(panchang.Tithi.end)} வரை ${displayNextThithi(newdate, panchang.Tithi)}</div>
						<div class="divTablePanchangMedium" style="width:160px">${panchang.Karna.name} ${formatDateTime(panchang.Karna.start)} முதல் ${formatDateTime(panchang.Karna.end)} வரை ${displayNextKar(newdate, panchang.Karna, panchang.Tithi.name)}</div>
						<div class="divTablePanchangMedium" style="width:160px">${panchang.Yoga.name} ${formatDateTime(panchang.Yoga.start)} முதல் ${formatDateTime(panchang.Yoga.end)} வரை ${displayNextYoga(newdate, panchang.Yoga)}</div>
					</div>`;
			});
		}

		html += `</div></center>`;
		container.innerHTML = html; // Insert the generated HTML into the container
	}
