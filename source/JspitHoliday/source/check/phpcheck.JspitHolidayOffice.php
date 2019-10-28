<?php
//error_reporting(-1);
error_reporting(E_ALL ^ (E_WARNING | E_USER_WARNING));
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

require __DIR__ . '/../class/phpcheck.php';

require __DIR__ . '/../class/JspitHoliday.php';
require __DIR__ . '/../class/icsEventReader.php';

$urlIcs = "https://www.officeholidays.com/ics/ics_country_code.php";

//prepare testdata
$lang = "en";
$publicHoliday = true; //not non public holidays

$countryRegion = array(

  //true check with regional holidays
  //Euro countries
  "DE" => true, //Germany
  "AT" => true, //Austria 
  "NL" => true, //Netherlands
  "DK" => true, //Denmark
  "FR" => true, //France
  "IT" => true, //Italy
  "ES" => false,//Spain
  "LU" => true, //Luxembourg
  "BE" => true, //Belgium
  "GR" => true, //Greece
  "SK" => true, //Slovakia
  "IE" => true, //Ireland
  "CY" => true, //Cyprus
  "PT" => true, //Portugal
  "EE" => true, //Estonia
  "FI" => true, //Finland
  "LV" => true, //Latvia
  "LT" => true, //Lithuania
  "MT" => true, //Malta
  
  //Other
  "CZ" => true, //Czech Republic
  "PL" => true, //Poland
  "CH" => true, //Switzerland
  "SE" => true, //Sweden
  "BG" => true, //Bulgaria
  "HR" => true, //Croatia
  "RO" => true, //Romania
  "SI" => true, //Slovenia
  "HU" => true, //Hungary

  "GB" => false,//Great Britain
  "US" => false,//United States 
  "JP" => true, //Japan
  "RU" => true, //Russia
 
);

//all years for check
$years = array(date("Y")-1,date("Y"),date("Y")+1);
//$years = array(2019);

$t = new PHPcheck;  //test-class

foreach($countryRegion as $country => $regionalHoliday) {
  $icsReader = new icsEventReader($urlIcs, $country);
  $holiday = new JspitHoliday($country."*"); 
  
  foreach($years as $year) {
    $icsReader->reset();  //for getNextEvent
    while($icsEvent = $icsReader->getNextEvent($year, $publicHoliday, $regionalHoliday)) {
      if($icsEvent->location !== false) {
        $t->start($icsEvent->date." ".$icsEvent->location." ".$icsEvent->description);
        $result = $holiday->holidayName($icsEvent->date,$lang);
        $t->check($result, $result !== false AND $result != "?");
      }
      else {
        $t->start('Unknown country '.$country);
        $t->check($country, false);
      }
    }
  }
}

//Output
echo $t->getHtml();
