<?php

/*
 * Copyright (C) 2012 Dinesh Copoosamy <dinesh@dinesh.co.za>
 * Ported from C https://github.com/santhoshn/panchanga
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('D2R',pi()/180.0);
define('R2D',180.0/pi());

class Panchang {

  public $Ls;
  public $Lm;
  public $Ms;
  public $Mm;
 
  public $month = array("January","February","March","April","May","June","July","August","September","October","November","December");
  //public $rashi = array("Mesha","Vrishabha","Mithuna","Karka","Simha","Kanya","Tula","Vrischika","Dhanu","Makara","Kumbha","Meena");
  public $rashi = array("Mesha/மேஷம்","Vrishabha/ரிஷபம்","Mithuna/மிதுனம்","Karka/கடகம்","Simha/சிம்மம்","Kanya/கன்னி","Tula/துலாம்","Vrischika/விருச்சிகம்","Dhanu/தனுசு","Makara/மகரம்","Kumbha/கும்பம்","Meena/மீனம்");
  public $day = array("Sunday/ஞாயிறு","Monday/திங்கள்","Tuesday/செவ்வாய்","Wednesday/புதன்","Thursday/வியாழன்","Friday/வெள்ளி","Saturday/சனி");
  public $tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
  //public $karan = array("Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Sakuni/சகுனி","Chatuspada/சதுஷ்பாத","Naga/நாகவ","Kimstughna/கிம்ஸ்துக்கினம்");
  public $karan = array("Kimstughna/கிம்ஸ்துக்கினம்","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Sakuni/சகுனி","Chatuspada/சதுஷ்பாத","Naga/நாகவ","Kimstughna/கிம்ஸ்துக்கினம்");
  public $yoga = array("Vishkambha/விஷ்கம்பம்","Prithi/பிரீதி","Ayushman/ஆயுஷ்மான்","Saubhagya/சௌபாக்கியம்","Shobhana/சோபனம்","Atiganda/அதிகண்டம்","Sukarman/சுகர்மம்","Dhrithi/திருதி","Shoola/சூலம்","Ganda/கண்டம்","Vridhi/விருத்தி","Dhruva/துருவம்","Vyaghata/வியாகதம்","Harshana/அரிசணம்","Vajra/வச்சிரம்","Siddhi/சித்தி","Vyatipata/வியாதிபாதம்","Variyan/வரியான்","Parigha/பரிகம்","Shiva/சிவம்","Siddha/சித்தம்","Sadhya/சாத்தியம்","Shubha/சுபம்","Supiram/சுப்பிரம்","Bramha/பிராமியம்","Indra/ஐந்திரம்","Vaidhruthi/வைதிருதி");
  //public $nakshatra = array("Ashwini","Bharani","Krittika","Rohini","Mrigashira","Ardhra","Punarvasu","Pushya","Ashlesa","Magha","Poorva Phalguni","Uttara Phalguni","Hasta","Chitra","Swathi","Vishaka","Anuradha","Jyeshta","Mula","Poorva Ashada","Uttara Ashada","Sravana","Dhanishta","Shatabisha","Poorva Bhadra","Uttara Bhadra","Revathi");
  public $nakshatra = array("Aswini/அசுவினி","Bharani/பரணி","Krithika/கிருத்திகை","Rohini/ரோகிணி","Mrigashirsha/மிருகசிரீஷம்","Arudra/திருவாதிரை","Punarvasu/புனர்பூசம்","Pushyami/பூசம்","Ashlesha/ஆயில்யம்","Magha/மகம்","Poorva Phalguni/பூரம்","Uthra phalguni/உத்திரம்","Hastha/ஹஸ்தம்","Chitra/சித்திரை","Swaathi/சுவாதி","Vishaakha/விசாகம்","Anuraadha/அனுஷம்","Jyeshta/கேட்டை","Moola/மூலம்","Poorva Ashaada/பூராடம்","Uthra Ashaada/உத்திராடம்","Shraavan/திருவோணம்","Dhanishta/அவிட்டம்","Shathabhisha/சதயம்","Poorva bhadrapada/பூரட்டாதி","Uthra bhadrapada/உத்திரட்டாதி","Revathi/ரேவதி");

  public function REV($x) {
    return (($x)-floor(($x)/360.0)*360.0);
  }

  public function ayanamsa($d) {
    $t = ($d+36523.5)/36525;
    $o = 259.183275-1934.142008333206*$t+0.0020777778*$t*$t;
    $l = 279.696678+36000.76892*$t+0.0003025*$t*$t;
    $ayan = 17.23*sin(($o)*D2R)+1.27*sin(($l*2)*D2R)-(5025.64+1.11*$t)*$t;
    $ayan = ($ayan-80861.27)/3600.0; //Lahiri
	//$ayan= -1* (16.90709 * 2022/1000 - 0.757371 * 2022/1000 * 2022/1000 - 6.92416100010001000 + ((7-1 + 22/30) * 1.1574074/1000)); echo $ayan;
	//$ayan = -23.9928962; //Lahiri new
	
    return $ayan;    
  }

  public function sunLongitude($d) {
    $w = 282.9404+4.70935e-5*$d;
    $a = 1.000000;
    $e = 0.016709-1.151e-9*$d;
    $M = $this->REV(356.0470+0.9856002585*$d);
    $this->Ms = $M;
    $this->Ls = $w+$M;

    $tmp = $M*D2R;
    $E = $M+R2D*$e*sin($tmp)*(1+$e*cos($tmp));

    $tmp = $E*D2R;
    $x = cos($tmp)-$e;
    $y = sin($tmp)*sqrt(1-$e*$e);

    $r = sqrt($x*$x + $y*$y);
    $v = $this->REV(R2D*atan2($y,$x));

    return $this->REV($v+$w);    
  }

  public function moonLongitude($d) {

    $N = 125.1228-0.0529538083*$d;
    $i = 5.1454;
    $w = $this->REV(318.0634+0.1643573223*$d);
    $a = 60.2666;
    $e = 0.054900;
    $M = $this->REV(115.3654+13.0649929509*$d);
    $this->Mm = $M;
    $this->Lm = $N+$w+$M;

    //Calculate Eccentricity anamoly
    $tmp = $M*D2R;
    $E = $M+R2D*$e*sin($tmp)*(1+$e*cos($tmp));

    $tmp = $E*D2R;
    $Et = $E-($E-R2D*$e*sin($tmp)-$M)/(1-$e*cos($tmp));

    do {
      $E = $Et;
      $tmp = $E*D2R;
      $Et = $E-($E-R2D*$e*sin($tmp)-$M)/(1-$e*cos($tmp));
    } while($E-$Et>0.005);

    $tmp = $E*D2R;
    $x = $a*(cos($tmp)-$e);
    $y = $a*sqrt(1-$e*$e)*sin($tmp);

    $r = sqrt($x*$x + $y*$y);
    $v = $this->REV(R2D*atan2($y,$x));

    $tmp = D2R*$N;
    $tmp1 = D2R*($v+$w);
    $tmp2 = D2R*$i;
    $xec = $r*(cos($tmp)*cos($tmp1)-sin($tmp)*sin($tmp1)*cos($tmp2));
    $yec = $r*(sin($tmp)*cos($tmp1)+cos($tmp)*sin($tmp1)*cos($tmp2));
    $zec = $r*sin($tmp1)*sin($tmp2);

    //Do some corrections
    $D = $this->Lm - $this->Ls;
    $F = $this->Lm - $N;

    $lon = R2D*atan2($yec,$xec);

    $lon += -1.274*sin(($this->Mm-2*$D)*D2R);
    $lon+= +0.658*sin((2*$D)*D2R);
    $lon+= -0.186*sin(($this->Ms)*D2R);
    $lon+= -0.059*sin((2*$this->Mm-2*$D)*D2R);
    $lon+= -0.057*sin(($this->Mm-2*$D+$this->Ms)*D2R);
    $lon+= +0.053*sin(($this->Mm+2*$D)*D2R);
    $lon+= +0.046*sin((2*$D-$this->Ms)*D2R);
    $lon+= +0.041*sin(($this->Mm-$this->Ms)*D2R);
    $lon+= -0.035*sin(($D)*D2R);
    $lon+= -0.031*sin(($this->Mm+$this->Ms)*D2R);
    $lon+= -0.015*sin((2*$F-2*$D)*D2R);
    $lon+= +0.011*sin(($this->Mm-4*$D)*D2R);

    return $this->REV($lon);
  }

  //Calculate Panchanga
  public function calcPanchanga($dd, $mm, $yy, $hr, $zhr)
  {
    $pdata = array();

    //Calculate day number since 2000 Jan 0.0 TDT
    $d = (367*$yy-7*($yy+($mm+9)/12)/4+275*$mm/9+$dd-730530);
	//$d = (367*$yy-7*($yy+($mm+9)/12)/4+275*$mm/9+$dd-730530+0.45);

    //Calculate Ayanamsa, moon and sun longitude
    $ayanamsa = $this->ayanamsa($d);
    $slon = $this->sunLongitude($d+(($hr-$zhr)/24.0));
    $mlon = $this->moonLongitude($d+(($hr-$zhr)/24.0));

    //Calculate Tithi and Paksha
    /*$tmlon = $mlon+(($mlon<$slon)?360:0);
    $tslon = $slon;
	$n = (int)floor(($tmlon-$tslon)/12);
    $pdata['tithi'] = $this->tithi[$n];
    if($n<=14){
      $pdata['Paksha'] = "Shukla/ஷுக்ல"; 
    }
    else {
      $pdata['Paksha'] = "Krishna/கிருஷ்ண";
    } */
	
	//Praveen Calculate Tithi and Paksha
    $tmlon = ($mlon-$slon)<0?($mlon-$slon)+360:($mlon-$slon);
   // $tslon = $slon;
	$n = intval($tmlon/12); 

    $pdata['tithi'] = $this->tithi[$n];
    if($n<=14){
      $pdata['Paksha'] = "Shukla/ஷுக்ல"; 
    }
    else {
      $pdata['Paksha'] = "Krishna/கிருஷ்ண";
    }
	
	
    //Calculate Nakshatra
    $tmlon = $this->REV($mlon+$ayanamsa);
    $pdata['nakshatra'] = $this->nakshatra[(int)($tmlon*6/80)];

    //Calculate Yoga
    $tmlon = $mlon+$ayanamsa;
    $tslon = $slon+$ayanamsa;
    $pdata['yoga'] = $this->yoga[(int)($this->REV($tmlon+$tslon)*6/80)];

    //Calculate Karana
    /*$tmlon = $mlon+(($mlon<$slon)?360:0);
    $tslon = $slon;
    $n = (int)(($tmlon-$tslon)/6);
	echo "tmlon=".$tmlon.",tslon=".$tslon.",n=".$n;
    if($n==0) $n=10;
    if($n>=57) $n-=50;
    if($n>0 && $n<57) $n=($n-1)-(($n-1)/7*7);
	echo "tmlon=".$tmlon.",tslon=".$tslon.",n=".$n;
    $pdata['karana'] = $this->karan[$n]; */
	
	$tmlon = $mlon+(($mlon<$slon)?360:0);
    $tslon = $slon;
    //$n = (int)(($tmlon-$tslon) % 360);
	$n = (int)($tmlon-$tslon);

	if($n<0) {$n = $n+360;}
	$karana_idx = ceil($n/6);
	$pdata['karana'] = $this->karan[$karana_idx];
	
    //Calculate the rashi in which the moon is present
    $tmlon = $this->REV($mlon+$ayanamsa);
    $pdata['rashi'] = $this->rashi[(int)($tmlon/30)];
    return $pdata;
  }    
}
?>
