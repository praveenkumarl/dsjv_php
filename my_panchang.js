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
							// Prepare tithi display name and map paksha to abbreviation when present
							var tithiRaw = panchang.Tithi.name || '';
							var tithiBase = tithiRaw;
							var tithiPaksha = '';
							if (tithiRaw.indexOf('(') !== -1) {
								tithiBase = tithiRaw.split('(')[0];
								tithiPaksha = tithiRaw.split('(')[1].replace(')','');
							}
							var pakAbbr = '';
							if (tithiPaksha.indexOf('சுக்') !== -1 && typeof L_PAKSHA_SUK_ABBR !== 'undefined') pakAbbr = L_PAKSHA_SUK_ABBR;
							if (tithiPaksha.indexOf('கிரு') !== -1 && typeof L_PAKSHA_KRI_ABBR !== 'undefined') pakAbbr = L_PAKSHA_KRI_ABBR;
							var tithiDisplayName = tithiBase + (pakAbbr ? '(' + pakAbbr + ')' : (tithiPaksha ? '(' + tithiPaksha + ')' : ''));

							if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
								row += '<div class="divTablePanchangMedium" style="width:160px">' + tithiDisplayName + ' ' + vTO + ' ' + formatDateTime(panchang.Tithi.end) + (displayNextThithi(newdate,panchang.Tithi) ? ' ' + vAFTER + ' ' + displayNextThithi(newdate,panchang.Tithi) : '') + '</div>';
							} else {
								row += '<div class="divTablePanchangMedium" style="width:160px">' + tithiDisplayName + ' ' + formatDateTime(panchang.Tithi.end) + ' ' + vTO + (displayNextThithi(newdate,panchang.Tithi) ? ' ' + vAFTER + ' ' + displayNextThithi(newdate,panchang.Tithi) : '') + '</div>';
							}
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
		// Use global `naks` array if available (overridden for English mode), otherwise fall back to local Tamil list
		var localNaks = (typeof naks !== 'undefined' && Array.isArray(naks)) ? naks.slice() : ["அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி"];
		// ensure wrap-around
		if (localNaks.length && localNaks[localNaks.length-1] !== localNaks[0]) localNaks.push(localNaks[0]);
		var idx = localNaks.indexOf(nak.name);
		if (nak.end.getDate() == today.getDate() && idx !== -1) {
			return localNaks[idx+1];
		}
		return "";
	}
	
	function displayNextKar(today,kar,thithi){
		// Use global arrays if available
		var localKars = (typeof kar !== 'undefined' && Array.isArray(kar)) ? kar : null; // note: parameter name 'kar' conflicts with global 'kar'
		var globalKarArr = (typeof kar !== 'undefined' && Array.isArray(kar)) ? kar : null;
		// try to use global 'kar' array defined in panchang.js; fallback to small list
		var kars = (typeof karArr !== 'undefined' && Array.isArray(karArr)) ? karArr : (globalKarArr ? globalKarArr : ["பவ", "பாலவ", "கௌலவ", "தைதுலை", "கரசை", "வணிசை", "பத்தரை", "பவ"]);
		if (kars.length && kars[kars.length-1] !== kars[0]) kars.push(kars[0]);

		if(kar.end.getDate() == today.getDate() && !thithi.includes("அமாவாசை") && !thithi.includes("பிரதமை")){
				var idx = kars.indexOf(kar.name);
				if (idx !== -1) return kars[idx+1];
			}
		return "";
	}
	
	function displayNextThithi(today,thithi){
		// use global tith array if available
		var localTith = (typeof tith !== 'undefined' && Array.isArray(tith)) ? tith : ["பிரதமை" , "துவிதியை" , "திருதியை" , "சதுர்த்தி" , "பஞ்சமி" , "சஷ்டி" , "சப்தமி" , "அஷ்டமி" , "நவமி" , "தசமி" , "ஏகாதசி" , "துவாதசி" , "திரயோதசி" , "சதுர்த்தசி", "பௌர்ணமி"];
		var thithis_suk = localTith.slice(0,15);
		var thithis_kri = localTith.slice(0,14).concat(["அமாவாசை"]);

		temp = (thithi.name).split('(');
		
		if(temp.length<2){
			temp[1]="";
		}

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

		// map paksha abbreviations to translated abbreviations when available
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
		// use global yog array if available
		var localYogs = (typeof yog !== 'undefined' && Array.isArray(yog)) ? yog.slice() : ["விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்","ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி"];
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

		// localized heading fallbacks
		const vDATE = (typeof L_DATE !== 'undefined') ? L_DATE : 'தேதி';
		const vWEEKDAY = (typeof L_WEEKDAY !== 'undefined') ? L_WEEKDAY : 'வாரம்';
		const vNAK = (typeof L_NAKSHATRA !== 'undefined') ? L_NAKSHATRA : 'நட்சத்திரம்';
		const vTITH = (typeof L_TITHI !== 'undefined') ? L_TITHI : 'திதி';
		const vKAR = (typeof L_KARANAM !== 'undefined') ? L_KARANAM : 'கரணம்';
		const vYOG = (typeof L_YOGA !== 'undefined') ? L_YOGA : 'யோகம்';
		const vFROM = (typeof L_FROM !== 'undefined') ? L_FROM : 'முதல்';
		const vTO = (typeof L_TO !== 'undefined') ? L_TO : 'வரை';
		const vAFTER = (typeof L_AFTER !== 'undefined') ? L_AFTER : 'பிறகு';

		html += `
			<div class="divTableRow">
				<div class="divTablePanchangHeading">${vDATE}</div>
				<div class="divTablePanchangHeading">${vWEEKDAY}</div>
				<div class="divTablePanchangHeading">${vNAK}</div>
				<div class="divTablePanchangHeading">${vTITH}</div>
				<div class="divTablePanchangHeading">${vKAR}</div>
				<div class="divTablePanchangHeading">${vYOG}</div>
			</div>`;

	const total_days = daysInMonth(month, year);
	const t = getFirstDayOfMonth(month, year);


	for (let i = 0; i < total_days; i++) {
		let newdate = new Date(t.getFullYear(), t.getMonth(), t.getDate() + i, t.getHours() + 6);
		newdate = changeTimezone(newdate, timezone);

		panchang.calculate(newdate, function () {
			// prepare tithi display: replace paksha text with localized/english abbreviation when needed
			var rawTithi = (panchang.Tithi && panchang.Tithi.name) ? panchang.Tithi.name : '';
			var tithiBase = rawTithi.split('(')[0];
			var tithiPak = '';
			if (rawTithi.indexOf('(') !== -1) {
				tithiPak = rawTithi.split('(')[1].replace(')','');
			}
			var pakshaLabel = tithiPak || '';
			if (pakshaLabel.indexOf('சுக்') !== -1) {
				if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
					if (typeof L_PAKSHA_SUK_ABBR !== 'undefined') pakshaLabel = L_PAKSHA_SUK_ABBR;
					else if (typeof L_PAKSHA_SUK !== 'undefined') pakshaLabel = L_PAKSHA_SUK;
					else pakshaLabel = 'Shu';
				} else {
					if (typeof L_PAKSHA_SUK !== 'undefined') pakshaLabel = L_PAKSHA_SUK;
				}
			}
			if (pakshaLabel.indexOf('கிரு') !== -1) {
				if (typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) {
					if (typeof L_PAKSHA_KRI_ABBR !== 'undefined') pakshaLabel = L_PAKSHA_KRI_ABBR;
					else if (typeof L_PAKSHA_KRI !== 'undefined') pakshaLabel = L_PAKSHA_KRI;
					else pakshaLabel = 'Kri';
				} else {
					if (typeof L_PAKSHA_KRI !== 'undefined') pakshaLabel = L_PAKSHA_KRI;
				}
			}
			var tithiDisplay = tithiBase + (pakshaLabel ? '(' + pakshaLabel + ')' : '');
			html += `
				<div class="divTableRow">
					<div class="divTablePanchangMedium" style="width:80px">${formatDate(newdate)}</div>
					<div class="divTablePanchangMedium" style="width:50px">${panchang.Day.name}</div>
						    <div class="divTablePanchangMedium" style="width:160px">
								${(typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) ? (panchang.Nakshatra.name + ' ' + vTO + ' ' + formatDateTime(panchang.Nakshatra.end) + (displayNextNak(newdate, panchang.Nakshatra) ? ' ' + vAFTER + ' ' + displayNextNak(newdate, panchang.Nakshatra) : '')) : (panchang.Nakshatra.name + ' ' + formatDateTime(panchang.Nakshatra.end) + ' ' + vTO + (displayNextNak(newdate, panchang.Nakshatra) ? ' ' + vAFTER + ' ' + displayNextNak(newdate, panchang.Nakshatra) : ''))}
						    </div>
						    <div class="divTablePanchangMedium" style="width:160px">
						${(typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) ? (tithiDisplay + ' ' + vTO + ' ' + formatDateTime(panchang.Tithi.end) + (displayNextThithi(newdate, panchang.Tithi) ? ' ' + vAFTER + ' ' + displayNextThithi(newdate, panchang.Tithi) : '')) : (tithiDisplay + ' ' + formatDateTime(panchang.Tithi.end) + ' ' + vTO + (displayNextThithi(newdate, panchang.Tithi) ? ' ' + vAFTER + ' ' + displayNextThithi(newdate, panchang.Tithi) : ''))}
						    </div>
						    <div class="divTablePanchangMedium" style="width:160px">
								${(typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) ? (panchang.Karna.name + ' ' + vFROM + ' ' + formatDateTime(panchang.Karna.start) + ' ' + vTO + ' ' + formatDateTime(panchang.Karna.end) + (displayNextKar(newdate, panchang.Karna, panchang.Tithi.name) ? ' ' + vAFTER + ' ' + displayNextKar(newdate, panchang.Karna, panchang.Tithi.name) : '')) : (panchang.Karna.name + ' ' + formatDateTime(panchang.Karna.start) + ' ' + vFROM + ' ' + formatDateTime(panchang.Karna.end) + ' ' + vTO + (displayNextKar(newdate, panchang.Karna, panchang.Tithi.name) ? ' ' + vAFTER + ' ' + displayNextKar(newdate, panchang.Karna, panchang.Tithi.name) : ''))}
						    </div>
						    <div class="divTablePanchangMedium" style="width:160px">
								${(typeof IS_ENGLISH !== 'undefined' && IS_ENGLISH) ? (panchang.Yoga.name + ' ' + vFROM + ' ' + formatDateTime(panchang.Yoga.start) + ' ' + vTO + ' ' + formatDateTime(panchang.Yoga.end) + (displayNextYoga(newdate, panchang.Yoga) ? ' ' + vAFTER + ' ' + displayNextYoga(newdate, panchang.Yoga) : '')) : (panchang.Yoga.name + ' ' + formatDateTime(panchang.Yoga.start) + ' ' + vFROM + ' ' + formatDateTime(panchang.Yoga.end) + ' ' + vTO + (displayNextYoga(newdate, panchang.Yoga) ? ' ' + vAFTER + ' ' + displayNextYoga(newdate, panchang.Yoga) : ''))}
						    </div>
				</div>`;
		});
	}

	html += `</div></center>`;
	container.innerHTML = html; // Insert the generated HTML into the container
}