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
				width: 400px;
				height: -webkit-fill-available;
				overflow: hidden;
			}
			.iframe-container iframe {
				position: absolute;
				top: -180px; /* Move the iframe content up by 102px */
				left: 0;
				width: 100%;
				height: calc(100% + 102px); /* Adjust height to cover the moved content */
			}
		</style>
	
    <script type="text/javascript">
	
	
	function init(){
		
	}
	
	$(document).ready(function() {
		var birthIframe = document.getElementById('birthIframe');
		
		if (birthIframe) {
			birthIframe.addEventListener('load', function() {
				setTimeout(() => {
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
				}, 100); // Adjust the delay as needed
			});
		} else {
			console.log('birthIframe does not exist.');
		}
	});


	
	
		
	var g_input = '';
	var g_pyramid_number = '';
	var g_date = '';
	var g_name = '';
	var outData = new Array(12).fill(' '); // Initializes with 12 elements, all set to 0
	
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
	
	//planet: [natpu, samam, pagai, vasiyam]
	
	const planetNatpuPagai = {
		'சூரி': [['சந்', 'செ','குரு'], ['புத'], ['சுக்', 'சனி', 'ரா', 'கே'], ['ரா']],
		'சந்': [['சூரி', 'செ','குரு'], ['புத','சுக்'], ['சனி', 'ரா', 'கே'], ['கே']],
		'செ': [['சூரி', 'சந்','குரு'], ['சுக்','ரா'], ['சனி','கே'], ['சனி']],
		'புத': [['சூரி', 'சுக்','சனி'], ['சந்','கே'], ['செ','குரு'], ['குரு']],
		'குரு': [['சூரி', 'சந்','செ'], ['சனி','ரா','கே'], ['சுக்','புத'], ['சுக்']],
		'சுக்': [['சனி', 'புத','ரா'], ['செ','சந்'], ['கே','சூரி','குரு'], ['குரு']],
		'சனி': [['சுக்', 'புத','ரா'], ['கே','குரு'], ['சூரி','சந்','செ'], ['செ']],
		'ரா': [['சனி', 'புத','சுக்'], ['கே','குரு'], ['சூரி','சந்','செ'], ['சூரி']],
		'கே': [['புத','குரு'], ['செ','சனி','ரா'], ['சுக்','சூரி','சந்'], ['சந்']]
	};
	
	const planetNumbers = {
		'சூரி': 1,
		'சந்': 2,
		'குரு': 3,
		'ரா': 4,
		'புத': 5,
		'சுக்': 6,
		'கே': 7,
		'சனி': 8,
		'செ': 9
	};
	
	const enemyHouse = {
	  'a': [1, 6, 9, 10],
	  'i': [1, 6, 9, 10], 
	  'j': [1, 6, 9, 10], 
	  'q': [1, 6, 9, 10], 
	  'y': [1, 6, 9, 10],
	  'b': [9, 10], 
	  'k': [9, 10], 
	  'r': [9, 10],
	  'c': [1, 6, 2, 5], 
	  'g': [1, 6, 2, 5], 
	  'l': [1, 6, 2, 5], 
	  's': [1, 6, 2, 5],
	  'd': [4, 3, 0, 7], 
	  'm': [4, 3, 0, 7], 
	  't': [4, 3, 0, 7],
	  'e': [0, 7, 8, 11], 
	  'h': [0, 7, 8, 11], 
	  'n': [0, 7, 8, 11], 
	  'x': [0, 7, 8, 11],
	  'u': [4, 8, 11], 
	  'v': [4, 8, 11], 
	  'w': [4, 8, 11],
	  'o': [1, 6, 4, 3], 
	  'z': [1, 6, 4, 3],
	  'f': [4, 3, 0, 7], 
	  'p': [4, 3, 0, 7]
	};
	
	const vasiyaHouse = {
	  'a': null,
	  'i': null, 
	  'j': null, 
	  'q': null, 
	  'y': null,
	  'b': null,
	  'k': null,
	  'r': null,
	  'c': [1, 6], 
	  'g': [1, 6], 
	  'l': [1, 6], 
	  's': [1, 6],
	  'd': [4], 
	  'm': [4], 
	  't': [4],
	  'e': [11], 
	  'h': [8, 11], 
	  'n': [8, 11], 
	  'x': [8, 11],
	  'u': [8, 11],  
	  'v': [8, 11],  
	  'w': [8, 11], 
	  'o': [3], 
	  'z': [3],
	  'f': [0, 7], 
	  'p': [0, 7]
	};

	
	//planet: [uchcham, aatchi, natpu, samam, pagai, neesam]
	const planetNatpuPagaiNumbers = {
		'சூரி': [[1, 73], [37], [28,46,64,100], [10], [82], [19,55,91]],
		'சந்': [[2, 38, 74], [29], [11,47,101], [65,83], [], [20,56,92]],
		'செ': [[90], [9,72,81,108], [45], [27], [19,54,63,99], [36]],
		'புத': [[50], [14,41,95], [5,23,59,77,86], [], [32,68], [104]],
		'குரு': [[30], [48,57,93], [21,66,102], [3,12,75], [39], [84]],
		'சுக்': [[24,60,96], [15], [87], [51,69], [33,105], [6,42,78]],
		'சனி': [[62], [35,71,98,107], [26], [17,53,89], [44,80], [8]],
		'ரா': [[67], [], [22,31,58,103], [94], [4,40,49,76,85], [13]],
		'கே': [[70], [], [7,43,79,106], [25,52], [34,61,88,97], [16]],
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
	//name_txt = (document.getElementById('name').value).toLowerCase();
	name_txt = g_input.toLowerCase();
	
	divElement = document.getElementById('name_number');
	divElement.innerHTML = '';

	//divElement.innerHTML += '<span class="name_total">'+((document.getElementById('name').value).split('').join('')).toUpperCase()+'</span>';
	divElement.innerHTML += '<BR><span class="name_total">'+(g_input.split('').join('')).toUpperCase()+'</span>';
	divElement.innerHTML += '<span class="name_total">'+name_txt.split('').map(letter => chaldeanValues[letter]).join('')+'</span>';
	divElement.innerHTML += '<span class="name_total">NAME TOTAL: <div class="pulse">'+sumToDoubleDigit(temp)+'</div></span>';
	divElement.innerHTML += '<span class="pyramid_number">PYRAMID NUMBER: <div class="pulse">'+g_pyramid_number+'</div></span>';
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
		
		dob = document.getElementById('day').value + document.getElementById('month').value + document.getElementById('year').value;
		displayDobResult(dob);
	}
	
	function new_calculate111(lagna_index) {
		displayPyramidNumerology(g_name);
		displayDobResult(g_date);

		const name_length = g_name.length;
		console.log('Name length is ' + name_length);
		let index = lagna_index % 12;

		if (name_length == 3) {
			// Specific logic for name_length == 3
			let steps = [0, 4, 8];
			for (let i = 0; i < steps.length; i++) {
				let stepIndex = (lagna_index + steps[i]) % 12;
				if (i < g_name.length && g_name[i] !== ' ') {
					if (!outData[stepIndex]) {
						outData[stepIndex] = g_name[i].toUpperCase();
					} else {
						outData[stepIndex] += g_name[i].toUpperCase();
					}
				}
			}
		} else if (name_length == 4) {
			// Specific logic for name_length == 4
			let steps = [0, 3, 6, 9];
			for (let i = 0; i < steps.length; i++) {
				let stepIndex = (lagna_index + steps[i]) % 12;
				if (i < g_name.length && g_name[i] !== ' ') {
					if (!outData[stepIndex]) {
						outData[stepIndex] = g_name[i].toUpperCase();
					} else {
						const ch = g_name[i].toLowerCase();
						const idx = enemyHouse[ch];
						const idx1 = vasiyaHouse[ch];
						const commonElements = idx && idx1 ? idx.filter(element => idx1.includes(element)) : [];
						
						if (commonElements.length > 0) {
							if (idx1.includes(stepIndex)) {
								outData[stepIndex] += '<span style="color:white;background-color: #32CD32;font-size:15px">${g_name[i].toUpperCase()}</span>';
							} else if (idx.includes(stepIndex)) {
								outData[stepIndex] += '<span style="color:white;background-color: red;font-size:15px">${g_name[i].toUpperCase()}</span>';
							} else {
								outData[stepIndex] += '<span style="font-size:15px">${g_name[i].toUpperCase()}</span>';
							}
						} else {
							if (idx1.includes(stepIndex)) {
								outData[stepIndex] += '<span style="color:white;background-color: #32CD32;font-size:15px">${g_name[i].toUpperCase()}</span>';
							} else if (idx.includes(stepIndex)) {
								outData[stepIndex] += '<span style="color:white;background-color: red;font-size:15px">${g_name[i].toUpperCase()}</span>';
							} else {
								outData[stepIndex] += '<span style="font-size:15px">${g_name[i].toUpperCase()}</span>';
							}
						}
					}
				}
			}
		} else {
			// General logic for name_length > 4
			for (let i = 0; i < g_name.length; i++) {
				if (g_name[i] !== ' ') {
					if (!outData[index]) {
						if (index == idx) {
							outData[index] = '<span style="color: red;">' + g_name[i].toUpperCase() + '</span>';
						} else {
							outData[index] = g_name[i].toUpperCase();
						}
					} else {
						const ch = g_name[i].toLowerCase();
						const idx = enemyHouse[ch];
						const idx1 = vasiyaHouse[ch];
						const commonElements = idx && idx1 ? idx.filter(element => idx1.includes(element)) : [];
						
						console.log('char=' + g_name[i] + ',idx=' + idx + ',idx1=' + idx1 + ',index=' + index);
						
						if (commonElements.length > 0) {
							if (idx1.includes(index)) {
								outData[index] += '<span style="color:white;background-color: #32CD32;font-size:15px">' + g_name[i].toUpperCase() + '</span>';
							} else if (idx.includes(index)) {
								outData[index] += '<span style="color:white;background-color: red;font-size:15px">' + g_name[i].toUpperCase() + '</span>';
							} else {
								outData[index] += '<span style="font-size:15px">' + g_name[i].toUpperCase() + '</span>';
							}
						} else {
							if (idx1.includes(index)) {
								outData[index] += '<span style="color:white;background-color: #32CD32;font-size:15px">' + g_name[i].toUpperCase() + '</span>';
							} else if (idx.includes(index)) {
								outData[index] += '<span style="color:white;background-color: red;font-size:15px">' + g_name[i].toUpperCase() + '</span>';
							} else {
								outData[index] += '<span style="font-size:15px">' + g_name[i].toUpperCase() + '</span>';
							}
						}
					}
					index = (index + 1) % 12;
				}
			}
		}
	}

	
	function new_calculate(lagna_index){
		displayPyramidNumerology(g_name);
		
		displayDobResult(g_date);
		
		const name_length = g_name.length;
		
		console.log('Name length is '+name_length);
		
		let index = lagna_index % 12;

		if (name_length == 3) {
			// Specific logic for name_length == 3
			let steps = [0, 4, 8];
			for (let i = 0; i < steps.length; i++) {
				let stepIndex = (lagna_index + steps[i]) % 12;
				if (i < g_name.length && g_name[i] !== ' ') {
					if (!outData[stepIndex]) {
						if(stepIndex == idx){
							outData[stepIndex] = '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
						}
						else{
							outData[stepIndex] = g_name[i].toUpperCase();
						}
					} else {
						const ch = g_name[i].toLowerCase();
						const idx = enemyHouse[ch];
						const idx1 = vasiyaHouse[ch];
						//alert('Letter='+ch+',idx='+idx+',house='+stepIndex);
						//outData[stepIndex] += g_name[i].toUpperCase();
						
						if(idx !== null && idx1 !== null && idx.length > 0 && idx1.length > 0){
							var commonElements = idx.filter(element => idx1.includes(element));
						}
						else{
							var commonElements = '';
						}
						
						//alert('char='+g_name[i]+',idx='+idx+',idx1='+idx1+',stepIndex='+stepIndex);
						console.log('char='+g_name[i]+',idx='+idx+',idx1='+idx1+',stepIndex='+stepIndex);
						if(commonElements.length>0){
							if(idx1!==null && idx1.includes(stepIndex)){
								outData[stepIndex] += '<span class="chartNumGreen">'+g_name[i].toUpperCase()+'</span>';
							}
							else if(idx.includes(stepIndex)){
								outData[stepIndex] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
								//outData[stepIndex] += g_name[i].toUpperCase();
							}
							else{
								outData[stepIndex] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
							}
						}
						else if(idx1!==null && idx1.includes(stepIndex)){
							if(idx1.includes(stepIndex)){
								outData[stepIndex] += '<span class="chartNumGreen">'+g_name[i].toUpperCase()+'</span>';
							}
							else if(idx.includes(stepIndex)){
								//outData[stepIndex] += g_name[i].toUpperCase();
								outData[stepIndex] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
							}
							else{
								outData[stepIndex] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
							}
						}
						else{
							if(idx.includes(stepIndex)){
								outData[stepIndex] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
							}
							else{
								outData[stepIndex] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
							}
							
							//outData[stepIndex] += g_name[i].toUpperCase();
						}
					}
				}
			}
			/*for (let i = 0; i < steps.length; i++) {
				let stepIndex = (lagna_index + steps[i]) % 12;
				if (i < g_name.length && g_name[i] !== ' ') {
					if (!outData[stepIndex]) {
						outData[stepIndex] = g_name[i].toUpperCase();
					} else {
						outData[stepIndex] += g_name[i].toUpperCase();
					}
				}
			}*/
			
		/*} else if (name_length == 4) {
			// Specific logic for name_length == 4
			let steps = [0, 3, 6, 9];
			for (let i = 0; i < steps.length; i++) {
				let stepIndex = (lagna_index + steps[i]) % 12;
				if (i < g_name.length && g_name[i] !== ' ') {
					if (!outData[stepIndex]) {
						outData[stepIndex] = g_name[i].toUpperCase();
					} else {
						outData[stepIndex] += g_name[i].toUpperCase();
					}
				}
			}
		} else {*/
			} else if (name_length == 4) {
				// Specific logic for name_length == 4
				let steps = [0, 3, 6, 9];
				for (let i = 0; i < steps.length; i++) {
					let stepIndex = (lagna_index + steps[i]) % 12;
					if (i < g_name.length && g_name[i] !== ' ') {
						if (!outData[stepIndex]) {
							if(stepIndex == idx){
								outData[stepIndex] = '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
							}
							else{
								outData[stepIndex] = g_name[i].toUpperCase();
							}
						} else {
							const ch = g_name[i].toLowerCase();
							const idx = enemyHouse[ch];
							const idx1 = vasiyaHouse[ch];
							//alert('Letter='+ch+',idx='+idx+',house='+stepIndex);
							//outData[stepIndex] += g_name[i].toUpperCase();
							
							if(idx !== null && idx1 !== null && idx.length > 0 && idx1.length > 0){
								var commonElements = idx.filter(element => idx1.includes(element));
							}
							else{
								var commonElements = '';
							}
							
							//alert('char='+g_name[i]+',idx='+idx+',idx1='+idx1+',stepIndex='+stepIndex);
							console.log('char='+g_name[i]+',idx='+idx+',idx1='+idx1+',stepIndex='+stepIndex);
							if(commonElements.length>0){
								if(idx1!==null && idx1.includes(stepIndex)){
									outData[stepIndex] += '<span class="chartNumGreen">'+g_name[i].toUpperCase()+'</span>';
								}
								else if(idx.includes(stepIndex)){
									outData[stepIndex] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
									//outData[stepIndex] += g_name[i].toUpperCase();
								}
								else{
									outData[stepIndex] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
								}
							}
							else if(idx1!==null && idx1.includes(stepIndex)){
								if(idx1.includes(stepIndex)){
									outData[stepIndex] += '<span class="chartNumGreen">'+g_name[i].toUpperCase()+'</span>';
								}
								else if(idx.includes(stepIndex)){
									//outData[stepIndex] += g_name[i].toUpperCase();
									outData[stepIndex] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
								}
								else{
									outData[stepIndex] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
								}
							}
							else{
								if(idx.includes(stepIndex)){
									outData[stepIndex] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
								}
								else{
									outData[stepIndex] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
								}
								
								//outData[stepIndex] += g_name[i].toUpperCase();
							}
						}
					}
				}
			} else {
			// General logic for name_length > 4
			for (let i = 0; i < g_name.length; i++) {
				if (g_name[i] !== ' ') {
					if (!outData[index]) {
						if(index == idx){
							outData[index] = '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
						}
						else{
							outData[index] = g_name[i].toUpperCase();
						}
					} else {
						const ch = g_name[i].toLowerCase();
						const idx = enemyHouse[ch];
						const idx1 = vasiyaHouse[ch];
						//alert('Letter='+ch+',idx='+idx+',house='+index);
						//outData[index] += g_name[i].toUpperCase();
						
						if(idx !== null && idx1 !== null && idx.length > 0 && idx1.length > 0){
							var commonElements = idx.filter(element => idx1.includes(element));
						}
						else{
							var commonElements = '';
						}
						
						//alert('char='+g_name[i]+',idx='+idx+',idx1='+idx1+',index='+index);
						console.log('char='+g_name[i]+',idx='+idx+',idx1='+idx1+',index='+index);
						if(commonElements.length>0){
							if(idx1!==null && idx1.includes(index)){
								outData[index] += '<span class="chartNumGreen">'+g_name[i].toUpperCase()+'</span>';
							}
							else if(idx.includes(index)){
								outData[index] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
								//outData[index] += g_name[i].toUpperCase();
							}
							else{
								outData[index] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
							}
						}
						else if(idx1!==null && idx1.includes(index)){
							if(idx1.includes(index)){
								outData[index] += '<span class="chartNumGreen">'+g_name[i].toUpperCase()+'</span>';
							}
							else if(idx.includes(index)){
								//outData[index] += g_name[i].toUpperCase();
								outData[index] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
							}
							else{
								outData[index] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
							}
						}
						else{
							if(idx.includes(index)){
								outData[index] += '<span class="chartNumRed">'+g_name[i].toUpperCase()+'</span>';
							}
							else{
								outData[index] += '<span style="font-size:15px">'+g_name[i].toUpperCase()+'</span>';
							}
							
							//outData[index] += g_name[i].toUpperCase();
						}
					}
					index = (index + 1) % 12;
				}
			}
		}


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
			//document.getElementById('divContent').innerHTML = targetDiv.innerHTML;
			console.log('Data fetched'); //birth chart generated
		} else {
			alert('Div not found!');
		}
		
		//fetching session data from generated birth chart
		fetch('get-session.php')
			.then(response => response.json())
			.then(data => {
				console.log(data); // Outputs: <b>Hello, World!</b>
				//document.getElementById('divContent').innerHTML = data.chartdata; // Insert HTML content into an element
				displayChart1(data.chartdata);
				
				g_name = data.name;
				g_date = formatDate(data.date);
			})
			.catch(error => {
				console.error('Error:', error);
			});
	}
	
	
	function displayChart1(inData) {
		// Replace all null values with a space
		inData = inData.map(element => element === null ? '' : element);
		
		// Fill the array with spaces
		//outData.fill(' ');
		
		// Use the global variable after the AJAX call completes
		setTimeout(() => {
			let index = inData.findIndex(element => element.includes("லக்"));
			console.log('Lagna house = '+index);
			new_calculate(index);
			// You can use g_name here
			
			var chartHTML = '<center><div class="divTable blueTable" id="chart">' +
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
			'</div></center>';


					document.getElementById('divContent').innerHTML = chartHTML;
					document.getElementById('iframe-container').style.display = 'none';
					
					document.getElementById('newButton').style.display = 'block';
					
		}, 100); // Adjust the timeout as needed
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
	
	function disableCheckbox() {
		const iframe = document.getElementById('birthIframe');
		const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
		const label = iframeDocument.querySelector('label.divBirthInputLable');
		if (label) {
			label.style.display = 'none';
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
			<button class="glow-button" id='newButton' onclick="window.location.reload();" style="display:none; font-size:12px; width:-webkit-fill-available; line-height: normal;">New</button>
			<button class="glow-button" id='calculatorBtn' onclick="window.open('numerologyCalc.php','_self')" style="width:-webkit-fill-available;line-height: normal;">Calculator</button>
		</center>
		
		<center><BR><p id='birthTitle' style="color:#660000;font-weight:bold; font-size:20px; text-align:center; margin:0px">DSJV எண் கணிதம்</p></center>
		<center><div class='name_number' id='name_number' style='text-align: -webkit-center;'></div></center>
		<center><div style='display: flex; overflow-x: auto; white-space: nowrap; text_align:center'><div class='py' id='py'></div></div></center>
		<BR>
		<center><div class='name_number' id='dob_result' style='text-align: -webkit-center;'></div></center>
		
		<BR><div id="divContent"></div>
	</div>	 

	
	<div class="iframe-container" id="iframe-container">
		<iframe id="birthIframe" src="Birth_input.php" name="resultFrame" frameborder="0" onload="disableCheckbox()"></iframe>
	</div>
	
	<BR>
	
	<center><p><span style='color:red;'>&#9679; பகை</span><span style='color:#32CD32'>&#9679; வசியம்</span></p></center>
	<center><p>*Uses Chaldean method </p></center>
	
</center>
</body>

</html>
