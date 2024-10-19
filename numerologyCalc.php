<?php session_start();
require "lib.php";

$devInfo = $_GET;
//array_csv_download(); exit;

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PERIYANAYAKI ASTRO SOLUTIONS</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/translate/index.min.js"></script>
	<script src="tz.js"></script>
	 
	<div id="google_translate_element"></div>
	<script type="text/javascript">
	
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script src="script.js"></script>
	
	      <style>
			.box {
				position: relative;
				margin-left: 20px;
			}
			.box::before {
				position: absolute;
				left: -20px;
				content: attr(data-character); /* Use the data-character attribute */
				height: 20px;
				width: 20px;
				border: 1px solid black;
				background-color: blue;
				display: flex;
				justify-content: center;
				align-items: center;
				color: white;
			}
			
			.iframe-container {
				position: relative;
				width: 100%;
				height: 500px; /* Adjust height as needed */
				overflow: hidden;
			}
			.iframe-container iframe {
				position: absolute;
				top: -102px; /* Move the iframe content up by 102px */
				left: 0;
				width: 100%;
				height: calc(100% + 102px); /* Adjust height to cover the moved content */
			}
		</style>
	
    <script type="text/javascript">
	
	
	function init(){
		
	}
	
	$(document).ready(function() {
		// Set focus to the element with ID 'name'
		document.getElementById("name").focus();
		
		// Check if 'birthIframe' exists before adding the event listener
		var birthIframe = document.getElementById('birthIframe');
		
		if (birthIframe) {
			birthIframe.addEventListener('load', function() {
				// Access the iframe content
				var iframeContent = this.contentDocument || this.contentWindow.document;
				
				if (iframeContent) {
					var saveButton = iframeContent.querySelector('input#save');
					if (saveButton) {
						console.log('Save button found, getting content');
						getDivContent();
					} else {
						console.log('Save button not found.');
					}
				} else {
					console.log('Unable to access iframe content.');
				}
			});
		} else {
			console.log('birthIframe does not exist.');
		}
	});

	
	
		
	var g_input = '';
	var g_pyramid_number = '';
	var g_date = '';
	var g_name = '';
	
	/*const chaldeanValues = {
        'a': 1, 'b': 2, 'c': 3, 'd': 4, 'e': 5, 'f': 8, 'g': 3, 'h': 5,
        'i': 1, 'j': 1, 'k': 2, 'l': 3, 'm': 4, 'n': 5, 'o': 7, 'p': 8,
        'q': 1, 'r': 2, 's': 3, 't': 4, 'u': 6, 'v': 6, 'w': 6, 'x': 5,
        'y': 1, 'z': 7, ' ': 9, '.': 0, '0': 0, '1': 1, '2': 2, '3': 3,
		'4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9
    };*/
	
	const chaldeanValues = {
        'a': 1, 'b': 2, 'c': 3, 'd': 4, 'e': 5, 'f': 8, 'g': 3, 'h': 5,
        'i': 1, 'j': 1, 'k': 2, 'l': 3, 'm': 4, 'n': 5, 'o': 7, 'p': 8,
        'q': 1, 'r': 2, 's': 3, 't': 4, 'u': 6, 'v': 6, 'w': 6, 'x': 5,
        'y': 1, 'z': 7, ' ': 0, '.': 0, '0': 0, '1': 1, '2': 2, '3': 3,
		'4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9
    };

	
function displayPyramidNumerology(name) {
    // Chaldean numerology values for each letter

    name = name.toLowerCase().replace(/\s+/g, ''); // Remove spaces and convert to lowercase
	
    let numbers = name.split('').map(letter => chaldeanValues[letter] || 0);
	
	g_input = name;

	let prArr = [];
	console.log(numbers.join(' '));
	
	prArr.push(numbers.join(' '));
	
    // Calculate and display the pyramid
    while (numbers.length > 0) {
        console.log(numbers.join(' '));	
		let newNumbers = [];	
        //for (let i = 0; i < numbers.length - 1; i++) {
		for (let i = 0; i < numbers.length - 1; i++) {
			sum = numbers[i] + numbers[i + 1];
			if(sum >= 10){
				digits = sum.toString();
				sum = parseInt(digits[0]) + parseInt(digits[1]);
			}
			
            //newNumbers.push(numbers[i] + numbers[i + 1]);
			newNumbers.push(sum);
        }
        numbers = newNumbers;
		
		prArr.push(newNumbers);
    }
	
	//print pyramid
	printPyramid(prArr);
	
	temp = g_input.split('').map(letter => chaldeanValues[letter] || 0).join('');
	name_txt = (document.getElementById('name').value).toLowerCase();
	
	divElement = document.getElementById('name_number');
	divElement.innerHTML = '';
	//divElement.innerHTML += g_input.split('').join(' ').toUpperCase()+'<BR>';
	//divElement.innerHTML += '<span class="name_total">'+((document.getElementById('name').value).split('').join(' ')).toUpperCase()+'</span><BR>';
	divElement.innerHTML += '<span class="name_total">'+((document.getElementById('name').value).split('').join('')).toUpperCase()+'</span>';
	//divElement.innerHTML += g_input.split('').map(letter => chaldeanValues[letter] || 0).join(' ');
	//divElement.innerHTML += '<span class="name_total">'+g_input.split('').map(letter => chaldeanValues[letter] || 0).join('')+'</span><BR>';
	divElement.innerHTML += '<span class="name_total">'+name_txt.split('').map(letter => chaldeanValues[letter]).join('')+'</span>';
	divElement.innerHTML += '<span class="name_total">NAME TOTAL: <div class="pulse">'+sumToDoubleDigit(temp)+'</div></span>';
	divElement.innerHTML += '<BR><span class="pyramid_number">PYRAMID NUMBER: <div class="pulse">'+g_pyramid_number+'</div></span>';
	g_pyramid_number ='0';
}

function sumToSingledigit(numbers){
	for (let i = 0; i < numbers.length - 1; i++) {
		sum = numbers[i] + numbers[i + 1];
		if(sum >= 10){
			digits = sum.toString();
			sum = parseInt(digits[0]) + parseInt(digits[1]);
		}
	}
	
	return sum;
}

function sumToDoubleDigit(numbers){
	sum=0;
	
	for (let i = 0; i < numbers.length; i++) {
		sum += parseInt(numbers[i]);
		console.log('*'+numbers[i]);
		//if(sum >= 10){
		//	digits = sum.toString();
		//	sum = parseInt(digits[0]) + parseInt(digits[1]);
		//}
	}
	
	return sum;
}

function printPyramid(numbers){
	var divElement = document.getElementById("py");
	
	divElement.innerHTML = "";
	
	for(i=numbers.length-1; i>0; i--){
		temp = numbers[i];
		
		//console.log(temp.join(' '));
		if(i==numbers.length-3){
			divElement.innerHTML += "<span class='pyramid_number_inside'>"+temp.join(' ')+"</span>"+'<BR>';
			
			g_pyramid_number = temp.join('');
		}
		else{
			//divElement.innerHTML += temp.join(' ')+'<BR>';
			
			for (let i = 0; i < temp.length; i++) {
				divElement.innerHTML += '<span class="pyramid_chars">'+temp[i]+'</span>';
			  }
			 
			 divElement.innerHTML += '<BR>'
			//divElement.innerHTML += '<span class="pyramid_number">'+temp.join(' ')+'</span>';
		}
	}
	
	//temp = g_input.split('').map(letter => chaldeanValues[letter] || 0).join('');
	
	//divElement.innerHTML += g_input.split('').map(letter => chaldeanValues[letter] || 0).join(' ')+'<BR>';
	
	temp = g_input.split('').map(letter => chaldeanValues[letter] || 0).join('');
	for (let i = 0; i < temp.length; i++) {
		divElement.innerHTML += '<span class="pyramid_chars">'+temp[i]+'</span>';
	  }
	//divElement.innerHTML += g_input.split('').join(' ').toUpperCase();
	//divElement.innerHTML += '('+sumToDoubleDigit(temp)+')';
}

	document.addEventListener('DOMContentLoaded', function() {
		const dropbtn = document.querySelector('.dropbtn');
		if (dropbtn) {
			dropbtn.addEventListener('click', function() {
				document.querySelector('.dropdown-content').classList.toggle('show');
			});
		} else {
			console.error('Element with class "dropbtn" not found.');
		}
	});
	
	function calculate(){
		displayPyramidNumerology(document.getElementById('name').value);
		
		//dob = document.getElementById('day').value + document.getElementById('month').value + document.getElementById('year').value;
		//displayDobResult(dob);
	}
	
	function new_calculate(){
		displayPyramidNumerology(g_name);
		
		displayDobResult(g_date);
	}
	
	function displayDobResult(dob){
		// Chaldean numerology values for each digit
		/*const chaldeanValues = {
			'1': 1, '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9, '0': 0
		};*/
		
		const chaldeanValues = {
			'a': 1, 'b': 2, 'c': 3, 'd': 4, 'e': 5, 'f': 8, 'g': 3, 'h': 5,
			'i': 1, 'j': 1, 'k': 2, 'l': 3, 'm': 4, 'n': 5, 'o': 7, 'p': 8,
			'q': 1, 'r': 2, 's': 3, 't': 4, 'u': 6, 'v': 6, 'w': 6, 'x': 5,
			'y': 1, 'z': 7, ' ': 0, '.': 0, '0': 0, '1': 1, '2': 2, '3': 3,
			'4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9
		};

		// Month names and their corresponding numerology values
		const monthValues = {
			'january': 8, 'february': 9, 'march': 6, 'april': 6, 'may': 6, 'june': 8,
			'july': 2, 'august': 5, 'september': 2, 'october': 3, 'november': 9, 'december': 3
		};

		// Extract the day, month, and year from the date of birth
		const dobParts = dob.match(/(\d{1,2})([a-zA-Z]+)(\d{4})/);

		if (!dobParts) {
			console.error('Invalid date format. Please use dd<full month name>yyyy.');
			return;
		}

		const day = dobParts[1];
		const month = dobParts[2].toLowerCase();
		const year = dobParts[3];

		// Convert the day and year to an array of numbers
		let numbers = (day + year).split('').map(digit => chaldeanValues[digit] || 0);

		// Add the numerology value of the month
		if (monthValues[month]) {
			numbers.push(monthValues[month]);
		} else {
			console.error('Invalid month name.');
			return;
		}

		// Calculate the sum of the numbers
		let sum = numbers.reduce((acc, num) => acc + num, 0);

		// Reduce the sum to a single digit if necessary
		while (sum >= 10) {
			sum = sum.toString().split('').reduce((acc, num) => acc + parseInt(num), 0);
		}
		
		temp = day+month+year;
		
		divElement = document.getElementById('dob_result');
		divElement.innerHTML = '';
		divElement.innerHTML += '<span class="name_total">'+temp.toUpperCase()+'</span>';
		divElement.innerHTML += '<span class="name_total">'+temp.split('').map(letter => chaldeanValues[letter]).join('')+'</span>';
		divElement.innerHTML += '<span class="name_total">DOB TOTAL: <div class="pulse">'+sum+'</div></span>';
		//divElement.innerHTML += '<BR><span class="pyramid_number">PYRAMID NUMBER: <div class="pulse">'+g_pyramid_number+'</div></span>';

		// Display the numerology sum
		console.log(`Numerology sum for DOB ${dob}: ${sum}`);
		return sum;
	}
	
	function getDivContent() {
		var iframe = document.getElementById('birthIframe');
		var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
		var targetDiv = iframeDocument.querySelector('#chart'); // Replace with your div's class or ID
		if (targetDiv) {
			document.getElementById('divContent').innerHTML = targetDiv.innerHTML;
		} else {
			alert('Div not found!');
		}
		
		//fetching session data from generated birth chart
		fetch('get-session.php')
			.then(response => response.json())
			.then(data => {
				console.log(data); // Outputs: <b>Hello, World!</b>
				document.getElementById('divContent').innerHTML = data.chartdata; // Insert HTML content into an element
				displayChart1(data.chartdata,data.chartdata);
				
				g_name = data.name;
				g_date = formatDate(data.date);
			})
			.catch(error => {
				console.error('Error:', error);
			});
	}
	
	
	function displayChart1(inData, outData) {
		// Replace all null values with a space
		inData = inData.map(element => element === null ? '' : element);
		
		// Fill the array with spaces
		outData.fill(' ');
		
		var chartHTML = '<div class="divTable blueTable" id="chart">' +
    '<div class="divTableBody">' +
        '<div class="divTableRow">' +
            '<div class="divTableOuterCellLeftTop">' + outData[11] + '</div>' +
            '<div class="divTableOuterCell" style="border-left:none"></div>' +
            '<div class="divTableOuterCell">' + outData[0] + '</div>' +
            '<div class="divTableOuterCell">' + outData[1] + '</div>' +
            '<div class="divTableOuterCell" style="border-right:none"></div>' +
            '<div class="divTableOuterCellRightTop">' + outData[2] + '</div>' +
        '</div>' +
        '<div class="divTableRow">' +
            '<div class="divTableOuterCell" style="border-top:none"></div>' +
            '<div class="divTableCell">' + inData[11] + '</div>' +
            '<div class="divTableCell">' + inData[0] + '</div>' +
            '<div class="divTableCell">' + inData[1] + '</div>' +
            '<div class="divTableCell">' + inData[2] + '</div>' +
            '<div class="divTableOuterCell" style="border-top:none"></div>' +
        '</div>' +
        '<div class="divTableRow">' +
            '<div class="divTableOuterCell">' + outData[10] + '</div>' +
            '<div class="divTableCell">' + inData[10] + '</div>' +
            '<div class="divTableCenterCell1"></div>' +
            '<div class="divTableCenterCell2"></div>' +
            '<div class="divTableCell">' + inData[3] + '</div>' +
            '<div class="divTableOuterCell">' + outData[3] + '</div>' +
        '</div>' +
        '<div class="divTableRow">' +
            '<div class="divTableOuterCell">' + outData[9] + '</div>' +
            '<div class="divTableCell">' + inData[9] + '</div>' +
            '<div class="divTableCenterCell3"></div>' +
            '<div class="divTableCenterCell4"></div>' +
            '<div class="divTableCell">' + inData[4] + '</div>' +
            '<div class="divTableOuterCell">' + outData[4] + '</div>' +
        '</div>' +
        '<div class="divTableRow">' +
            '<div class="divTableOuterCell" style="border-bottom:none"></div>' +
            '<div class="divTableCell">' + inData[8] + '</div>' +
            '<div class="divTableCell">' + inData[7] + '</div>' +
            '<div class="divTableCell">' + inData[6] + '</div>' +
            '<div class="divTableCell">' + inData[5] + '</div>' +
            '<div class="divTableOuterCell" style="border-bottom:none"></div>' +
        '</div>' +
        '<div class="divTableRow">' +
            '<div class="divTableOuterCellLeftBottom">' + outData[8] + '</div>' +
            '<div class="divTableOuterCell" style="border-left:none"></div>' +
            '<div class="divTableOuterCell">' + outData[7] + '</div>' +
            '<div class="divTableOuterCell">' + outData[6] + '</div>' +
            '<div class="divTableOuterCell" style="border-right:none"></div>' +
            '<div class="divTableOuterCellRightBottom">' + outData[5] + '</div>' +
        '</div>' +
    '</div>' +
'</div>';


		document.getElementById('divContent').innerHTML = chartHTML;
		
		document.getElementById('iframe-container').style.display = 'none';
		
		// Use the global variable after the AJAX call completes
		setTimeout(() => {
			new_calculate();
			// You can use g_name here
		}, 1000); // Adjust the timeout as needed
	}


	function formatDate(dateStr) {
		const months = {
			'Jan': 'January',
			'Feb': 'February',
			'Mar': 'March',
			'Apr': 'April',
			'May': 'May',
			'Jun': 'June',
			'Jul': 'July',
			'Aug': 'August',
			'Sep': 'September',
			'Oct': 'October',
			'Nov': 'November',
			'Dec': 'December'
		};

		const [day, month, year] = dateStr.split('/');
		const monthName = months[month];

		if (monthName) {
			return `${day}${monthName}${year}`;
		} else {
			return 'Invalid date format';
		}
	}


	

    </script>
</head>

<body onLoad="init();">

	
<div class="title" id='title'></div>


	<div class='column-menu'>
		
		<?php displayUserInfo($_SESSION['userinfo']); ?>
		<div class='desktop'>
		</div>
		<div class='mobile'>
			<?php include "numerologyMenu_mobile.php"; ?>
			
		</div>
	</div>
	<div class='column-body'>
		<center>
			<button class="glow-button" id='calculatorBtn' onclick="window.open('numerology.php','_self')" style="width:-webkit-fill-available;line-height: normal;">Birth Numerology</button>
		</center>
		<table style="width:100%; text-align: left">
			<tr>
				<td colspan=2><p style="color:#660000;font-weight:bold; font-size:20px; text-align:center; margin: 5px;">DSJV எண் கணிதம்</p></td>
			</tr>
			<tr style="background-color: #FFFFFF">
				<td colspan=2><center><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px;">*Name/Phone#/Vehicle#:</H5></center></td>
			</tr>
			<tr style="background-color: #FFFFFF">
				<!-- <td><center><input type='text' name='name' id='name' style='width:100%;height:50px;color: black;font-weight: bold; background-color:gold' oninput="this.value = this.value.toUpperCase()" onkeyup='displayPyramidNumerology(this.value)' placeholder='Enter name' /></center></td> -->
				<td><center><input type='text' name='name' id='name' style='width:100%;height:50px;color: black;font-weight: bold; background-color:gold' oninput="this.value = this.value.toUpperCase()" placeholder='Enter name/phone#/vehicle#' /></center></td>
			</tr>

			<tr style="background-color: #FFFFFF;text-align-last: center;">
				<td colspan=2><button class='primary wide' onClick="calculate();" style="background:var(--menu-background)">Calculate</button></td>
			</tr>

			<tr style="background-color: #FFFFFF">
				<td colspan=2><div class='name_number' id='name_number' style='text-align: -webkit-center;'></div></td>
			</tr>
		</table>
		
		<div style='display: flex; overflow-x: auto; white-space: nowrap; text_align:center'><div class='py' id='py'></div></div>
		<BR>
		<div class='name_number' id='dob_result' style='text-align: -webkit-center;'></div>
		<BR>
		<p>*Uses Chaldean method </p>
	</div>	 
	
</center>
</body>

</html>
