<?php

if(isset($_COOKIE['jamakol']) && $_COOKIE['jamakol']){
	$jamakol_checked = 'checked';
	$panchang_checked = '';
}
else if(isset($_COOKIE['panchang']) && $_COOKIE['panchang']){
	$jamakol_checked = '';
	$panchang_checked = 'checked';
}

echo <<<EOL
<div style='text-align: right;'><button class='actionBtnHome' id='setHomeButton' onClick="setHome()">Set home</button></div>
<div id="fullscreenDiv">
	<p style="color:#660000;font-weight:bold; font-size:20px; text-align:center">முகப்புத்திரை</p>
	<input type="radio" name="defaultHome" value="j" id="jamakol" $jamakol_checked >
	<label for="jamakol">ஜாமக்கோள்</label>
	<br>
	<br>
	<input type="radio" name="defaultHome" value="p" id="panchang" $panchang_checked >
	<label for="panchang">பஞ்சாங்கம்</label>
	<br>
	<br>
	<center><button class='actionBtn' id="btnSetDefaultHome" OnClick='setDefaultHome()'>Confirm</button></center>
</div>

EOL;

?>