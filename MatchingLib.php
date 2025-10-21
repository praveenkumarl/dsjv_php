<?php
function isFilled($str)
{
	if(isset($str)) {
		echo "already has value";
		return 1;
	}
	else {
		return 0;
	}
}

function updatePlanet_transit(array $planets,array $retro, $in_ragu_house)
{
	global $DEBUG,$planetDeg_transit;
	
	if($DEBUG==1) {
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($planets);
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($retro);
	}
	
	
	$out = str_replace('Sun','சூரி',$planets);
	
	$out = str_replace('Moon','சந்',$out);
	
	if($retro[4]==1){
		$out = str_replace('Mars','செ',$out);
	}else{
		$out = str_replace('Mars','<span class="retro">செ</span>',$out);
	}
	
	if($retro[3]==1){
		$out = str_replace('Venus','சுக்',$out);
	}else{
		$out = str_replace('Venus','<span class="retro">சுக்</span>',$out);
	}
	
	if($retro[2]==1){
		$out = str_replace('Mercury','புத',$out);
	}else{
		$out = str_replace('Mercury','<span class="retro">புத</span>',$out);
	}
	
	if($retro[5]==1){
		$out = str_replace('Jupiter','குரு',$out);
	}else{
		$out = str_replace('Jupiter','<span class="retro">குரு</span>',$out);
	}
	
	if($retro[6]==1){
		$out = str_replace('Saturn','சனி',$out);
	}else{
		$out = str_replace('Saturn','<span class="retro">சனி</span>',$out);
	}
	
	//$out = str_replace('true Node','ரா',$out);
	$out = str_replace('mean Node','ரா',$out);
	$out = str_replace('Ascendant','<span class="lagna">லக்</span>',$out);
	
	//trim spaces
	$out = array_map('trim', $out);
	
	$Kethu_pos = findKethuPos($in_ragu_house);
	
	$out[$Kethu_pos] = 'கே ' . $out[$Kethu_pos];
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE PLANET after update</BR>";
		echo "<BR>KETHU position: ".$Kethu_pos . "</BR>";
		var_dump($out);
	}
	
	$planetDeg_transit['கே']=$planetDeg_transit['ரா'];
	asort($planetDeg_transit);
	
	return $out;
}

function updateMandhi_transit(array $out, array $in, $in_sun_degree, $in_day, $in_dayofweek,$in_sun_house)
{	
	global $transit_datetime,$transit_today_sunrise,$planetDeg_transit,$mandhi_degree_transit, $planetDeg_default_transit;
	
	if(strtotime($transit_datetime) < strtotime($transit_today_sunrise)){
		$in_dayofweek = $in_dayofweek - 1;
		
		if($in_dayofweek < 0){
			$in_dayofweek = 6;
		}
	}
		
	$mandhi_house = targetHouse($in_sun_house,findMandhiHouse_transit($in_dayofweek,$in_sun_degree, $in_day));
	
	debug($mandhi_house);
	
	foreach ($in as $key => $value) {
		//mandhi house update
		if($key == $mandhi_house){
			$out[$key] = $out[$key] . " மா";
			
			$planetDeg_transit["மா"] = ($mandhi_degree_transit % 30);
			$planetDeg_default_transit["மா"] = ($mandhi_degree_transit % 30);
		}
	}
	
	asort($planetDeg_transit);
	
	return $out;
}

function findMandhiHouse_transit($in_dayofweek,$in_sun_degree,$in_day)
{
	global $mandhi_degree_transit;
	
	switch ($in_dayofweek) {
		case 0: //Sun
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+156;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+240;
			}
			break;
		case 1: //Mon
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+132;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+216;
			}
			break;
		case 2: //Tue
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+108;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+192;
			}
			break;
		case 3: //Wed
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+84;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+336;
			}
			
			debug ("(((((((((".$mandhi_degree_transit."))))))))");
			break;
		case 4: //Thur
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+60;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+312;
			}
			break;
		case 5: //Fri
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+36;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+288;
			}
			break;
		case 6:  //Sat
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+12;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+264;
			}
			break;
	  default:
		break;
	}
	
	$mandhi_house = (int)(($mandhi_degree_transit % 360)/30);
	
	debug("FINDMANDHI Transit - WEEK DAY=" . $in_dayofweek);
	debug("FINDMANDHI Transit - DAYTIME=" . $in_day);
	debug("FINDMANDHI Transit - MANDHI DEGREE=" . ($mandhi_degree_transit % 30));
	debug("FINDMANDHI Transit - SUN DEGREE=" . $in_sun_degree);
	debug("FINDMANDHI Transit - MANDHI DEGREE FROM SUN=" . $mandhi_degree_transit);
	debug("FINDMANDHI Transit - MANDHI HOUSE=" . $mandhi_house);
	
	//update mandhi degree in degree array
	$tr_sw_degree["mandhi"] = DECtoDMS($mandhi_degree_transit % 30);
	
	return $mandhi_house;
}

//find house
function findHouse($inArray,$inStr)
{
	foreach($inArray as $house => $planets){
		if(strpos($planets,$inStr)===0 || strpos($planets,$inStr)>0){
			return $house;
		}
	}
}

// Lagna matching by position/raasi
function matchingRule1()
{
	global $sw_planet, $tr_sw_planet, $g_matching_mark;
	
	$result = false;
	
	$reason = "";
	
	$girlChart_lagna = findHouse($sw_planet,"லக்")+1;
	$boyChart_lagna = findHouse($tr_sw_planet,"லக்")+1;
	
	$girlLagna_to_boyLagna = countHouses($girlChart_lagna,$boyChart_lagna);
	$boyLagna_to_girlLagna = countHouses($boyChart_lagna,$girlChart_lagna);

	if(($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 1) ||     
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 1) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 3) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 5) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 7) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 9) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 11)){     
		$result = true;
		$reason = $girlLagna_to_boyLagna."/".$boyLagna_to_girlLagna;
	}
	else{
		$result = false;
		$reason = $girlLagna_to_boyLagna."/".$boyLagna_to_girlLagna;	
	}
	
	if($result == true){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>1. லக்கினம்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>1. லக்கினம்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	
}

//traditional star matching (11 points)
function matchingRule12()
{
	global $g_matching_mark, $g_girl_nak_pad_idx, $g_boy_nak_pad_idx;
	$result = false;
	$result_txt = "";
	
	$star_match_count = 0;
	
	$girl_to_boy_start_count = starCount(ceil($g_girl_nak_pad_idx/4), ceil($g_boy_nak_pad_idx/4));  //divide by 4 to get nak idx
	//1 தினம்
	$thinam = [2, 4, 6, 8, 9, 11, 13, 15, 17, 18, 20, 24, 26]; //removed 22 as it's vainasigam
	
	if(array_search($girl_to_boy_start_count,$thinam)){
		$result_txt = $result_txt . "தினம் பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "தினம் பொருத்தம் &#x2716;";
	}
	
	//2 கணம்
	if(ganaMatching())
	{
		$result_txt = $result_txt . "<BR>கண பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>கண பொருத்தம் &#x2716;";
	}
	
	//மகேந்திர பொருத்தம்
	$mahendra = [4,7,10,13,16,19,22,25];
	
	if(array_search($girl_to_boy_start_count,$mahendra)){
		$result_txt = $result_txt . "<BR>மகேந்திர பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>மகேந்திர பொருத்தம் &#x2716;";
	}
	
	//ஸ்திரீ தீர்க்கம்
	if($girl_to_boy_start_count>13){
		$result_txt = $result_txt . "<BR>ஸ்திரீ தீர்க்கம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ஸ்திரீ தீர்க்கம் &#x2716;";
	}
	
	//யோனி பொருத்தம்
	if(yoniMatch()){
		$result_txt = $result_txt . "<BR>யோனி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>யோனி பொருத்தம் &#x2716;";
	}
	
	//ராசி பொருத்தம்
	if(matchingRule2(false)){
		$result_txt = $result_txt . "<BR>ராசி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ராசி பொருத்தம் &#x2716;";
	}
	
	//ராசி அதிபதி பொருத்தம் 
	if(checkRaasiAthipathiMatching()){
		$result_txt = $result_txt . "<BR>ராசி அதிபதி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ராசி அதிபதி பொருத்தம் &#x2716;";
	}
	
	//வசிய பொருத்தம் 
	if(vasiyaMatching()){
		$result_txt = $result_txt . "<BR>வசிய பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>வசிய பொருத்தம் &#x2716;";
	}
	
	//ரஜ்ஜு பொருத்தம் 
	if(matchingRule8(false)){
		$result_txt = $result_txt . "<BR>ரஜ்ஜு பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ரஜ்ஜு பொருத்தம் &#x2716;";
	}
	
	//வேதை பொருத்தம் 
	if(matchingRule8(false)){
		$result_txt = $result_txt . "<BR>வேதை பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>வேதை பொருத்தம் &#x2716;";
	}
	
	//நாடி பொருத்தம் 
	if(naadiMatching()){
		$result_txt = $result_txt . "<BR>நாடி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>நாடி பொருத்தம் &#x2716;";
	}	
	
	if($star_match_count >= MIN_STAR_MATCH_COUNT){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>12. நட்சத்திர பொருத்தம்</div>";
			$final_result = "&#x2705;(".$star_match_count . "/" . TOTAL_STAR_MATCH_COUNT . ")" . "<BR>";
			echo "<div class='divPanchang'>$final_result<div class='divStarMatch'>$result_txt</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>12. நட்சத்திர பொருத்தம்</div>";
			$final_result = "&#x274C;(".$star_match_count . "/" . TOTAL_STAR_MATCH_COUNT . ")";
			echo "<div class='divPanchang'>$final_result<div class='divStarMatch'>$result_txt</div></div>";
		echo "</div>";
	}
}

//naadi poruththam
function naadiMatching(){
	global $g_boy_nak_pad_idx, $g_girl_nak_pad_idx;
	
	$result = true;
	
	$vaatha = [1,6,7,12,13,18,19,24,25]; //அசுவினி, திருவாதிரை, புனர்பூசம், உத்தரம், அஸ்தம், கேட்டை, மூலம், சதயம், பூரட்டாதி  
	$pithaa = [2,5,8,11,14,17,20,23,26]; //பரணி, மிருகசீரிடம், பூசம், பூரம், சித்திரை, அனுசம், பூராடம், அவிட்டம், உத்ரட்டாதி      
	$neer = [3,4,9,10,15,16,21,22,27]; //கார்த்திகை, ரோகிணி, ஆயில்யம், மகம், சுவாதி, விசாகம், உத்ராடம், திருவோணம், ரேவதி
	
	$girl_star = ceil($g_girl_nak_pad_idx/4);
	$boy_star = ceil($g_boy_nak_pad_idx/4);
	
	if(array_search($girl_star,$vaatha) && array_search($boy_star,$vaatha)){
		$result = false;
	}
	
	if(array_search($girl_star,$pithaa) && array_search($boy_star,$pithaa)){
		$result = false;
	}
	
	if(array_search($neer,$pithaa) && array_search($neer,$pithaa)){
		$result = false;
	}
	
	return $result;
	
}

//Girl/boy Sun/Sat/Rah/Kethu/Mars in 7th house, warning
function matchingRule13(){
	global $sw_planet, $tr_sw_planet,$g_matching_mark;
	
	$result=true;
	$reason="";
	
	$girl_sun = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சூரி"));
	$girl_sat = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சனி"));
	$girl_rah = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"ரா"));
	$girl_ket = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"கே"));
	$girl_mar = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"செ"));
	
	$boy_sun = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சூரி"));
	$boy_sat = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சனி"));
	$boy_rah = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"ரா"));
	$boy_ket = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"கே"));
	$boy_mar = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"செ"));
	
	if($girl_sun == 7 || $girl_sat == 7 || $girl_rah == 7 || $girl_ket == 7 || $girl_mar == 7
		|| $boy_sun == 7 || $boy_sat == 7 || $boy_rah == 7 || $boy_ket == 7 || $boy_mar == 7){
		$result = false;
		$reason = "7ஆம் இடத்தில் சுரி/சனி/ரா/கே/செ, மிக கவனமாக பொருத்தம் பார்த்து முடிவு எடுக்கவும்";
	}
	
	if($result == false){
		echo <<<EOL
		<div class="divDisclaimer" colspan=3>
			<H6 style='font-weight;color:red'>*$reason</H6>
		</div>
EOL;
	}
}


function vasiyaMatching()
{
	global $g_rasi_girl, $g_rasi_boy;
	
	$result = false;
	
	if($g_rasi_girl==1 and ($g_rasi_boy==5 or $g_rasi_boy==8)){
		$result = true;
	}
	
	if($g_rasi_girl==2 and ($g_rasi_boy==4 or $g_rasi_boy==7)){
		$result = true;
	}
	
	if($g_rasi_girl==3 and $g_rasi_boy==6){
		$result = true;
	}
	
	if($g_rasi_girl==4 and ($g_rasi_boy==8 or $g_rasi_boy==9)){
		$result = true;
	}
	
	if($g_rasi_girl==5 and $g_rasi_boy==10){
		$result = true;
	}
	
	if($g_rasi_girl==6 and ($g_rasi_boy==2 or $g_rasi_boy==12)){
		$result = true;
	}
	
	if($g_rasi_girl==7 and $g_rasi_boy==10){
		$result = true;
	}
	
	if($g_rasi_girl==8 and ($g_rasi_boy==4 or $g_rasi_boy==6)){
		$result = true;
	}
	
	if($g_rasi_girl==9 and $g_rasi_boy==12){
		$result = true;
	}
	
	if($g_rasi_girl==10 and $g_rasi_boy==11){
		$result = true;
	}
	
	if($g_rasi_girl==11 and $g_rasi_boy==12){
		$result = true;
	}
	
	if($g_rasi_girl==12 and $g_rasi_boy==10){
		$result = true;
	}
	
	return $result;
	
}

function checkRaasiAthipathiMatching()
{
	global $g_rasi_girl, $g_rasi_boy,$g_matching_mark;
	
	$result = true;
	$reason = "";
	
	$girlRasiLord = findAthipathi($g_rasi_girl-1);
	$boyRasiLord = findAthipathi($g_rasi_boy-1);
	
	if(strcmp($girlRasiLord,$boyRasiLord)==0){    //same rasi return true;
		return $result; //true
	}
	
	if((strcmp($girlRasiLord,"சூரி")==0 and strcmp($boyRasiLord,"சுக்")==0) //sun enemy to ven
		or (strcmp($boyRasiLord,"சுக்")==0 and strcmp($girlRasiLord,"சூரி")==0) //sun enemy to ven
		or (strcmp($girlRasiLord,"சூரி")==0 and strcmp($boyRasiLord,"சனி")==0) 		//sun enemy to sat
		or (strcmp($boyRasiLord,"சூரி")==0 and strcmp($girlRasiLord,"சனி")==0)){   //sun enemy to sat
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"செ")==0 and strcmp($boyRasiLord,"புத")==0) 		//mars enemy to mer
		or (strcmp($boyRasiLord,"செ")==0 and strcmp($girlRasiLord,"புத")==0) 	//mars enemy to mer
		or (strcmp($girlRasiLord,"செ")==0 and strcmp($boyRasiLord,"சனி")==0) 		//mar enemy to sat
		or (strcmp($boyRasiLord,"செ")==0 and strcmp($girlRasiLord,"சனி")==0)){   //mar enemy to sat
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"புத")==0 and strcmp($boyRasiLord,"சந்")==0) 		//mer enemy to moon
		or (strcmp($boyRasiLord,"புத")==0 and strcmp($girlRasiLord,"சந்")==0) 	//mer enemy to moon
		or (strcmp($girlRasiLord,"சந்")==0 and strcmp($boyRasiLord,"சனி")==0) 		//moo enemy to sat
		or (strcmp($boyRasiLord,"சந்")==0 and strcmp($girlRasiLord,"சனி")==0)){   //moo enemy to sat
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"குரு")==0 and strcmp($boyRasiLord,"சுக்")==0) 		//jup enemy to ven
		or (strcmp($boyRasiLord,"குரு")==0 and strcmp($girlRasiLord,"சுக்")==0) 		//jup enemy to ven
		or (strcmp($girlRasiLord,"குரு")==0 and strcmp($boyRasiLord,"புத")==0) 		//Jup enemy to mer
		or (strcmp($boyRasiLord,"குரு")==0 and strcmp($girlRasiLord,"புத")==0)){   //Jup enemy to mer
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"சுக்")==0 and strcmp($boyRasiLord,"சந்")==0) 		//ven enemy to moon
		or (strcmp($boyRasiLord,"சுக்")==0 and strcmp($girlRasiLord,"சந்")==0)){   //ven enemy to moon
		$result = false;
	}
	
	return $result;
}


function displayMatchingResult()
{
	global $g_matching_mark;
	
	$matching_percent = ceil($g_matching_mark/TOTAL_MATCH_RULES * 100);
	
	if($matching_percent >=70) //possible good match
	{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>பொருத்த மதிப்பு</div>";
			echo "<div class='divMatchingResultGreen'>$matching_percent%</div>";
		echo "</div>";
	}
	elseif($matching_percent >50 && $matching_percent <70) //average match
	{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>பொருத்த மதிப்பு</div>";
			echo "<div class='divMatchingResultOrange'>$matching_percent%</div>";
		echo "</div>";
	}
	else{   //bad match
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>பொருத்த மதிப்பு</div>";
			echo "<div class='divMatchingResultRed'>$matching_percent%</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow'>";
		echo "<div class='divPanchangHeading' style='text-align:left'>மதிப்பு விவரம்</div>";
		echo "<div class='divPanchang' style='text-align:left;line-height:15px'>
		<span id='boot-icon' class='bi bi-x-square-fill' style='color: #ff8080;'></span> தவிர்க்க (0-50)<BR>
		<span id='boot-icon' class='bi bi-dash-square-fill' style='color: #FFBF00;'></span> சராசரி (50-69)<BR>
		<span id='boot-icon' class='bi bi-check-square-fill' style='color: #00FF00;'></span> உத்தமம் (70-100)
		</div>";
	echo "</div>";
	
	echo "<div style='display:none' id='matching_mark'>$matching_percent%</div>";
}

// The house where Mars placed should not have Rahu, Sani in matching chart and viz versa
function matchingRule122()
{
	global $sw_planet, $tr_sw_planet;
	
	$boyChart_mars = findHouse($sw_planet,"செ");
	$boyChart_sat = findHouse($sw_planet,"சனி");
	$boyChart_rah = findHouse($sw_planet,"ரா");
	
	$girlChart_mars = findHouse($tr_sw_planet,"செ");
	$girlChart_sat = findHouse($tr_sw_planet,"சனி");
	$girlChart_rah = findHouse($tr_sw_planet,"ரா");
	
	if($boyChart_mars == $girlChart_sat || $boyChart_mars == $girlChart_rah){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>Rahu/Sani over Mars</div>";
			echo "<div class='divPanchangRed'>&#x274C;</div>";
		echo "</div>";
	}
	else if ($girlChart_mars == $boyChart_sat || $girlChart_mars == $boyChart_rah){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Rahu/Sani over Mars</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Rahu/Sani over Mars)</div>";
		echo "</div>";
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Rahu/Sani over Mars</div>";
			echo "<div class='divPanchangGreen'>&#x2705; (Rahu/Sani not over Mars)</div>";
		echo "</div>";
	}
}

// male/female raasi matching (Moon)
function matchingRule2($display)
{
	global $sw_planet, $tr_sw_planet,$g_matching_mark, $g_rasi_girl, $g_rasi_boy;
	
	$g_rasi_girl = findHouse($sw_planet,"சந்")+1; 
	$g_rasi_boy = findHouse($tr_sw_planet,"சந்")+1; 
	
	if($g_rasi_boy % 2 == 1 && ($g_rasi_girl % 2 == 1 || $g_rasi_girl % 2 == 0 )){
		$result = true;
		$reason = "<BR>" . "பெண்-" . ($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	elseif(($g_rasi_girl % 2 == 0 && ($g_rasi_boy % 2 == 1 || $g_rasi_boy % 2 == 0 ))){
		$result = true;
		$reason = "<BR>". "பெண்-" .($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	elseif($g_rasi_girl % 2 == 1 && $g_rasi_boy % 2 == 1){
		$result = true;
		$reason = "<BR>" . "பெண்-" .($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	else{
		$result = false;
		$reason = "<BR>" . "பெண்-" .($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	
	if(!$display){
		return $result;
	}
	
	if($result == false){
		//echo "---Rasi rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>2. ராசி/சந்திரன்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Rasi rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>2. ராசி/சந்திரன்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	
	return $result;
}

// One Neesa graka should not be neesa in another chart viz versa
function matchingRule22()
{
	global $sw_planet, $tr_sw_planet;
	
	if(findHouse($sw_planet,"செ")==3 && findHouse($tr_sw_planet,"செ")==3){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Mar/செ)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சூரி")==6 && findHouse($tr_sw_planet,"சூரி")==6){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Sun/சூரி)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சந்")==7 && findHouse($tr_sw_planet,"சந்")==7){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Moo/சந்)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"புத")==11 && findHouse($tr_sw_planet,"புத")==11){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Mer/புத)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சுக்")==5 && findHouse($tr_sw_planet,"சுக்")==5){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Ven/சுக்)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"குரு")==9 && findHouse($tr_sw_planet,"குரு")==9){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Jup/குரு)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சனி")==0 && findHouse($tr_sw_planet,"சனி")==0){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Sat/சனி)</div>";
		echo "</div>";
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangGreen'>&#x2705;</div>";
		echo "</div>";
	}
}

// Karanam matching rule
function matchingRule3()
{
	global $sw_planet, $tr_sw_planet, $g_girl_karan, $g_boy_karan,$g_matching_mark;
	
	$karan = array("Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Sakuni/சகுனி","Chatuspada/சதுஷ்பாத","Naga/நாகவ","Kimstughna/கிம்ஸ்துக்கினம்");
	$karan_planet = array("செ","ரா","சனி","சுக்","சந்","சூரி","கே","சனி","குரு","ரா","புத");
	
	$result = false;
	$reason = "";
	
	$girl_karan_planet = array_search($g_girl_karan,$karan);
	$boy_karan_planet = array_search($g_boy_karan,$karan);
	
	debug('GIRL KARAN IDX='.$girl_karan_planet);
	debug('BOY KARAN IDX='.$boy_karan_planet);
	
	if(($girl_karan_planet==5 and $boy_karan_planet==3) //sun enemy to ven
		or ($girl_karan_planet==3 and $boy_karan_planet==5) //sun enemy to ven
		or ($girl_karan_planet==5 and $boy_karan_planet==2 or $boy_karan_planet==7) 		//sun enemy to sat
		or ($boy_karan_planet==5 and ($girl_karan_planet==2 or $girl_karan_planet==7))){   //sun enemy to sat
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==0 and $boy_karan_planet==10) //mars enemy to mer
		or ($girl_karan_planet==10 and $boy_karan_planet==0)){   
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==10 and $boy_karan_planet==4) //mer enemy to moon
			or ($girl_karan_planet==4 and $boy_karan_planet==10) //moon enemy to mer
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==8 and $boy_karan_planet==10) 	//Jup enemy to mer
			or ($girl_karan_planet==10 and $boy_karan_planet==8) //mer enemy to jup
			or ($girl_karan_planet==10 and $boy_karan_planet==3) //jup enemy to ven
			or ($girl_karan_planet==3 and $boy_karan_planet==10) //ven enemy to jup
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==7 and $boy_karan_planet==5) 	//sat enemy to sun
			or ($girl_karan_planet==5 and $boy_karan_planet==7) //sun enemy to sat
			or ($girl_karan_planet==7 and $boy_karan_planet==4) //sat enemy to moo
			or ($girl_karan_planet==4 and $boy_karan_planet==7) //moo enemy to sat
			or ($girl_karan_planet==7 and $boy_karan_planet==0) //sat enemy to mar
			or ($girl_karan_planet==0 and $boy_karan_planet==7) //mar enemy to sat
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==3 and $boy_karan_planet==5) 	//ven enemy to sun
			or ($girl_karan_planet==5 and $boy_karan_planet==3) //sun enemy to ven
			or ($girl_karan_planet==3 and $boy_karan_planet==4) //ven enemy to moo
			or ($girl_karan_planet==4 and $boy_karan_planet==3) //moo enemy to ven
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif($girl_karan_planet == $boy_karan_planet){
		$result = true;
		$reason = "<BR>" . $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] ;
	}
	else{
		$result = true;
		$reason = "<BR>" . $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] ;
	}
	
	if($result == false){
		//echo "---Karana rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>3. கரணம்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Karana rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>3. கரணம்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

// Lagna lords should not be 6 or 8. Also lagna should not be in the 6th or 8th to Venues and sukran
function matchingRule4()
{
	global $sw_planet, $tr_sw_planet,$g_matching_mark;
	
	$result = array(true,true,true);
	$reason = "";
	
	$girlLagnaHouse = findHouse($sw_planet, "லக்");
	$boyLagnaHouse = findHouse($tr_sw_planet, "லக்");
	
	$girlLagnaLord = findAthipathi($girlLagnaHouse);
	$boyLagnaLord = findAthipathi($boyLagnaHouse);
	
	$girlVenHouse = findHouse($sw_planet, "சுக்");
	$boyVenHouse = findHouse($tr_sw_planet, "சுக்");
	
	$girlMarHouse = findHouse($sw_planet, "செ");
	$boyMarHouse = findHouse($tr_sw_planet, "செ");
	
	$girlLagnaLordHouse = findHouse($sw_planet, $girlLagnaLord);
	$boyLagnaLordHouse = findHouse($tr_sw_planet, $boyLagnaLord);
	
	//$girlBoyLagnaCount = countHouses($girlLagnaHouse, $boyLagnaHouse);
	$girlChart_lagna = findHouse($sw_planet,"லக்")+1;
	$boyChart_lagna = findHouse($tr_sw_planet,"லக்")+1;
	
	$girlLagna_to_boyLagna = countHouses($girlChart_lagna,$boyChart_lagna);
	$boyLagna_to_girlLagna = countHouses($boyChart_lagna,$girlChart_lagna);
	
	$girlLagnaLordBoyVenCount = countHouses($girlLagnaLordHouse, $boyVenHouse);
	$boyLagnaLordGirlVenCount = countHouses($boyLagnaLordHouse, $girlVenHouse);
	
	$girlLagnaLordBoyMarCount = countHouses($girlLagnaLordHouse, $boyMarHouse);
	$boyLagnaLordGirlMarCount = countHouses($boyLagnaLordHouse, $girlMarHouse);
	
	//if($girlBoyLagnaCount == 6 || $girlBoyLagnaCount == 8){
	if($girlLagna_to_boyLagna == 6 || $boyLagna_to_girlLagna == 8){
		//echo "<br>Lagna 6/8";
		$reason = "<BR>&#x2022; லக்கினம் 6/8";
		$result[0] = false;
	}
	else{
		$reason = "<BR>லக்கினம் ".$girlLagna_to_boyLagna.'/'.$boyLagna_to_girlLagna;
		$result[0] = true;
	}
	
	if($girlLagnaLordBoyVenCount == 6 || $girlLagnaLordBoyVenCount == 8){
		$result[1] = false;
		//echo "<br>Girl Venus-Boy lagna lord 6/8";
		$reason = $reason . "<BR>&#x2022; பெண் லக்கினாதிபதி & ஆண் சுக் 6/8";
	}
	else{
		$reason = $reason  . "<BR>&#x2022; பெண் லக்கினாதிபதி & ஆண் சுக் ". "(" . $girlLagnaLordBoyVenCount . ")";
		$result[1] = true;
	}
	
	if($boyLagnaLordGirlMarCount == 6 || $boyLagnaLordGirlMarCount == 8){
		$result[2] = false;
		//echo "<br>Boy Mars-Girl lagna lord 6/8";
		$reason = $reason . "<BR>&#x2022; ஆண் லக்கினாதிபதி & பெண் செ 6/8";
	}
	else{
		$reason = $reason  . "<BR>&#x2022; ஆண் லக்கினாதிபதி & பெண் செ ". "(" . $boyLagnaLordGirlMarCount . ")";
		$result[2] = true;
	}
	
	if($result[0] == true && $result[1] == true && $result[2] == true){
		//echo "<br>Lagna lord rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>4. ஆண்/பெண்<BR>(லக்/செவ்/சுக்/2/7)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	else{
		//echo "---Lagna lord rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>4. ஆண்/பெண்<BR>(லக்/செவ்/சுக்/2/7)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
}

// Lagna matching, panjcha bootha, paathaga
function matchingRule41()
{
	global $sw_planet, $tr_sw_planet;
	
	$result = false;
	
	$reason = "";
	
	$boyChart_lagna = findHouse($sw_planet,"லக்");
	$girlChart_lagna = findHouse($tr_sw_planet,"லக்");

	if($boyChart_lagna == $girlChart_lagna){
		$result = true;
	}
	elseif(($boyChart_lagna == 0 && $girlChart_lagna == 4) ||      //based on pancha bootha fire x fire
			($boyChart_lagna == 0 && $girlChart_lagna == 8) ||     // fire x fire
			($boyChart_lagna == 4 && $girlChart_lagna == 8) ||     // fire x fire
			($boyChart_lagna == 1 && $girlChart_lagna == 5) ||     // land x land
			($boyChart_lagna == 1 && $girlChart_lagna == 9) ||     // land x land
			($boyChart_lagna == 5 && $girlChart_lagna == 9) ||     // land x land
			($boyChart_lagna == 2 && $girlChart_lagna == 6) ||     // air x air
			($boyChart_lagna == 2 && $girlChart_lagna == 10) ||    // air x air
			($boyChart_lagna == 6 && $girlChart_lagna == 10) ||    // air x air
			($boyChart_lagna == 3 && $girlChart_lagna == 7) ||     // water x water
			($boyChart_lagna == 3 && $girlChart_lagna == 11) ||    // water x water
			($boyChart_lagna == 7 && $girlChart_lagna == 11) ||    // water x water
			($boyChart_lagna == 0 && $girlChart_lagna == 2) ||     // fire x air
			($boyChart_lagna == 0 && $girlChart_lagna == 5) ||     // fire x air
			($boyChart_lagna == 0 && $girlChart_lagna == 10) ||    // fire x air
			($boyChart_lagna == 2 && $girlChart_lagna == 0) ||     // air x fire
			($boyChart_lagna == 5 && $girlChart_lagna == 0) ||     // air x fire
			($boyChart_lagna == 10 && $girlChart_lagna == 0) ||    // air x fire
			($boyChart_lagna == 1 && $girlChart_lagna == 3) ||     // land x water
			($boyChart_lagna == 1 && $girlChart_lagna == 7) ||     // land x water
			($boyChart_lagna == 1 && $girlChart_lagna == 11) ||    // land x water
			($boyChart_lagna == 3 && $girlChart_lagna == 1) ||     // water x land
			($boyChart_lagna == 7 && $girlChart_lagna == 1) ||     // water x land
			($boyChart_lagna == 11 && $girlChart_lagna == 1) ||     // water x land
			($boyChart_lagna == 4 && $girlChart_lagna == 10) ||     // fire x air
			($boyChart_lagna == 4 && $girlChart_lagna == 6) ||      // fire x air
			($boyChart_lagna == 4 && $girlChart_lagna == 2)){       // fire x air
		$result = true;
	}
	else{
		$result = false;
		$reason = "Pancha Bootha";
	}
	
	if(($boyChart_lagna == 0 && $girlChart_lagna == 10) || //paathaga sthana
		($boyChart_lagna == 1 && $girlChart_lagna == 8) || 
		($boyChart_lagna == 2 && $girlChart_lagna == 8) ||
		($boyChart_lagna == 3 && $girlChart_lagna == 1) ||
		($boyChart_lagna == 4 && $girlChart_lagna == 0) ||
		($boyChart_lagna == 5 && $girlChart_lagna == 11) ||
		($boyChart_lagna == 6 && $girlChart_lagna == 4) ||
		($boyChart_lagna == 7 && $girlChart_lagna == 3) ||
		($boyChart_lagna == 8 && $girlChart_lagna == 2) ||
		($boyChart_lagna == 9 && $girlChart_lagna == 7) ||
		($boyChart_lagna == 10 && $girlChart_lagna == 6) ||
		($boyChart_lagna == 11 && $girlChart_lagna == 5)){
		//echo "<BR>Lagna rule failed (paathaga)";
		$result = false;
		$reason = "Pathaga Sthaana";
	}
	
	if($result == true){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Lagna match (Pancha Pootha/Pathaga)</div>";
			echo "<div class='divPanchangGreen'>&#x2705;</div>";
		echo "</div>";
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Lagna match (Pancha Pootha/Pathaga)</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	
}

//this function fills all planet aspects in girl chart
function fillPlanetAspectsGirl(){
	global $sw_planet, $lagna_house;
	global $g_planet_asp_girl;
	$sun=array();
	$moo=array();
	$mer=array();
	$ven=array();
	$mars=array();
	$jup=array();
	$sat=array();
	$rah=array();
	$ket=array();
			
	foreach($sw_planet as $idx => $value){
		if(strpos($value,"சூரி")===0 or strpos($value,"சூரி")>0){
			array_push($sun,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சந்")===0 or strpos($value,"சந்")>0){
			array_push($moo,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"புத")===0 or strpos($value,"புத")>0){
			array_push($mer,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சுக்")===0 or strpos($value,"சுக்")>0){
			array_push($ven,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"செ")===0 or strpos($value,"செ")>0){
			array_push($mars,countHouses($lagna_house,targetHouse($idx+1,4)));
			array_push($mars,countHouses($lagna_house,targetHouse($idx+1,7)));
			array_push($mars,countHouses($lagna_house,targetHouse($idx+1,8)));
		}
		
		if(strpos($value,"குரு")===0 or strpos($value,"குரு")>0){
			array_push($jup,countHouses($lagna_house,targetHouse($idx+1,5)));
			array_push($jup,countHouses($lagna_house,targetHouse($idx+1,7)));
			array_push($jup,countHouses($lagna_house,targetHouse($idx+1,9)));
		}
		
		if(strpos($value,"சனி")===0 or strpos($value,"சனி")>0){
			array_push($sat,countHouses($lagna_house,targetHouse($idx+1,3)));
			array_push($sat,countHouses($lagna_house,targetHouse($idx+1,7)));
			array_push($sat,countHouses($lagna_house,targetHouse($idx+1,10)));
		}
		
		if(strpos($value,"ரா")===0 or strpos($value,"ரா")>0){
			array_push($rah,countHouses($lagna_house,targetHouse($idx+1,3)));
			array_push($rah,countHouses($lagna_house,targetHouse($idx+1,11)));
		}
		
		if(strpos($value,"கே")===0 or strpos($value,"கே")>0){
			array_push($ket,countHouses($lagna_house,targetHouse($idx+1,3)));
			array_push($ket,countHouses($lagna_house,targetHouse($idx+1,11)));
		}
	}
	
	$g_planet_asp_girl["சூரி"] = $sun;
	$g_planet_asp_girl["சந்"] = $moo;
	$g_planet_asp_girl["புத"] = $mer;
	$g_planet_asp_girl["சுக்"] = $ven;
	$g_planet_asp_girl["செ"] = $mars;
	$g_planet_asp_girl["குரு"] = $jup;
	$g_planet_asp_girl["சனி"] = $sat;
	$g_planet_asp_girl["ரா"] = $rah;
	$g_planet_asp_girl["கே"] = $ket;
}

//this function fills all planet aspects in boy chart
function fillPlanetAspectsBoy(){
	global $tr_sw_planet, $transit_lagna_house;
	global $g_planet_asp_boy;
	$sun=array();
	$moo=array();
	$mer=array();
	$ven=array();
	$mars=array();
	$jup=array();
	$sat=array();
	$rah=array();
	$ket=array();
			
	foreach($tr_sw_planet as $idx => $value){
		if(strpos($value,"சூரி")===0 or strpos($value,"சூரி")>0){
			array_push($sun,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சந்")===0 or strpos($value,"சந்")>0){
			array_push($moo,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"புத")===0 or strpos($value,"புத")>0){
			array_push($mer,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சுக்")===0 or strpos($value,"சுக்")>0){
			array_push($ven,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"செ")===0 or strpos($value,"செ")>0){
			array_push($mars,countHouses($transit_lagna_house,targetHouse($idx+1,4)));
			array_push($mars,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
			array_push($mars,countHouses($transit_lagna_house,targetHouse($idx+1,8)));
		}
		
		if(strpos($value,"குரு")===0 or strpos($value,"குரு")>0){
			array_push($jup,countHouses($transit_lagna_house,targetHouse($idx+1,5)));
			array_push($jup,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
			array_push($jup,countHouses($transit_lagna_house,targetHouse($idx+1,9)));
		}
		
		if(strpos($value,"சனி")===0 or strpos($value,"சனி")>0){
			array_push($sat,countHouses($transit_lagna_house,targetHouse($idx+1,3)));
			array_push($sat,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
			array_push($sat,countHouses($transit_lagna_house,targetHouse($idx+1,10)));
		}
		
		if(strpos($value,"ரா")===0 or strpos($value,"ரா")>0){
			array_push($rah,countHouses($transit_lagna_house,targetHouse($idx+1,3)));
			array_push($rah,countHouses($transit_lagna_house,targetHouse($idx+1,11)));
		}
		
		if(strpos($value,"கே")===0 or strpos($value,"கே")>0){
			array_push($ket,countHouses($transit_lagna_house,targetHouse($idx+1,3)));
			array_push($ket,countHouses($transit_lagna_house,targetHouse($idx+1,11)));
		}
	}
	
	$g_planet_asp_boy["சூரி"] = $sun;
	$g_planet_asp_boy["சந்"] = $moo;
	$g_planet_asp_boy["புத"] = $mer;
	$g_planet_asp_boy["சுக்"] = $ven;
	$g_planet_asp_boy["செ"] = $mars;
	$g_planet_asp_boy["குரு"] = $jup;
	$g_planet_asp_boy["சனி"] = $sat;
	$g_planet_asp_boy["ரா"] = $rah;
	$g_planet_asp_boy["கே"] = $ket;
}

//mars & saturn aspect
function matchingRule5()
{
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_matching_mark;
	global $g_planet_asp_girl,$g_planet_asp_boy;
	
	$common_mars_sat_house = array();
	
	$result = true;
	$reason = "இல்லை";
			
	fillPlanetAspectsGirl();
	fillPlanetAspectsBoy();
	
	$mar_sat_asp_girl = array_intersect($g_planet_asp_girl["செ"],$g_planet_asp_girl["சனி"]);
	$mar_sat_asp_boy = array_intersect($g_planet_asp_boy["செ"],$g_planet_asp_boy["சனி"]);
	
	if(!empty(array_search(7,$mar_sat_asp_girl)) 
		and !empty(array_search(7,$mar_sat_asp_boy)))
	{
		$result=false;
		array_push($common_mars_sat_house,"7");
	}
	
	if(!empty(array_search(5,$mar_sat_asp_girl)) 
		and !empty(array_search(5,$mar_sat_asp_boy)))
	{
		$result=false;
		array_push($common_mars_sat_house,"5");
	}
	
	if(empty($mar_sat_asp_girl)){
		array_push($mar_sat_asp_girl,"இல்லை");
	}
	
	if(empty($mar_sat_asp_boy)){
		array_push($mar_sat_asp_boy,"இல்லை");
	}
	
	if($result == false){
		//echo "---damaged house match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>5. செ/சனி பார்வை (5/7)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>".
				"பெண்-".join('-',$mar_sat_asp_girl).
				"<BR>".
				"ஆண்-".join('-',$mar_sat_asp_boy).
				"</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>damaged house match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>5. செ/சனி பார்வை (5/7)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>".
			"பெண்-".join('-',$mar_sat_asp_girl).
				"<BR>".
				"ஆண்-".join('-',$mar_sat_asp_boy).
				"</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

//இருண்ட வீடு  - எந்த கிரகமும் இல்லாத, எந்த கிரகத்தின் பார்வையும் இல்லாத வீடு இருண்ட வீடு 
function matchingRule6()
{
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_planet_asp_girl,$g_planet_asp_boy,$g_matching_mark;
	
	$result = false;
	
	$dark_house_girl = array();
	$dark_house_boy = array();
	
	foreach($sw_planet as $idx => $value){
		if(empty($value)){
			array_push($dark_house_girl,countHouses($lagna_house,$idx+1));
		}
		elseif(!(strpos($value,"சுக்")===0 or 
				strpos($value,"புத")===0 or
				strpos($value,"குரு")===0 or
				strpos($value,"சனி")===0 or
				strpos($value,"ரா")===0 or
				strpos($value,"கே")===0 or
				strpos($value,"செ")===0 or
				strpos($value,"சூரி")===0 or
				strpos($value,"சந்")===0 or
				
				strpos($value,"சுக்")>0 or
				strpos($value,"புத")>0 or
				strpos($value,"குரு")>0 or
				strpos($value,"சனி")>0 or
				strpos($value,"ரா")>0 or
				strpos($value,"கே")>0 or
				strpos($value,"செ")>0 or
				strpos($value,"சூரி")>0 or
				strpos($value,"சந்")>0 )){
					array_push($dark_house_girl,countHouses($lagna_house,$idx+1));
				}		
	}
	
	foreach($tr_sw_planet as $idx => $value){
		if(empty($value)){
			array_push($dark_house_boy,countHouses($transit_lagna_house,$idx+1));
		}
		elseif(!(strpos($value,"சுக்")===0 or 
				strpos($value,"புத")===0 or
				strpos($value,"குரு")===0 or
				strpos($value,"சனி")===0 or
				strpos($value,"ரா")===0 or
				strpos($value,"கே")===0 or
				strpos($value,"செ")===0 or
				strpos($value,"சூரி")===0 or
				strpos($value,"சந்")===0 or
				
				strpos($value,"சுக்")>0 or
				strpos($value,"புத")>0 or
				strpos($value,"குரு")>0 or
				strpos($value,"சனி")>0 or
				strpos($value,"ரா")>0 or
				strpos($value,"கே")>0 or
				strpos($value,"செ")>0 or
				strpos($value,"சூரி")>0 or
				strpos($value,"சந்")>0 )){
					array_push($dark_house_boy,countHouses($transit_lagna_house,$idx+1));
				}
	}
	
	sort($dark_house_girl);
	sort($dark_house_boy);
	
	//find aspecting house and remove it from dark house array for girl
	$temp=array_diff($dark_house_girl,$g_planet_asp_girl["சூரி"]);
	$temp=array_diff($temp,$g_planet_asp_girl["சந்"]);
	$temp=array_diff($temp,$g_planet_asp_girl["புத"]);
	$temp=array_diff($temp,$g_planet_asp_girl["சுக்"]);
	$temp=array_diff($temp,$g_planet_asp_girl["செ"]);
	$temp=array_diff($temp,$g_planet_asp_girl["குரு"]);
	$temp=array_diff($temp,$g_planet_asp_girl["சனி"]);
	$temp=array_diff($temp,$g_planet_asp_girl["ரா"]);
	$temp=array_diff($temp,$g_planet_asp_girl["கே"]);
	
	$dark_house_girl = $temp;
	
	$temp = array(); //empty the temp variable
	//find aspecting house and remove it from dark house array for boy
	$temp=array_diff($dark_house_boy,$g_planet_asp_boy["சூரி"]);
	$temp=array_diff($temp,$g_planet_asp_boy["சந்"]);
	$temp=array_diff($temp,$g_planet_asp_boy["புத"]);
	$temp=array_diff($temp,$g_planet_asp_boy["சுக்"]);
	$temp=array_diff($temp,$g_planet_asp_boy["செ"]);
	$temp=array_diff($temp,$g_planet_asp_boy["குரு"]);
	$temp=array_diff($temp,$g_planet_asp_boy["சனி"]);
	$temp=array_diff($temp,$g_planet_asp_boy["ரா"]);
	$temp=array_diff($temp,$g_planet_asp_boy["கே"]);
	
	$dark_house_boy = $temp;
	
	if(empty($dark_house_boy)){
		$result = true;
		$dark_house_boy=array("இல்லை");
	}
	
	if(empty($dark_house_girl)){
		$result = true;
		$dark_house_girl=array("இல்லை");
	}
	
	if($result == false){
		//echo "---damaged house match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>6. இருண்ட வீடுகள்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>".
				"பெண் ".join('-',$dark_house_girl).
				"<BR>".
				"ஆண் ".join('-',$dark_house_boy).
				"</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>damaged house match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>6. இருண்ட வீடுகள்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>".
			"பெண் ".join('-',$dark_house_girl).
				"<BR>".
				"ஆண் ".join('-',$dark_house_boy).
				"</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

//ஆண்/பெண் பாதிக்கப்பட்ட  பாவம் மற்றும் கிரகம் 
function matchingRule7(){
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_planet_asp_girl,$g_planet_asp_boy,$g_matching_mark;
	
	$result=true;
	$reason="இல்லை";
	
	$damaged_house_girl = array();
	$damaged_house_boy = array();
	$common_damaged_house = array();
	
	$girl_mars_aspect = $g_planet_asp_girl["செ"];
	$girl_sat_aspect = $g_planet_asp_girl["சனி"];
	$girl_rahu_aspect = $g_planet_asp_girl["ரா"];
	$girl_kethu_aspect = $g_planet_asp_girl["கே"];
	
	$boy_mars_aspect = $g_planet_asp_boy["செ"];
	$boy_sat_aspect = $g_planet_asp_boy["சனி"];
	$boy_rahu_aspect = $g_planet_asp_boy["ரா"];
	$boy_kethu_aspect = $g_planet_asp_boy["கே"];
		
	$damaged_house_girl[0] = "செ"."(".join('-',$girl_mars_aspect).")";
	$damaged_house_girl[1] = "சனி"."(".join('-',$girl_sat_aspect).")";
	$damaged_house_girl[2] = "ரா"."(".join('-',$girl_rahu_aspect).")";
	$damaged_house_girl[3] = "கே"."(".join('-',$girl_kethu_aspect).")";

	$damaged_house_boy[0] = "செ"."(".join('-',$boy_mars_aspect).")";
	$damaged_house_boy[1] = "சனி"."(".join('-',$boy_sat_aspect).")";
	$damaged_house_boy[2] = "ரா"."(".join('-',$boy_rahu_aspect).")";
	$damaged_house_boy[3] = "கே"."(".join('-',$boy_kethu_aspect).")";
	
	$all_aspects_girl = array_merge($girl_mars_aspect,$girl_sat_aspect,$girl_rahu_aspect,$girl_kethu_aspect);
	$all_aspects_boy = array_merge($boy_mars_aspect,$boy_sat_aspect,$boy_rahu_aspect,$boy_kethu_aspect);
	
	if(is_numeric(array_search(7,$all_aspects_girl))
		and is_numeric(array_search(7,$all_aspects_boy)))
	{
		$result=false;
		array_push($common_damaged_house,"7");
	}
	
	if(is_numeric(array_search(5,$all_aspects_girl)) 
		and is_numeric(array_search(5,$all_aspects_boy)))
	{
		$result=false;
		array_push($common_damaged_house,"5");
	}
		
	if(is_numeric(array_search(2,$all_aspects_girl)) 
		and is_numeric(array_search(2,$all_aspects_boy)))
	{
		$result=false;
		array_push($common_damaged_house,"2");
	}
	
	if(!empty($common_damaged_house)){
		$result=false;
		$reason = join('-',$common_damaged_house);
	}
	
	
	if($result == false){
		//echo "---damaged house match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>7. பாதிக்கப்பட்ட வீடுகள் (2/5/7)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>பாதிப்பு - $reason<BR>".
				"<BR>பெண்<BR>".join('<BR>',$damaged_house_girl).
				"<BR>".
				"<BR>ஆண்<BR>".join('<BR>',$damaged_house_boy).
				"</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>damaged house match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>7. பாதிக்கப்பட்ட வீடுகள் (2/5/7)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>பாதிப்பு - $reason<BR>".
			"<BR>பெண்<BR>".join('<BR>',$damaged_house_girl).
				"<BR>".
				"<BR>ஆண்<BR>".join('<BR>',$damaged_house_boy).
				"</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

// Guna or Gana star poruththam
function ganaMatching()
{
	global $sw_planet, $tr_sw_planet, $g_girl_nak, $g_boy_nak;
	
	$sath_ganam = array("அசுவினி","மிருகசிரீஷம்", "புனர்பூசம்", "பூசம்", "ஹஸ்தம்", "சுவாதி", "அனுஷம்", "திருவோணம்", "ரேவதி"); //sathveega
	$raja_ganam = array("பரணி", "ரோகிணி", "திருவாதிரை", "பூரம்", "உத்திரம்", "பூராடம்", "உத்திராடம்", "பூரட்டாதி","உத்திரட்டாதி"); //thamo
	$tham_ganam = array("கிருத்திகை", "ஆயில்யம்", "மகம்", "சித்திரை", "விசாகம்", "கேட்டை", "மூலம்", "அவிட்டம்","சதயம்"); //Rakshasha
	
	$result = false;
	$reason = "";
	
	if(in_array($g_girl_nak,$sath_ganam)){
		$girl_guna=1;
	}
	elseif(in_array($g_girl_nak,$raja_ganam)){
		$girl_guna=2;
	}
	elseif(in_array($g_girl_nak,$tham_ganam)){
		$girl_guna=3;
	}
	
	if(in_array($g_boy_nak,$sath_ganam)){
		$boy_guna=1;
	}
	elseif(in_array($g_boy_nak,$raja_ganam)){
		$boy_guna=2;
	}
	elseif(in_array($g_boy_nak,$tham_ganam)){
		$boy_guna=3;
	}
	
	if($girl_guna==$boy_guna){  //sathveegam+sathveegam or rajasam+rajasam or thamasom+thamasom
		debug("matched");
		$reason="ஒரே குணம்";
		$result=true;
	}
	elseif($girl_guna==1 && $boy_guna==2){   //sathveegam+rajasam
		debug("matched");
		$reason="சாத்வீக+ராஜஸ";
		$result=true;
	}
	elseif($girl_guna==1 && $boy_guna==2){   //sathveegam+thamasom
		debug("50% matched");
		$reason="சாத்வீக+தமோ-50%";
		$result=true;
	}
	elseif($girl_guna==2 && $boy_guna==1){  //rajasam+sathveegam
		debug("matched");
		$reason="ராஜஸ+சாத்வீக";
		$result=true;
	}
	elseif($girl_guna==2 && $boy_guna==3){   //rajasam+thamasom
		debug("not matched");
		$reason = "ராஜஸ+தமோ";
		$result=false;
	}
	elseif($girl_guna==3 && $boy_guna==2){   //thamasom+rajasam
		debug("not matched");
		$reason = "தமோ+ராஜஸ";
		$result=false;
	}
	elseif($girl_guna==3 && $boy_guna==1){   //thamasom+sathveegam
		debug("not matched");
		$reason = "தமோ+சாத்வீக";
		$result=false;
	}

/*	
	if($result == false){
		//echo "---Guna rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Guna match</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Guna rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Guna match</div>";
			echo "<div class='divPanchangGreen'>&#x2705;</div>";
		echo "</div>";
	}
	*/
	
	return $result;
}

// Boy & Girl bava matching
function matchingRule71()
{
}

//dasa lords matching based on friend/enemy
//dasa matching rule
function matchingRule10()
{
	global $current_dasa_bukthi, $current_dasa_bukthi_boy,$g_matching_mark;
	
	$result = false;
	$reason = "";
	
	debug('GIRL Dasa='.$current_dasa_bukthi['dasa']);
	debug('BOY Dasa='.$current_dasa_bukthi_boy['dasa']);
	
	if((strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //sun enemy to ven
		or (strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi['dasa'],"சூரி")==0) //sun enemy to ven
		or (strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) 		//sun enemy to sat
		or (strcmp($current_dasa_bukthi_boy['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi['dasa'],"சனி")==0)){   //sun enemy to sat
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"செ")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"புத")==0) //mars enemy to mer
		or (strcmp($current_dasa_bukthi['dasa'],"புத")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"செ")==0)){   
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"புத")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சந்")==0) //mer enemy to moon
			or (strcmp($current_dasa_bukthi['dasa'],"சந்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"புத")==0) //moon enemy to mer
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"குரு")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"புத")==0) 	//Jup enemy to mer
			or (strcmp($current_dasa_bukthi['dasa'],"புத")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"குரு")==0) //mer enemy to jup
			or (strcmp($current_dasa_bukthi['dasa'],"குரு")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //jup enemy to ven
			or (strcmp($current_dasa_bukthi['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"குரு")==0) //ven enemy to jup
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"சனி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சூரி")==0) 	//sat enemy to sun
			or (strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) //sun enemy to sat
			or (strcmp($current_dasa_bukthi['dasa'],"சனி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சந்")==0) //sat enemy to moo
			or (strcmp($current_dasa_bukthi['dasa'],"சந்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) //moo enemy to sat
			or (strcmp($current_dasa_bukthi['dasa'],"சனி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"செ")==0) //sat enemy to mar
			or (strcmp($current_dasa_bukthi['dasa'],"செ")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) //mar enemy to sat
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சூரி")==0) 	//ven enemy to sun
			or (strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //sun enemy to ven
			or (strcmp($current_dasa_bukthi['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சந்")==0) //ven enemy to moo
			or (strcmp($current_dasa_bukthi['dasa'],"சந்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //moo enemy to ven
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif(strcmp($current_dasa_bukthi['dasa'],$current_dasa_bukthi_boy['dasa'])==0){   //Same dasa
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa']." - ஒரே தசா";
	}
	else{
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'];
		$result = true;
	}
	
	if($result == false){
		//echo "---Dasa match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா பொருத்தம்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Dasa match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா பொருத்தம்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}


// Sun/Moon position
function matchingRule9()
{
	global $sw_planet, $tr_sw_planet,$g_matching_mark;
	
	$girl_sun = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சூரி"));
	$girl_moon = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சந்"));
	$boy_sun = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சூரி"));
	$boy_moon = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சந்"));
	
	if($girl_sun == 6 || $girl_sun == 8 || $girl_sun == 12 ){
		$result = false;
		$reason = "பெண்-சூரி-".$girl_sun;
	}
	elseif($girl_moon == 6 || $girl_moon == 8 || $girl_moon == 12 ){
		$result = false;
		$reason = "பெண்-சந்-".$girl_moon;
	}
	elseif($boy_sun == 6 || $boy_sun == 8 || $boy_sun == 12 ){
		$result = false;
		$reason = "ஆண்-சூரி-".$boy_sun;
	}
	elseif($boy_moon == 6 || $boy_moon == 8 || $boy_moon == 12 ){
		$result = false;
		$reason = "ஆண்-சந்-".$boy_moon;
	}
	else{
		$result = true;
		$reason = "பெண்-சூரி-".$girl_sun." பெண்-சந்-".$girl_moon."<BR>ஆண்-சூரி-".$boy_sun." ஆண்-சந்-".$boy_moon;
	}
	
	if($result == false){
		//echo "---Rasi rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>9. சூரி/சந் நிலை</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Rasi rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>9. சூரி/சந் நிலை</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

//Dasa match
function matchingRule10__()
{
	global $current_dasa_bukthi, $current_dasa_bukthi_boy,$sw_planet, $tr_sw_planet;
	
	$result = false;
	$reason = "";
	
	debug('GIRL Dasa='.$current_dasa_bukthi['dasa']);
	debug('BOY Dasa='.$current_dasa_bukthi_boy['dasa']);
	
	$girl_dasa_lord_house = findHouse($sw_planet,$current_dasa_bukthi['dasa']);
	$boy_dasa_lord_house = findHouse($tr_sw_planet,$current_dasa_bukthi_boy['dasa']);
	
	$dasa_lord_housecount = countHouses($girl_dasa_lord_house,$boy_dasa_lord_house);
	
	debug('Girl-dasa-lord-house='.$girl_dasa_lord_house);
	debug('<BR>Boy-dasa-lord-house='.$boy_dasa_lord_house);
	debug('<BR>Dasa lord house count ='.$dasa_lord_housecount);
	
	if($dasa_lord_housecount==6 or $dasa_lord_housecount==8){
		$result = false;
		$reason = "Dasa lord 6/8 (". $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] .")";
	}
	else{
		$result = true;
		$reason = "Dasa lord ". $dasa_lord_housecount . "/" . countHouses($boy_dasa_lord_house,$girl_dasa_lord_house);
	}
	
	if($result == false){
		//echo "---Dasa lord rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா/புக்தி</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Dasa lord rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா/புக்தி</div>";
			echo "<div class='divPanchangGreen'>&#x2705; ($reason)</div>";
		echo "</div>";
	}
	
}

//chevvai, surpa, kalathira, puthra thosam check
function matchingRule11()
{
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_matching_mark;
	
	$mars_dhosham_girl = false; //mars in 2,4,7,8,12
	$mars_dhosham_boy = false;
	$sarpa_dhosham_girl = false; // rahu/kethu in 1/7 or 2/8
	$sarpa_dhosham_boy = false;
	$kalathira_dhosham_girl = false; //The placement of malefic planets Mars, Saturn, Sun, Rahu, and Ketu in the 1st, 2nd, 4th, 7th, 8th, or 12th house from the Ascendant (Lagna) signifies Kalathra Dosha
	$kalathira_dhosham_boy = false;
	$puthra_dhosham_girl = false; // rahu/kethu/sun/sat in 5th house, 5th lord Neesam, 5th lord with rahu/kethu/sat, 5th lord in 6/8 house
	$puthra_dhosham_boy = false;
	
	$result = true;
	
	$girl_dhoshas = "பெண்-";
	$boy_dhoshas = "ஆண்-";
	
	$girl_mars_house = countHouses($lagna_house,findHouse($sw_planet,"செ")+1); //findHouse returns 0 as first house, hence adding 1
	$boy_mars_house = countHouses($transit_lagna_house,findHouse($tr_sw_planet,"செ")+1); //findHouse returns 0 as first house, hence adding 1
	
	$girl_sat_house = countHouses($lagna_house,findHouse($sw_planet,"சனி ")+1); //findHouse returns 0 as first house, hence adding 1
	$boy_sat_house = countHouses($transit_lagna_house,findHouse($tr_sw_planet,"சனி ")+1); //findHouse returns 0 as first house, hence adding 1
	
	$girl_rahu_house = countHouses($lagna_house,findHouse($sw_planet,"ரா")+1); //findHouse returns 0 as first house, hence adding 1
	$boy_rahu_house = countHouses($transit_lagna_house,findHouse($tr_sw_planet,"ரா")+1); //findHouse returns 0 as first house, hence adding 1

	$girl_kethu_house = targetHouse($girl_rahu_house,7); 
	$boy_kethu_house = targetHouse($boy_rahu_house,7);   
	
	//chevvai dhosham
	//mars in 2,4,7,8,12
	if($girl_mars_house==2 or $girl_mars_house==4 or $girl_mars_house==7 or $girl_mars_house==8 or $girl_mars_house==12)
	{
		$mars_dhosham_girl = true;
	}
	
	if($boy_mars_house==2 or $boy_mars_house==4 or $boy_mars_house==7 or $boy_mars_house==8 or $boy_mars_house==12)
	{
		$mars_dhosham_boy = true;
	}
	
	//rahu/kethu dhosham
	// rahu/kethu in 1/7 or 2/8
	if($girl_rahu_house==1 or $girl_rahu_house==2 or $girl_rahu_house==7 or $girl_rahu_house==8)
	{
		$sarpa_dhosham_girl = true;
	}
	
	if($boy_rahu_house==1 or $boy_rahu_house==2 or $boy_rahu_house==7 or $boy_rahu_house==8)
	{
		$sarpa_dhosham_boy = true;
	}
	
	//Kalathra dhosam
	//The placement of malefic planets Mars, Saturn, Sun, Rahu, and Ketu in the 1st, 2nd, 4th, 7th, 8th, or 12th house from the Ascendant (Lagna) signifies Kalathra Dosha
	if($girl_rahu_house==1 or $girl_rahu_house==2 or $girl_rahu_house==7 or $girl_rahu_house==8 or
	   $girl_sat_house==7 or $girl_mars_house==7)
	{
		$kalathira_dhosham_girl = true;
	}
	
	if($boy_rahu_house==1 or $boy_rahu_house==2 or $boy_rahu_house==7 or $boy_rahu_house==8 or
	   $boy_sat_house==7 or $boy_mars_house==7)
	{
		$kalathira_dhosham_boy = true;
	}
	
	// puthira dhosham
	// rahu/kethu/sun/sat in 5th house, 5th lord Neesam, 5th lord with rahu/kethu/sat, 5th lord in 6/8 house
	if($girl_rahu_house==5 or $girl_kethu_house==5 or $girl_sat_house==5)
	{
		$puthra_dhosham_girl = true;
	}
	
	if($boy_rahu_house==5 or $boy_kethu_house==5 or $boy_sat_house==5)
	{
		$puthra_dhosham_boy = true;
	}
	
	//add dhosha details for girl
	if($mars_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "செ";
	}
	
	if($sarpa_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "-" . "ராகு கேது";
	}
	
	if($kalathira_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "-" . "களத்திர";
	}
	
	if($puthra_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "-" . "புத்திர";
	}
	
	//add dhosha details for boy
	if($mars_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "செ";
	}
	
	if($sarpa_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "-" . "ராகு கேது";
	}
	
	if($kalathira_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "-" . "களத்திர";
	}
	
	if($puthra_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "-" . "புத்திர";
	}
	
	// if any one dhosha is there, mark it as red
	if($mars_dhosham_girl or $mars_dhosham_boy or $sarpa_dhosham_girl or $sarpa_dhosham_boy or 
	   $kalathira_dhosham_girl or $kalathira_dhosham_boy or $puthra_dhosham_girl or $puthra_dhosham_boy)
	{
		$result = false;
	}
	
	if(!($mars_dhosham_girl or $sarpa_dhosham_girl or $kalathira_dhosham_girl or $puthra_dhosham_girl))
	{
		$girl_dhoshas = "பெண்-இல்லை";
	}
	
	if(!($mars_dhosham_boy or $sarpa_dhosham_boy or $kalathira_dhosham_boy or $puthra_dhosham_boy))
	{
		$boy_dhoshas = "ஆண்-இல்லை";
	}
	
	if($result == false){
		//echo "---Dhosha rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. தோஷங்கள்</div>";
			echo "<div class='divPanchang'>&#x274C;<div class='divStarMatch'>$girl_dhoshas<BR>$boy_dhoshas</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Dhosha  rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. தோஷங்கள்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>(தோஷங்கள் இல்லை)</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	
}

//yoni matching rule
function yoniMatch()
{
	global $g_girl_nak, $g_boy_nak;
	
	$yoni_arr = ["அசுவினி"=>"குதிரை",
				"பரணி"=>"யானை",
				"கிருத்திகை"=>"ஆடு",
				"ரோகிணி"=>"நாகம்",
				"மிருகசிரீஷம்"=>"சாரை",
				"திருவாதிரை"=>"நாய்",
				"புனர்பூசம்"=>"பூனை",
				"பூசம்"=>"ஆடு",
				"ஆயில்யம்"=>"பூனை",
				"மகம்"=>"எலி",
				"பூரம்"=>"எலி",
				"உத்திரம்"=>"எருது",
				"ஹஸ்தம்"=>"எருமை",
				"சித்திரை"=>"புலி",
				"சுவாதி"=>"எருமை",
				"விசாகம்"=>"புலி",
				"அனுஷம்"=>"மான்",
				"கேட்டை"=>"மான்",
				"மூலம்"=>"நாய்",
				"பூராடம்"=>"குரங்கு",
				"உத்திராடம்"=>"பசு",
				"திருவோணம்"=>"குரங்கு",
				"அவிட்டம்"=>"சிங்கம்",
				"சதயம்"=>"குதிரை",
				"பூரட்டாதி"=>"சிங்கம்",
				"உத்திரட்டாதி"=>"பசு",
				"ரேவதி"=>"யானை"];
	
	/**** யோனிப் பொருத்தம் **********
	
		பாம்பு x கீரி
		யானை x சிங்கம்
		குரங்கு x ஆடு
		மான் x நாய்
		எலி x பூனை
		குதிரை x எருமை
		பசு x புலி
		நாகம் x எலி
	
	**** யோனிப் பொருத்தம் **********/
	
	$result = false;
	$reason = "";
	
	debug('GIRL Yoni='.$g_girl_nak);
	debug('BOY Yoni='.$g_boy_nak);
	
	$girl_yoni = $yoni_arr[$g_girl_nak];
	$boy_yoni = $yoni_arr[$g_boy_nak];
	
	if((strcmp($girl_yoni,"பாம்பு")==0 and strcmp($boy_yoni,"கீரி")==0)
		or (strcmp($boy_yoni,"பாம்பு")==0 and strcmp($girl_yoni,"கீரி")==0)){   //பாம்பு x கீரி
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"யானை")==0 and strcmp($boy_yoni,"சிங்கம்")==0)
		or (strcmp($boy_yoni,"யானை")==0 and strcmp($girl_yoni,"சிங்கம்")==0)){   //யானை x சிங்கம்
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"குரங்கு")==0 and strcmp($boy_yoni,"ஆடு")==0)
		or (strcmp($boy_yoni,"குரங்கு")==0 and strcmp($girl_yoni,"ஆடு")==0)){   //குரங்கு x ஆடு
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"மான்")==0 and strcmp($boy_yoni,"நாய்")==0)
		or (strcmp($boy_yoni,"மான்")==0 and strcmp($girl_yoni,"நாய்")==0)){   //மான் x நாய்
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"எலி")==0 and strcmp($boy_yoni,"பூனை")==0)
		or (strcmp($boy_yoni,"எலி")==0 and strcmp($girl_yoni,"பூனை")==0)){   //எலி x பூனை
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"குதிரை")==0 and strcmp($boy_yoni,"எருமை")==0)
		or (strcmp($boy_yoni,"குதிரை")==0 and strcmp($girl_yoni,"எருமை")==0)){   //குதிரை x எருமை
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"பசு")==0 and strcmp($boy_yoni,"புலி")==0)
		or (strcmp($boy_yoni,"பசு")==0 and strcmp($girl_yoni,"புலி")==0)){   //பசு x புலி
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"நாகம்")==0 and strcmp($boy_yoni,"எலி")==0)
		or (strcmp($boy_yoni,"நாகம்")==0 and strcmp($girl_yoni,"எலி")==0)){   //நாகம் x எலி
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	else{
		$reason = $girl_yoni . " + " . $boy_yoni;
		$result = true;
	}
	
	/*
	if($result == false){
		//echo "---Yoni rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Yoni match</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Yoni rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Yoni match</div>";
			echo "<div class='divPanchangGreen'>&#x2705; ($reason)</div>";
		echo "</div>";
	}
	*/
	
	return $result;
}

//rajju matching rule
function matchingRule8($display)
{
	global $g_girl_nak, $g_boy_nak,$g_matching_mark;
	
	$sirasu = ["மிருகசிரீஷம்", "சித்திரை", "அவிட்டம்"];
	$kanta = ["ரோகிணி", "திருவாதிரை", "ஹஸ்தம்", "சுவாதி", "திருவோணம்", "சதயம்"];
	$uthara = ["கிருத்திகை", "புனர்பூசம்", "உத்திரம்", "விசாகம்", "உத்திராடம்", "பூரட்டாதி"];
	$ooru = ["பரணி", "பூசம்", "பூரம்", "அனுஷம்", "பூராடம்", "உத்திரட்டாதி"];
	$paatha = ["அசுவினி", "ஆயில்யம்", "மகம்", "கேட்டை", "மூலம்", "ரேவதி"];
	
	$rajju_type = ["dummy","சிரசு","கண்ட","உதர", "ஊரு", "பாத"];
	
	/**** rajju பொருத்தம் **********
	
		same rajju = bad
	
	**** rajju பொருத்தம் **********/
	
	$result = false;
	$reason = "";
	$details ="";
	
	if(array_search($g_girl_nak,$sirasu)!==false){
		$girl_rajju = 1; //sirasu
	}
	elseif(array_search($g_girl_nak,$kanta)!==false){
		$girl_rajju = 2; //kanta
	}
	elseif(array_search($g_girl_nak,$uthara)!==false){
		$girl_rajju = 3; //uthara
	}
	elseif(array_search($g_girl_nak,$ooru)!==false){
		$girl_rajju = 4; //ooru
	}
	elseif(array_search($g_girl_nak,$paatha)!==false){
		$girl_rajju = 5; //paatha
	}
	
	if(array_search($g_boy_nak,$sirasu)!==false){
		$boy_rajju = 1; //sirasu
	}
	elseif(array_search($g_boy_nak,$kanta)!==false){
		$boy_rajju = 2; //kanta
	}
	elseif(array_search($g_boy_nak,$uthara)!==false){
		$boy_rajju = 3; //uthara
	}
	elseif(array_search($g_boy_nak,$ooru)!==false){
		$boy_rajju = 4; //ooru
	}
	elseif(array_search($g_boy_nak,$paatha)!==false){
		$boy_rajju = 5; //paatha
	}
	
	debug('GIRL rajju='.$girl_rajju);
	debug('BOY rajju='.$boy_rajju);
	
	if($girl_rajju == $boy_rajju){ 
		$result = false;
		$reason = "ஒரே ரஜ்ஜு";
	
		$details = "<BR>பெண் - $rajju_type[$girl_rajju] <BR> ஆண் - $rajju_type[$boy_rajju]";
	}
	else{
		$reason = "வேறு ரஜ்ஜு";
		$result = true;
		
		$details = "<BR>பெண் - $rajju_type[$girl_rajju] <BR> ஆண் - $rajju_type[$boy_rajju]";
	}
	
	if(!$display){   // Just the result returned to the called plage without displaying
		return $result;
	}
	
	if($result == false){
		//echo "---Yoni rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>8. ரஜ்ஜு பொருத்தம் (வேதை நட்)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason $details</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Yoni rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>8. ரஜ்ஜு பொருத்தம் (வேதை நட்)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason  $details</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	
	return $result;
}

//thithi soonyam check
function matchingRule111($in_thithi_girl, $in_thithi_boy)
{
	global $tr_sw_planet, $sw_planet;
	
	$thithi_girl = explode('[',explode("/",$in_thithi_girl)[1])[0];
	$thithi_boy = explode('[',explode("/",$in_thithi_boy)[1])[0];
	
	$result=false;
	$reason="";
	
	$thithi_arr = array(
					"பிரதமை"	=>	 "துலாம், மகரம்",
					"துவிதியை"	=>	 "தனுசு, மீனம்",
					"திருதியை"	=>	 "சிம்மம், மகரம்",
					"சதுர்த்தி"	=>	 "ரிஷபம், கும்பம்",
					"பஞ்சமி"	=>	 "மிதுனம், கன்னி",
					"சஷ்டி"	=>	 "மேஷம், சிம்மம்",
					"சப்தமி"	=>	 "கடகம், தனுசு",
					"அஷ்டமி"	=>	 "மிதுனம், கன்னி",
					"நவமி"	=>	 "சிம்மம், விருச்சிகம்",
					"தசமி"	=>	 "சிம்மம், விருச்சிகம்",
					"ஏகாதசி"	=>	 "தனுசு, மீனம்",
					"துவாதசி"	=>	 "துலாம், மகரம்",
					"திரயோதசி"	=>	 "ரிஷபம், சிம்மம்",
					"சதுர்த்தசி"	=>	 "மிதுனம், கன்னி, தனுசு, மீனம்",
					"அமாவாசை"	=>	"இல்லை",
					"பௌர்ணமி" 	=>	"இல்லை"
					);
					
	$girl_thithi_soonyam = explode(',',$thithi_arr[$thithi_girl]);
	$boy_thithi_soonyam = explode(',',$thithi_arr[$thithi_boy]);
	
	$arr_size = sizeof($girl_thithi_soonyam);
	
	if(sizeof(array_intersect($girl_thithi_soonyam,$boy_thithi_soonyam))>0){ // if any one or more rasi being soonyam across girl and boy soonya rasi
		$result = false;
		$reason = "ஒரே திதி சூன்ய ராசி (". join('-',$boy_thithi_soonyam) . ' / ' . join('-',$girl_thithi_soonyam) . ')';
	}
	else{
		$result=true;
		$reason = "ஒரே திதி சூன்ய ராசி இல்லை";
	}
	
	if($result == false){
		//echo "---Yoni rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. திதி சூனியம்</div>";
			echo "<div class='divPanchangRed'>&#x274C; <BR>($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Yoni rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. திதி சூனியம்</div>";
			echo "<div class='divPanchangGreen'>PASS <BR>($reason)</div>";
		echo "</div>";
	}
}

function calculateGirlPanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_girl_nak, $g_girl_karan, $g_panchang_girl,$g_girl_nak_pad_idx, $full_sun_deg ;
	
	$tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
	
	$karan_sukla = array(array("Kimstughna/கிம்ஸ்துக்கினம்","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"));
	$karan_krishna = array(array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Sakuni/சகுனி"),
						array("Chatuspada/சதுஷ்பாத","Naga/நாகவ"));
	
	$weekday_arr = array("Sunday/ஞாயிற்றுக்கிழமை","Monday/திங்கட்கிழமை","Tuesday/செவ்வாய்க்கிழமை","Wednesday/புதன்கிழமை",
					"Thursday/வியாழக்கிழமை","Friday/வெள்ளிக்கிழமை","Saturday/சனிக்கிழமை");
	
	$panchang = array();
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Poorva bhadrapada 1/பூரட்டாதி 1","Poorva bhadrapada 2/பூரட்டாதி 2","Poorva bhadrapada 3/பூரட்டாதி 3","Poorva bhadrapada 4/பூரட்டாதி 4",
						"Uthra bhadrapada 1/உத்திரட்டாதி 1","Uthra bhadrapada 2/உத்திரட்டாதி 2","Uthra bhadrapada 3/உத்திரட்டாதி 3","Uthra bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333);
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$g_girl_nak_pad_idx = $nak_idx;
	$g_girl_nak = explode(' ',explode('/',$nak_array[$nak_idx])[1])[0];
	
	//thithi calculation
	$full_sun_deg = ($sun_house-1)*30+$sun_degree;
	
	if($full_moon_deg - $full_sun_deg < 180){
		if($full_moon_deg - $full_sun_deg < 0){
		
			$idx = ($full_moon_deg - $full_sun_deg+360) / 12;
			
			$thi_idx = floor($idx);
			
			if($thi_idx >=0 and $thi_idx <=14)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			elseif($thi_idx >14 and $thi_idx <=29)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			
			/*
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			elseif($idx > 14){
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			elseif($idx <= 14){
				
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			*/
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor($idx);
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			
		}
	}
	else{
		$idx = ($full_moon_deg - $full_sun_deg) / 12;
		
		if($idx < 1){
			$idx = 14;
			
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
		}
		else{
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			
		}
	}
	
	debug("--THI IDX--".floor($idx)."----");

	
/*	if($moon_house > $sun_house){
		$diff = ($full_moon_deg - $full_sun_deg)/12;
	}
	elseif($moon_house < $sun_house){
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	else{
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	
	debug("<BR>diff=".$diff);
	
	if(floor($diff) > 0){	
		$thi_idx = floor($diff);
		$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
	elseif(floor($diff) < 0){	
		$thi_idx = 30 - floor($diff) ;
		$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}else{
		if($moon_house==$sun_house){
			$thi_idx = 29; 
		}
		else{
			$thi_idx = 14; 
		}
		$panchang["thithi"] = $tithi[$thi_idx];
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
*/	
	
	//karanam calculation
	$kar = $idx-floor($idx);
	//$kar = $thi_idx-floor($thi_idx);
	
	if($thi_idx < 15){
		
		if($kar < 0.5){
			$panchang["karan"] = $karan_sukla[$thi_idx][0];
		}
		else{
			$panchang["karan"] = $karan_sukla[$thi_idx][1];
		}
		
		debug("THI-IDX=".$thi_idx . "KAR=". $kar . "&&&&&&");
	}
	else{
		if($kar < 0.5){
			$panchang["karan"] = $karan_krishna[$thi_idx-15][0];
		}
		else{
			$panchang["karan"] = $karan_krishna[$thi_idx-15][1];
		}
		
		debug("THI-IDX-15=".($thi_idx-15) . "KAR=". $kar . "&&&&&&");
	}
	
	//Store karanam for future use
	$g_girl_karan = $panchang["karan"];
	
	/*if($full_moon_deg % 180 > $full_sun_deg){
		$kar = ($full_moon_deg - $full_sun_deg)/12;
		debug("krishna".(abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 . "|" . $thi_idx) ;
		
		if((abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][0];debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_krishna[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][1];debug("%%%%");
			}
			else{
				echo $thi_idx; 
				$panchang["karan"] = $karan_krishna[$thi_idx][1]; debug("$$$$$");
			}
		}
	}
	else{
		$kar = ($full_sun_deg - $full_moon_deg)/12;
		debug("shukla".((abs(($full_sun_deg - $full_moon_deg) - 180))) . "|")  ; 
		
		if((abs(($full_sun_deg - $full_moon_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][0]; debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][1]; debug("%%%%");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][1]; debug("$$$$$");
			}
		}
	}*/
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	debug("yoga_idx=$yoga_idx, yogam_deg".($deg / 13.333333));
	
	if($yoga_idx > 27){
		$yoga_idx = 27;
	}
	
	$panchang["yoga"] = $yoga_arr[$yoga_idx];
	
	/*
	//display panchang table
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Vaaram/வாரம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['vaaram']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Nakshatra/நட்சத்திரம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['nak']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Thithi/திதி</div>";
			echo "<div class='divGirlPanchang'>".$panchang['thithi']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Karanam/கரணம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['karan']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Yogam/யோகம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
	echo "</div></center>";
	*/
	
	$g_panchang_girl = $panchang;
}

function calculateBoyPanchang(){
	global $transit_moon_degree, $transit_moon_house, $transit_sun_house, $transit_sun_degree, $transit_dayofweek, $g_boy_nak, $g_boy_karan, $g_panchang_boy, $g_boy_nak_pad_idx, $full_sun_deg_boy;
	
	$tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
	
	$karan_sukla = array(array("Kimstughna/கிம்ஸ்துக்கினம்","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"));
	$karan_krishna = array(array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Sakuni/சகுனி"),
						array("Chatuspada/சதுஷ்பாத","Naga/நாகவ"));
	
	$weekday_arr = array("Sunday/ஞாயிற்றுக்கிழமை","Monday/திங்கட்கிழமை","Tuesday/செவ்வாய்க்கிழமை","Wednesday/புதன்கிழமை",
					"Thursday/வியாழக்கிழமை","Friday/வெள்ளிக்கிழமை","Saturday/சனிக்கிழமை");
	
	$panchang = array();
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Poorva bhadrapada 1/பூரட்டாதி 1","Poorva bhadrapada 2/பூரட்டாதி 2","Poorva bhadrapada 3/பூரட்டாதி 3","Poorva bhadrapada 4/பூரட்டாதி 4",
						"Uthra bhadrapada 1/உத்திரட்டாதி 1","Uthra bhadrapada 2/உத்திரட்டாதி 2","Uthra bhadrapada 3/உத்திரட்டாதி 3","Uthra bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$transit_dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($transit_moon_house-1) * 30 + $transit_moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333);
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$g_boy_nak_pad_idx = $nak_idx;
	$g_boy_nak = explode(' ',explode('/',$nak_array[$nak_idx])[1])[0];
	
	//thithi calculation
	$full_sun_deg_boy = ($transit_sun_house-1)*30+$transit_sun_degree;
	
	$full_sun_deg = $full_sun_deg_boy; //just to avoid renaming in the below codes. Should be fixed in future.
	
	if($full_moon_deg - $full_sun_deg < 180){
		if($full_moon_deg - $full_sun_deg < 0){
		
			$idx = ($full_moon_deg - $full_sun_deg+360) / 12;
			
			$thi_idx = floor($idx);
			
			if($thi_idx >=0 and $thi_idx <=14)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			elseif($thi_idx >14 and $thi_idx <=29)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			
			/*
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			elseif($idx > 14){
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			elseif($idx <= 14){
				
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			*/
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor($idx);
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			
		}
	}
	else{
		$idx = ($full_moon_deg - $full_sun_deg) / 12;
		
		if($idx < 1){
			$idx = 14;
			
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
		}
		else{
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			
		}
	}
	
	debug("--THI IDX--".floor($idx)."----");

	
/*	if($moon_house > $sun_house){
		$diff = ($full_moon_deg - $full_sun_deg)/12;
	}
	elseif($moon_house < $sun_house){
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	else{
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	
	debug("<BR>diff=".$diff);
	
	if(floor($diff) > 0){	
		$thi_idx = floor($diff);
		$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
	elseif(floor($diff) < 0){	
		$thi_idx = 30 - floor($diff) ;
		$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}else{
		if($moon_house==$sun_house){
			$thi_idx = 29; 
		}
		else{
			$thi_idx = 14; 
		}
		$panchang["thithi"] = $tithi[$thi_idx];
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
*/	
	
	//karanam calculation
	$kar = $idx-floor($idx);
	//$kar = $thi_idx-floor($thi_idx);
	
	if($thi_idx < 15){
		
		if($kar < 0.5){
			$panchang["karan"] = $karan_sukla[$thi_idx][0];
		}
		else{
			$panchang["karan"] = $karan_sukla[$thi_idx][1];
		}
		
		debug("THI-IDX=".$thi_idx . "KAR=". $kar . "&&&&&&");
	}
	else{
		if($kar < 0.5){
			$panchang["karan"] = $karan_krishna[$thi_idx-15][0];
		}
		else{
			$panchang["karan"] = $karan_krishna[$thi_idx-15][1];
		}
		
		debug("THI-IDX-15=".($thi_idx-15) . "KAR=". $kar . "&&&&&&");
	}
	
	//Store karanam for future use
	$g_boy_karan = $panchang["karan"];
	
	/*if($full_moon_deg % 180 > $full_sun_deg){
		$kar = ($full_moon_deg - $full_sun_deg)/12;
		debug("krishna".(abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 . "|" . $thi_idx) ;
		
		if((abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][0];debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_krishna[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][1];debug("%%%%");
			}
			else{
				echo $thi_idx; 
				$panchang["karan"] = $karan_krishna[$thi_idx][1]; debug("$$$$$");
			}
		}
	}
	else{
		$kar = ($full_sun_deg - $full_moon_deg)/12;
		debug("shukla".((abs(($full_sun_deg - $full_moon_deg) - 180))) . "|")  ; 
		
		if((abs(($full_sun_deg - $full_moon_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][0]; debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][1]; debug("%%%%");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][1]; debug("$$$$$");
			}
		}
	}*/
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	$panchang["yoga"] = $yoga_arr[$yoga_idx];
	
	/*
	//display panchang table
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Vaaram/வாரம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['vaaram']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Nakshatra/நட்சத்திரம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['nak']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Thithi/திதி</div>";
			echo "<div class='divBoyPanchang'>".$panchang['thithi']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Karanam/கரணம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['karan']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Yogam/யோகம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
	echo "</div></center>";
	*/
	
	$g_panchang_boy = $panchang;
	$full_sun_deg_boy = $full_sun_deg; //reassign the value to global variable
}


?>
