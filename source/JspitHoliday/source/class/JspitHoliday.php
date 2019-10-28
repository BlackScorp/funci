<?php
/**
.---------------------------------------------------------------------------.
|  Software: JspitHoliday - PHP class                                       |
|   Version: 1.31                                                           |
|      Date: 2019-10-19                                                     |
| ------------------------------------------------------------------------- |
| Copyright © 2018,2019 Peter Junk alias jspit All Rights Reserved.         |
'---------------------------------------------------------------------------'
*/

class JspitHoliday
{
  const TYPE_OFFICIAL = 1;
  const TYPE_BANK = 2;
  const TYPE_OBSERVED = 4;
  const TYPE_OTHER = 8;
  const TYPE_4 = 16;
  const TYPE_5 = 32;
  const TYPE_6 = 64;
  const TYPE_ALL = 0x7FFF;
  
  protected $pdo;
  protected $language = "en-GB";
  protected $region;
  protected $config;
  protected $typFilter;

  /**
   * Constructs the class instance
   * @param string $filterRegion Country/Region ISO 3361 Alpha2 ('DE','DE-BY'..) 
   * @param string $db filename for SQLite or PDO Object, default: holiday.sqlite
   * @param int $typFilte Filter for Holiday-Type for SQLite, default: holiday::TYPE_ALL
   * @throws InvalidArgumentException
   */
  public function __construct($filterRegion = "", $db = null, $typFilter = self::TYPE_ALL) {
    //verify filter
    $filter = strtoupper($filterRegion);
    if(!preg_match('/^[A-Z]{2,3}(-[A-Z0-9]{1,8}){0,3}\-?\*?$/', $filter)) {
      throw new InvalidArgumentException("filterRegion is not like ISO3361");  
    }
     
    if($db instanceof PDO) {
      try{  
        $this->pdo = $db;
        $this->createConfig($filterRegion, $typFilter);
      } catch(Exception $e) {
        throw new InvalidArgumentException("Faulty PDO-Connection");  
      }
    }
    else {    
      if(! is_string($db) OR $db == "") {
        $db = __DIR__ . "/".basename(__CLASS__).".sqlite";
      }
      if(!file_exists($db)){
        throw new InvalidArgumentException("SQLite File '$db' not found");
      }
      try{
        $options = array(
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        );

        $this->pdo = new PDO('sqlite:'.$db,null,null,$options);
        
        $this->createConfig($filterRegion, $typFilter);
      } catch(Exception $e) {
        throw new InvalidArgumentException("Faulty SQLite-DB '$sqliteFile'");  
      }
    }
  }
  
 /**
  * return a new class instance
  * @param string $filterRegion Country/Region ISO 3361 Alpha2 ('DE','DE-BY'..) 
  * @param string $sqliteFile filename for SQLite, default: holiday.sqlite
  * @param int $typFilte Filter for Holiday-Type for SQLite, default: holiday::TYPE_ALL
  * @return object JspitHoliday
  * @throws InvalidArgumentException
  */
  public static function create($filterRegion = "", $sqliteFile = null, $typFilter = self::TYPE_ALL) {
    return new static($filterRegion, $sqliteFile, $typFilter );    
  }
  
  
 /**
  * set Default Language
  * @param string $language p.E. "de-DE", "en-GB" 
  * @return $this
  */
  public function setLanguage($language = "en-GB") {
    $this->language = $language;
    return $this;
  }

 /**
  * get Default Language
  * @return string default language
  */
  public function getLanguage() {
    return $this->language;
  }

 /**
  * set region
  * @param string $filterRegion Country/Region ISO 3361 Alpha2 ('DE','DE-BY'..) 
  * @return $this
  */
  public function setRegion($filterRegion) {
    try{
      $this->createConfig($filterRegion, $this->typFilter);
      return $this;
    } catch(Exception $e) {
      throw $e;
    }
  }
  
 /**
  * Returns the current Region
  * @return string
  */
  public function getRegion() {
    return $this->region;
  }
  
 /**
  * set Filter Holiday Type
  * @param int typ Filter
  * @throws Exception
  */
  public function setTypFilter($typFilter = self::TYPE_ALL) {
    try{
      $this->createConfig($this->region, (int)$typFilter);
      return $this;
    } catch(Exception $e) {
      throw $e;
    }
  }

 /**
  * get Name from a Holiday p.e: "New Year's Day"
  * @param $date: string, datetime-object or timestamp 
  * @param $language string p.E. "de-DE", "en-GB" 
  * @return mixed string name if ok, false Error or Date is not a Holiday,
  *  string "?" no Name for the language in Database  
  */
  public function holidayName($date = "today", $language = null){
    if(is_string($date)) {
      $date = date_create($date)->format("Y-m-d");
    } elseif($date instanceof DateTime) {
      $date = $date->format("Y-m-d");
    } elseif(is_int($date)) {
      $date = date("Y-m-d", $date);
    } else {
      throw new InvalidArgumentException("incorrect Parameter date '$date' "); 
    }
    
    $id = $this->getId($date);
    if(!$id) return false;  //holiday by date not found
    
    if($language === null) $language = $this->language;
    $name = $this->getHolidayNameById($id, $language);

    return $name;
  }

 /**
  * return array( 'YYYY-MM-DD' => holidayname, ..)
  * the array is sorted by ascending date
  * @param integer year full year p.E. 2018
  * @param string $language p.E. "en_GB"
  * @return array
  */
  public function holidayList($year = null, $language = null){
    if(empty($year)) $year = date('Y');
    if($language === null) $language = $this->language;

    $hList = array();
    foreach($this->config as $id => $row){
      $curDate = $this->getDateFromDBrow($row, $year);
      if($curDate === false) continue;

      $hList[$curDate] = $this->getHolidayNameById($id, $language);
    }
    ksort($hList); 
    return $hList;
  }

  /**
   * returns array of string dates Y-m-d 
   * wich are a holiday between two dates
   * @param mixed $startDate
   * @param mixed $endDate
   * @param array $weekDayFilterList with numbers 0..6 for Sunday ..Saturday
   *   default null for all weekdays 
   * @return array of strings ["Y-m-d",..]
   */
  public function dateList($startDate, $endDate, $weekDayFilterList = null)
  {
    if(is_string($startDate)) {
      $startDate = date_create($startDate)->format("Y-m-d");
    } elseif($startDate instanceof DateTime) {
      $startDate = $startDate->format("Y-m-d");
    } elseif(is_int($startDate)) {
      $startDate = date("Y-m-d", $startDate);
    } else {
      throw new InvalidArgumentException("incorrect Parameter date '$startDate' "); 
    }

    if(is_string($endDate)) {
      $endDate = date_create($endDate)->format("Y-m-d");
    } elseif($endDate instanceof DateTime) {
      $endDate = $endDate->format("Y-m-d");
    } elseif(is_int($endDate)) {
      $endDate = date("Y-m-d", $endDate);
    } else {
      throw new InvalidArgumentException("incorrect Parameter date '$endDate' "); 
    }

    $startYear = (int)substr($startDate,0,4);
    $endYear = (int)substr($endDate,0,4);
    $dateArr = array();
    foreach($this->config as $id => $row){
      for($year = $startYear; $year <= $endYear; $year++){
        $curDate = $this->getDateFromDBrow($row, $year);
        if($curDate !== false AND $curDate >= $startDate AND $curDate <= $endDate){
          if(is_array($weekDayFilterList)){
            $numberWeekDay = (int)date_create($curDate)->format('w');
            if(!in_array($numberWeekDay,$weekDayFilterList)) continue;
          }
          $dateArr[] = $curDate;
        } 
      }
    }
    sort($dateArr);
    return $dateArr;
  }
  
 /**
  * return array of datetime objects
  * the array is sorted by ascending date
  * datetime objects are extended with public property holidayName
  * @param year integer full year p.E. 2018
  * @param $language string p.E. "en_GB"
  * @return array
  */
  public function dateTimeList($year = null, $language = null){
    $dtArr = array();
    foreach(self::holidayList($year, $language) as $strDate => $name) {
      $dt = date_create($strDate);
      if(is_object($dt)) {
        $dt->holidayName = $name;
        $dtArr[] = $dt;
      }
    }
    return $dtArr;
  }


 /**
  * return true id if date is a holiday or false
  * @param mixed $date string, datetime-object or timestamp 
  * @return bool
  */
  public function isHoliday($date = 'today'){
    if(is_string($date)) {
      $date = date_create($date)->format("Y-m-d");
    } elseif($date instanceof DateTime) {
      $date = $date->format("Y-m-d");
    } elseif(is_int($date)) {
      $date = date("Y-m-d", $date);
    } else {
      throw new InvalidArgumentException("date '$date' incorrect"); 
    }
    
    return $this->getId($date) ? true : false;
    
  }
  
 /**
  * get List of Names from DB as 
  * array(idholiday => name, ..) by nameFilter and language
  * return false if not found
  * @param string $nameFilter Filter for name , caseinsenitive 
  * @param string $language  how de or de-ch, default Default Language
  * @param bool $onlyCurrentRegion bool, default false
  * @return mixed  
  */
  public function getNames($nameFilter = "", $language = null, $onlyCurrentRegion = false) {
    if($language === null) $language = $this->language;
    $sql = "SELECT idholiday, name
      FROM names 
      WHERE language LIKE :language COLLATE NOCASE"; 
      $param = array("language" => "%".$language."%");
    if($nameFilter != "") {
      $sql .= " AND name LIKE :nameFilter COLLATE NOCASE";
      $param['nameFilter'] = "%".$nameFilter."%";
    }
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($param);
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    if($rows AND $onlyCurrentRegion) {
      $rows = array_intersect_key($rows, $this->config);  
    }
    return $rows ? $rows : false; 
  }
  
 /**
  * get List of Names from DB as 
  * array(idholiday => name, ..) by nameFilter and language
  * return false if not found
  * @param nameFilter: Filter for name , caseinsenitive 
  * @param yearStart: integer YYYY , default current year
  * @param countYears:  default 1 or end of year (end of year > 
  * @param language string how de or de-ch, default Default Language
  * @return mixed
  */
  public function holidayNameList($nameFilter = "",$yearStart = null, $countYears=1, $language = null) {
    if($yearStart === null) $yearStart = date("Y");
    //countYears or end of year
    if($countYears > 1000 AND $countYears >= $yearStart){
       $countYears = $countYears - $yearStart + 1; 
    }
    //get id => name array
    $idNames = $this->getNames($nameFilter, $language, true);
    if($idNames === false) return false;

    $list = array();
    for($i=0; $i < $countYears; $i++){
      $year = $yearStart + $i;
      foreach($idNames as $id => $HolidayName) {
        if(array_key_exists($id, $this->config)) {
          $curDate = $this->getDateFromDBrow($this->config[$id], $year);
          if($curDate) $list[$curDate] = $HolidayName;
        } else {  
        //id not in current region
        }
      }
    }  
    ksort($list);
    return $list;
  }
 
 /** 
  * get config, may use as debugging info
  * @return array
  */
  public function getConfig(){
    return $this->config;
  }
  
  // get id from holiday-Table
  // @param $strDate string format "YYYY-MM-DD"
  protected function getId($date){
    list($year,$month,$day) = explode("-",$date);
    foreach($this->config as $id => $row){
      if($date == $this->getDateFromDBrow($row, $year, $month, $day)) {
        return $id;
      }
    }
    return false;
  }
  
 /*
  * get date as string YYYY-MM-DD
  * $code string : special Code 
  * $year 1600 < year < 2100
  * return false if error
  */
  protected function getMovableDate($code, $year, $month = 1, $day = 1){
    if(empty($code)) {
      return sprintf("%04d-%02d-%02d",$year, $month, $day);
    }
    if(strpos($code,'}}')) {
      $callback = function($match) use($year, $month, $day){
        $m0 = $match[0];
        $c = __CLASS__;
        if($m0 == '{{year}}') return $year;
        if($m0 == '{{month}}') return $month;
        if($m0 == '{{day}}') return $day;
        if($m0 == '{{easter}}') return $c::getEasterDate($year);
        if($m0 == '{{easter_o}}') return $c::getEasterDate($year, true);
        if($m0 == '{{passover}}') return $c::getPassoverDate($year);
        if($m0 == '{{islamic}}') return $c::getHijriDate($year, $month, $day);
        if($m0 == '{{hebrew}}') return $c::getJewishDate($year, $month, $day);  //jewish
        if(extension_loaded('intl')) {
          $intlCalendars = array(
            '{{japanese}}','{{buddhist}}','{{chinese}}','{{persian}}',
            '{{indian}}','{{coptic}}','{{ethiopic}}'
          );
          if(in_array($m0,$intlCalendars)) {
            return $c::getCalendarDate(trim($m0,'{}'), $month, $day, $year);  
          }
        }
        return $m0;  //not replace
      };
      $code = preg_replace_callback('~\{\{[a-z_]+\}\}~', $callback, $code);
      
      
      //check extends methods
      if(preg_match('~\{\{([a-z]+)\}\}~',$code,$match)) {
        $methodName = $match[1];
        if(method_exists($this, $methodName)) {
          $replacement = $this->$methodName($year, $month, $day);
          $code = str_replace($match[0],$replacement,$code);
        } else { //error
          throw new Exception("Error ".__CLASS__.": unknown special entry ".$match[0]); 
        }      
      }
    }
    
    $modifiers = explode("|", $code);
    $date = date_create($year."-".$month."-".$day);
    foreach($modifiers as $modify) {
      //check for datelist {{2018:2/3,..}}
      if(preg_match('~\{\{(\d{4}):(.*)\}\}~',$modify,$match)) {
        $startYear = $match[1];
        $values = explode(",",$match[2]);
        $key = $year - $startYear; 
        if(array_key_exists($key,$values)) {
          $modify = str_replace($match[0],$values[$key],$modify);
        } else {
          //if year not exist in list get static day and month
          $modify = $month."/".$day;
        }
        $date->modify($modify);
      }

      elseif(preg_match('~^\{\{\?([DdmLY]+)(!?=)([^}]+)\}\}(.*)~',$modify,$match)) {
        $curFmt = $date->format($match[1]);
        $found = stripos($match[3], $curFmt) !== false;
        if($found === ($match[2] == "=")) {
          //condition true
          if($match[4] !== "") $date->modify($match[4]);
        } elseif($match[4] === "") {
          return false;
        }
      } else { 
        $date->modify($modify);
      }        
      $errArr = date_get_last_errors();
      if($errArr['error_count']) return false;
    }
    
    return $date->format("Y-m-d");
   }
  
  //get easter-date as string YYYY-MM-DD
  public static function getEasterDate($year,$orthodox = false){
    if($orthodox) {
      $flag = CAL_EASTER_ALWAYS_JULIAN;
      $basisDate = $year."-4-3";
    } else {
      $flag = CAL_EASTER_ALWAYS_GREGORIAN;
      $basisDate = $year."-3-21";
    }
    $date = date_create($basisDate)
      ->modify(easter_days($year, $flag).' Days')
    ;
    return $date
      ->modify((-(int)$date->format('w'))." Days")
      ->format("Y-m-d");
  }
  
 /*
  * calculate the first day of Passover (Gauß)
  * @params: $year integer as YYYY, interval 1900 to 2099
  * @return date as string YYY-MM-DD
  */
  public static function getPassoverDate($year){
    $a = (12*$year+12)%19; 
    $b = $year%4;
    $m = 20.0955877 + 1.5542418 * $a + 0.25 * $b - 0.003177794 * $year; 
    $mi = (int)$m;
    $mn = $m-$mi;
    $c = ($mi + 3 * $year + 5 * $b + 1)%7; 
    if($c==2 OR $c==4 OR $c==6) {
      $mi += 1;
    } elseif($c==1 AND $a > 6 AND $mn >= (1367/2160)) {
      $mi += 2;
    } elseif ($c==0 AND $a > 11 AND $mn > (23269/25920)) {
      $mi += 1;
    }
    return date_create($year."-3-13")
      ->modify($mi." Days")
      ->format("Y-m-d"); 
  }
  
 /* 
  * get the gregorian Date for the year $gregYear
  * @param integer $gregYear: greg.Year (2007..2031)
  * @param integer $hijriMonth: Month hijri-Calendar
  * @param integer $hijriMonth: Month hijri-Calendar
  * @return date as string YYY-MM-DD or false if error
  */
  public static function getHijriDate($gregYear, $hijriMonth, $hijriDay) {
    $gy = $gregYear;
    for($i=0;$i<3;$i++){
      $hijri = self::GregToHijri($gy,1,1);
      $greg  = self::HijriToGreg($hijri[0],$hijriMonth,$hijriDay);
      if($greg[0] == $gregYear) {
        return sprintf("%04d-%02d-%02d",$greg[0],$greg[1],$greg[2]);
      }
      $gy += ($greg[0] < $gy) ? 1 : -1;
    }
    return false;
  }  

 /*
  * convert Gregorian Date to Hijri
  * return array($year, $month, $day)
  */  
  public static function GregToHijri($y,$m,$d)
  {
      $jd = gregoriantojd ($m, $d, $y);
      $days360month = 10631;
      $y = $days360month / 30.0;
      $shift1 = 8.01 / 60.0;
      $z = $jd - 1948085; //-$epochAstro
      $cyc = floor($z / $days360month);
      $z = $z - $days360month * $cyc;
      $j = floor(($z - $shift1) / $y);
      $z = $z - floor($j * $y + $shift1);
      $year = 30 * $cyc + $j;
      $month = (int)floor(($z + 28.5001) / 29.5);
      if ($month == 13) $month = 12;
      $day = $z - floor(29.5001 * $month - 29);
      return array($year,$month,$day);
  }

 /*
  * convert Hijri Date to Gregorian
  * return array($year, $month, $day)
  */  
  public static function HijriToGreg($y,$m,$d){
   $jd = (int)((11 * $y + 3) / 30) + 354 * $y + 
     30 * $m - (int)(($m - 1) / 2) + $d + 1948440 - 385;
   list($month,$day,$year) = explode("/", jdtogregorian($jd));
   return array($year,$month,$day);
  }
  
 /*
  * return string date YYYY-MM-DD;
  */
  public static function getJewishDate($gregYear, $jewishMonth, $jewishDay) {
    $gy = $gregYear;
    for($i=0;$i<3;$i++){
      $jd1 = gregoriantojd(1,1,$gy);
      $jw1 = jdtojewish($jd1);  //"Monat/Tag/Jahr"
      list($jmonth,$jday,$jyear) = explode("/",$jw1);
      $jd = jewishtojd($jewishMonth,$jewishDay,$jyear);
      $greg = jdtogregorian($jd);
      list($gmonth,$gday,$gyear) = explode("/",$greg);
      if($gyear == $gregYear) {
        return sprintf("%04d-%02d-%02d",$gyear,$gmonth,$gday);
      }
      $gy += ($gyear < $gy) ? 1 : -1;
    }
    return false;
  }

 /*
  * get greg.Date from $calMonth and $calDay in calendar $cal
  * return string date "Y-m-d" or false if error
  */
  public static function getCalendarDate($calendar, $calMonth, $calDay, $gregYear){
    if(!extension_loaded('intl')) return false;
    $formatter = IntlDateFormatter::create(
      'de_DE', 
      IntlDateFormatter::FULL, 
      IntlDateFormatter::FULL,
      null, 
      IntlDateFormatter::GREGORIAN,
      'yyyy-MM-dd'
    );
    
    $cal = IntlCalendar::createInstance(null, "@calendar={$calendar}");
    
    $gy = $gregYear;
    for($i=0;$i<3;$i++){
      $msTs = mktime(0,0,0,1,1,$gy) * 1000.0;
      $cal->setTime($msTs);
      $cal->set(IntlCalendar::FIELD_MONTH, $calMonth-1); //month-1 !
      $cal->set(IntlCalendar::FIELD_DAY_OF_MONTH, $calDay); 
      $gregDate = $formatter->format($cal);
      $gyear = (int)substr($gregDate,0,4);
      if($gyear == $gregYear) return $gregDate;
      $gy += ($gyear < $gy) ? 1 : -1;
    }
    return false;  
  }  
  



 /*
  * return data as string YYYY-MM-DD or false
  */
  protected function getDateFromDBrow($row, $year, $month = 1, $day = 1){
    //accept years
    if(strlen($row->year) >= 4) {
      $dbEntry = $row->year;
      if(ctype_digit($dbEntry)) {
        //only YYYY
        if($dbEntry != $year) return false;
      } elseif(preg_match('~^\d{4}-$~',$dbEntry)) {
        //YYYY-
        if($year < (int)$dbEntry) return false;
      } elseif(preg_match('~^-\d{4}$~',$dbEntry)) {
        //-YYYY
        if($year > (-(int)$dbEntry)) return false;
      } elseif(!in_array($year,$this->listToArray($dbEntry))) {
        return false;
      }
    }
        
    if(strlen($row->except_year) >= 1) {
      if($row->except_year == '*') return false;
      if(in_array($year,$this->listToArray($row->except_year))){
        return false;
      }
    }

    $curmonth = $row->month ? $row->month : $month;
    $curday = $row->day ? $row->day : $day;
    if($row->special) {
      //getMovableDate return false if not match or error
      $curDate = $this->getMovableDate($row->special,$year,$curmonth,$curday);
    } else {
      $curDate = sprintf("%04d-%02d-%02d",$year,$curmonth,$curday);
    }
    return $curDate;
  }
  
  //create config-array
  private function createConfig($region, $typFilter){
    $filterRegion = preg_replace('~\-?\*$~','',$region);
    $allOption = $filterRegion != $region; //de*
    $sql = "SELECT id, year, except_year, month, day, special, region
      FROM holidays 
      WHERE typ & ".(int)$typFilter.
      " ORDER BY except_year DESC, year DESC";

    $stmt = $this->pdo->query($sql);
    
    $this->config = array();
    $match = false;
    foreach($stmt as $row){
      $dbRegios = explode(",",$row->region);
      foreach($dbRegios as $region) {
        if($allOption) {
          if ($match = (stripos($region,$filterRegion) === 0)) break;
        } else {       
          if($match = (stripos($filterRegion,$region) === 0)) break;
        }
      }
      if($match) $this->config[$row->id] = $row;
    }
    $this->region = $filterRegion;
    $this->typFilter = $typFilter;
    return true;
  }

  //string list to array
  private function listToArray($strList){
    $strList = preg_replace_callback(
      '/(\d{4})-(\d{4})/',
      function(array $m){
        return implode(",",range($m[1],$m[2]));
      },
      $strList
    );
    return explode(",",$strList);  
  }

  //get Name by id and $language
  private function getHolidayNameById($id, $language){
    $sql = "SELECT name 
      FROM names 
      WHERE idholiday = $id AND language LIKE :language COLLATE NOCASE
      LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(array("language" => "{$language}%"));
    $row = $stmt->fetch();
    if($row) return $row->name;
    return "?";
  }
  
  
  
}  
