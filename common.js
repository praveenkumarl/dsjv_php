/***** setup default home page *****/
function checkDefaultHome(){
	if (checkCookie('jamakol')) {
			return;
	}
	else if (checkCookie('panchang')) {
			window.location.href = 'new_panchang.php';
	}
	else {
		// Set 'jamakol' cookie (expires in 5 years)
        setCookie('jamakol', '1', 1825);
		console.log('Neither jamakol nor panchang cookie exist. Check the code'); //mostly will not execute unless both cookies are deleted
	}
}

// This opens the default home setup form
function setHome() {
    fullscreenDiv.style.display = 'block'; // Show the full-screen div
}

//this sets cookie to setup the default home page redirection
function setDefaultHome(){
	fullscreenDiv.style.display = 'none';
	
	const jamakol = document.getElementById('jamakol');
    const panchang = document.getElementById('panchang');
	
	if (jamakol.checked) {
		setCookie('jamakol');
		deleteCookie('panchang');
		showCustomDialog('ஜாமக்கோல் முகப்பாக மாற்றம் செய்யப்பட்டுள்ளது');
		console.log("Selected 'jamakol'. Route to Jamakol.");
	} else if (panchang.checked) {
		setCookie('panchang');
		deleteCookie('jamakol');
		showCustomDialog('பஞ்சாங்கம் முகப்பாக மாற்றம் செய்யப்பட்டுள்ளது');
		console.log("Selected 'panchang'. Route to Panchang.");
	} else {
		alert('விருப்பத்தை தேர்வு செய்யவும்');
		console.log("Please select an option.");
	}
}

/***** setup default home page *****/
	
function deleteCookie(cookieName) {
    document.cookie = `${cookieName}=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;`;
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function checkCookie(cookieName) {
	let cookies = document.cookie.split('; ');
	for (let i = 0; i < cookies.length; i++) {
		let [name, value] = cookies[i].split('=');
		if (name === cookieName) {
			return true; // Cookie exists
		}
	}
	return false; // Cookie does not exist
}


/****** custom dialog box ********/

 function showCustomDialog(msg) {
            // Create the custom dialog container
            const customDialog = document.createElement('div');
            customDialog.id = 'customDialog';
            customDialog.className = 'custom-dialog';

            // Create the dialog box
            const dialogBox = document.createElement('div');
            dialogBox.className = 'custom-dialog-box';
            dialogBox.textContent = msg;

            // Create the OK button
            const okButton = document.createElement('button');
            okButton.className = 'actionBtn';
            okButton.textContent = 'OK';
            okButton.onclick = closeCustomDialog;

            // Append elements to the custom dialog
            customDialog.appendChild(dialogBox);
            customDialog.appendChild(okButton);

            // Add the custom dialog to the document
            document.body.appendChild(customDialog);
        }

        function closeCustomDialog() {
            // Hide the custom dialog
            const customDialog = document.getElementById('customDialog');
            if (customDialog) {
                customDialog.style.display = 'none';
            }
        }
/****** custom dialog box ********/
