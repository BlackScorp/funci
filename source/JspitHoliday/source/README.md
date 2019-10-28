# holiday

A php class for determining holidays for many countries, regions and languages.
All defintions are in a small SQLite database that can be changed and expanded by the user.
The database "JspitHoliday.sqlite" currently supports the following countries

  "DE":Germany, "AT":Austria, "NL":Netherlands, "DK":Denmark, "FR":France,
  "IT":Italy, "ES":Spain(\*), "LU":Luxembourg, "BE":Belgium,
  "GR":Greece, "SK":Slovakia, "IE":Ireland, "CY":Cyprus, "PT":Portugal,
  "EE":Estonia, "FI":Finland, "LV":Latvia, "LT":Lithuania, "MT":Malta
  "CZ":Czech Republic, "PL":Poland, "CH":Switzerland, "GB":Great Britain(\*),
  "SE":Sweden, "BG":Bulgaria, "HR":Croatia, "RO":Romania, "SI":Slovenia,
  "HU":Hungary, "US":United States(\*),"JP":Japan,"RU:Russia

  (*) without regional holidays

 Database can be used without any claim to correctness and completeness.

## Usage

Include class JspitHoliday.php (1 File) directly with require or use a autoloader.
Put JspitHoliday.sqlite in the same Directory how JspitHoliday.php.
If you want to put the configuration JspitHoliday.sqlite in another directory, 
then specify the full path in the 2.Parameter of the constructor.

```php
<?php

$holiday = new JspitHoliday("DE-BB"); 
$holidayList = $holiday->holidayList(2018,'en');

```
$holidayList contain a array with all public holidays from 
country Germany(de) Region Brandenburg (bb) with english names(en) for the year 2018.

```php
array (
  '2018-01-01' => "New Year's Day",
  '2018-03-30' => "Good Friday",
  '2018-04-01' => "Easter",
  '2018-04-02' => "Easter Monday",
  '2018-05-01' => "Labor Day",
  '2018-05-10' => "Ascension of Christ",
  '2018-05-20' => "Whit Sunday",
  '2018-05-21' => "Whit Monday",
  '2018-10-03' => "Day of German Unity",
  '2018-10-31' => "Reformation Day",
  '2018-12-25' => "Christmas Day",
  '2018-12-26' => "Boxing Day",
)
```
Regions can be nested to depth 3. The notation is based on the ISO standard 3361.
Examples: "DE", "NL", "DE-BY", "DE-BY-SCH-A"
Last for Germany-Bavaria-Schwabing-Augsburg(City). 
The languages can be divided into dialects, e.g. 'de-DE' , 'de-CH'.

Get all holiday dates between $dateStart and $dateEnd which are from a Monday to Friday.

```php
$holiday = JspitHoliday::create('de-BE');
$dateArray = $holiday->dateList("2019-03-01","2019-03-31",array(1,2,3,4,5));
```

Further examples:

```php
$dateTime = new DateTime("1 May 2018 08:00");

$holidaysDE = JspitHoliday::create("DE");
if($holidaysDE->isHoliday($dateTime)) {
  echo "1 May 2018 is in DE a holiday";
}

//holidayName
$holidayName = $holidaysDE->holidayName('3 Oct','en');
if($holidayName) {
  echo $holidayName;
  //'Day of German Unity'
}

//holidayNameList
$holidaysIL = JspitHoliday::create("IL","JspitHoliday.sqlite");
$list = $holidaysIL->holidayNameList("Pessach I",2018,2022,'de');

var_dump($list);
/*
array(5) {
  ["2018-03-31"]=>
  string(9) "Pessach I"
  ["2019-04-20"]=>
  string(9) "Pessach I"
  ["2020-04-09"]=>
  string(9) "Pessach I"
  ["2021-03-28"]=>
  string(9) "Pessach I"
  ["2022-04-16"]=>
  string(9) "Pessach I"
}
*/

/*
 * get Config from a URL
 */
$url = "http://example.com/data/JspitHoliday.sqlite";
$tmpfname = tempnam(sys_get_temp_dir(), "holiday.sqlite");
$copyOk = copy($url,$tmpfname);

$holidaysDE = new JspitHoliday('de',$tmpfname);

```
## Class-Info

| Info | Value |
| :--- | :---- |
| Declaration | class JspitHoliday |
| Datei | JspitHoliday.php |
| Date/Time modify File | 2018-08-09 15:23:52 |
| File-Size | 20 KByte |
| MD5 File | 5796df9354e50ad44ef21df9930211d0 |
| Version | 1.30 |
| Date | 2018-08-09 |

## Public Methods

| Methods and Parameter | Description/Comments |
| :-------------------- | :------------------- |
| public function __construct($filterRegion = &quot;&quot;, $db = null, $typFilter = self::TYPE_ALL) | Constructs the class instance<br>@param string $filterRegion Country/Region ISO 3361 Alpha2 (&#039;DE&#039;,&#039;DE-BY&#039;..) <br>@param string $db filename for SQLite or PDO Object, default: holiday.sqlite<br>@param int $typFilte Filter for Holiday-Type for SQLite, default: holiday::TYPE_ALL<br>@throws InvalidArgumentException |
| public static function create($filterRegion = &quot;&quot;, $sqliteFile = null, $typFilter = self::TYPE_ALL) | return a new class instance<br>@param string $filterRegion Country/Region ISO 3361 Alpha2 (&#039;DE&#039;,&#039;DE-BY&#039;..) <br>@param string $sqliteFile filename for SQLite, default: holiday.sqlite<br>@param int $typFilte Filter for Holiday-Type for SQLite, default: holiday::TYPE_ALL<br>@return object JspitHoliday<br>@throws InvalidArgumentException |
| public function setLanguage($language = &quot;en-GB&quot;) | set Default Language<br>@param string $language p.E. &quot;de-DE&quot;, &quot;en-GB&quot; <br>@return $this |
| public function getLanguage() | get Default Language<br>@return string default language |
| public function setRegion($filterRegion) | set region<br>@param string $filterRegion Country/Region ISO 3361 Alpha2 (&#039;DE&#039;,&#039;DE-BY&#039;..) <br>@return $this |
| public function getRegion() | Returns the current Region<br>@return string |
| public function setTypFilter($typFilter = self::TYPE_ALL) | set Filter Holiday Type<br>@param int typ Filter<br>@throws Exception |
| public function holidayName($date = &quot;today&quot;, $language = null) | get Name from a Holiday p.e: &quot;New Year&#039;s Day&quot;<br>@param $date: string, datetime-object or timestamp <br>@param $language string p.E. &quot;de-DE&quot;, &quot;en-GB&quot; <br>@return mixed string name if ok, false Error or Date is not a Holiday,<br>string &quot;?&quot; no Name for the language in Database  |
| public function holidayList($year = null, $language = null) | return array( &#039;YYYY-MM-DD&#039; =&gt; holidayname, ..)<br>the array is sorted by ascending date<br>@param integer year full year p.E. 2018<br>@param string $language p.E. &quot;en_GB&quot;<br>@return array |
| public function dateList($startDate, $endDate, $weekDayFilterList = null) | returns array of string dates Y-m-d <br>wich are a holiday between two dates<br>@param mixed $startDate<br>@param mixed $endDate<br>@param array $weekDayFilterList with numbers 0..6 for Sunday ..Saturday<br>default null for all weekdays <br>@return array of strings [&quot;Y-m-d&quot;,..] |
| public function dateTimeList($year = null, $language = null) | return array of datetime objects<br>the array is sorted by ascending date<br>datetime objects are extended with public property holidayName<br>@param year integer full year p.E. 2018<br>@param $language string p.E. &quot;en_GB&quot;<br>@return array |
| public function isHoliday($date = &#039;today&#039;) | return true id if date is a holiday or false<br>@param mixed $date string, datetime-object or timestamp <br>@return bool |
| public function getNames($nameFilter = &quot;&quot;, $language = null, $onlyCurrentRegion = false) | get List of Names from DB as <br>array(idholiday =&gt; name, ..) by nameFilter and language<br>return false if not found<br>@param string $nameFilter Filter for name , caseinsenitive <br>@param string $language how de or de-ch, default Default Language<br>@param bool $onlyCurrentRegion bool, default false<br>@return mixed  |
| public function holidayNameList($nameFilter = &quot;&quot;,$yearStart = null, $countYears=1, $language = null) | get List of Names from DB as <br>array(idholiday =&gt; name, ..) by nameFilter and language<br>return false if not found<br>@param nameFilter: Filter for name , caseinsenitive <br>@param yearStart: integer YYYY , default current year<br>@param countYears: default 1 or end of year (end of year &gt; <br>@param language string how de or de-ch, default Default Language<br>@return mixed |
| public function getConfig() | get config, may use as debugging info<br>@return array |
| public static function getEasterDate($year,$orthodox = false) | get easter-date as string YYYY-MM-DD |
| public static function getPassoverDate($year) | calculate the first day of Passover (Gauß)<br>@params: $year integer as YYYY, interval 1900 to 2099<br>@return date as string YYY-MM-DD |
| public static function getHijriDate($gregYear, $hijriMonth, $hijriDay) | get the gregorian Date for the year $gregYear<br>@param integer $gregYear: greg.Year (2007..2031)<br>@param integer $hijriMonth: Month hijri-Calendar<br>@param integer $hijriMonth: Month hijri-Calendar<br>@return date as string YYY-MM-DD or false if error |
| public static function GregToHijri($y,$m,$d) | convert Gregorian Date to Hijri<br>return array($year, $month, $day) |
| public static function HijriToGreg($y,$m,$d) | convert Hijri Date to Gregorian<br>return array($year, $month, $day) |
| public static function getJewishDate($gregYear, $jewishMonth, $jewishDay) | return string date YYYY-MM-DD; |
| public static function getCalendarDate($calendar, $calMonth, $calDay, $gregYear) | get greg.Date from $calMonth and $calDay in calendar $cal<br>return string date &quot;Y-m-d&quot; or false if error |

## Constants

| Declaration/Name | Value | Description/Comments |
| :--------------- | :---- | :------------------- |
|  const TYPE_OFFICIAL = 1; | 1 |   |
|  const TYPE_BANK = 2; | 2 |   |
|  const TYPE_OBSERVED = 4; | 4 |   |
|  const TYPE_OTHER = 8; | 8 |   |
|  const TYPE_4 = 16; | 16 |   |
|  const TYPE_5 = 32; | 32 |   |
|  const TYPE_6 = 64; | 64 |   |
|  const TYPE_ALL = 0x7FFF; | 32767 |   |

## Define country depending holidays

All holidays are dates defined in the table 'holidays' and names for all languages in the table 'names'.
Working with SQLite database will be easy if you use a tool like DB Browser for SQLite ( http://sqlitebrowser.org ) and
the test enviroment phpcheck. Download as zip and unzip in a public directory.
Then you can call phpcheck.JspitHolidayOffice.php in the browser. Edit the Source of phpcheck.JspitHolidayOffice.php. 
Take the big list of countries in comment and use your country for that. 
Check after each new database entry if the holiday date is correctly determined.

### Fields of  holidays table:

| Field | Description |
| ----- | ----------- |
| id | id, autoincrement, reference to 'idholiday' in the table names |
| comment | a comment (not a name for a holiday) |
| year | free or "*" for all years, a year YYYY for only this year, -YYYY to year, YYYY- from year, a range YYYY-YYYY, a list of years YYYY,YYYY,.. |
| except_year | free for no exception, YYYY for except only this year, except a range YYYY-YYYY, except a list of years YYYY,YYYY,.., "*" except all |
| month | used for fixed months |
| day | used for fixed days |
| special | A pipe with relative date formates and wildcards. Pipe elements are are separated by \| . {{name}} is a wildcard. Some examples: "first sunday of september {{year}}\|next thursday" ,'third sunday of september {{year}}' , '{{easter}}\|+1 Day' |
| region | A list auf Countrycodes/Regions. Countrycode-[[[Subdivision]-Subregion1]-Subregion2] |
| typ | Type of holiday (TYPE_OFFICIAL, TYPE_BANK..) |

### Fixed date every year

Table holidays

| id  | comment     | year | except_year | month | day | special | region      | typ |
| --- | ----------- | ---- | ----------- | ----- | --- | ------- | ----------- | --- | 
| 1   | NewYear     |      |             | 1     | 1   |         | DE,CH,AT,NL | 1   |

Table names

| id  | idholiday   | language | name           | 
| --- | ----------- | -------- | -------------- | 
|     | 1           | en-GB    | New Year's Day | 
|     | 1           | de-DE    | Neujahr        | 
|     | 1           | de-CH    | Neujahr        |
|     | 1           | ru-RU    | Новый год      |

### Fixed date for a year or rage of years

Table holidays

| id  | comment         | year  | except_year | month | day | special | region | typ | 
| --- | --------------- | ----- | ----------- | ----- | --- | ------- | ------ | --- |  
|     | Reformation Day | 2017  |             | 10    | 31  |         | DE     | 1   |
|     | Day of Unity    | 1990- |             | 10    | 3   |         | DE     | 1   |

### Movable dates

Table holidays

| id  | comment        | year | except_year | month | day | special                            | region | typ |
| --- | -------------- | ---- | ----------- | ----- | --- | ---------------------------------- | -------| --- |  
| 7   | Buß und Bettag |      |             | 11    | 23  | last Wed                           | DE-SN  | 1   |
| 23  | Bettag         |      |             |       |     | third sunday of september {{year}} | CH     | 1   |


### Dates depend on religious holidays

You can use this wildcards:

- {{easter}}    Catholic Easter
- {{easter_o}}  Orthodox Easter
- {{passover}}  Passover I


Others can be defined in an extension class.

Table holidays

| id  | comment     | year | except_year | month | day | special             | region      | typ | 
| --- | ----------- | ---- | ----------- | ----- | --- | ------------------- | ----------- | --- |  
| 8   | Ascension   |      |             |       |     | {{easter}}\|+39 Days | DE,CH,AT,NL | 1   |


Table names

| id  | idholiday   | language | name                | 
| --- | ----------- | -------- | ------------------- | 
|     | 8           | en-GB    | Ascension of Christ | 
|     | 8           | de-DE    | Christi Himmelfahrt | 
|     | 8           | de-CH    | Auffahrt            |

### Dates depend on other calendars

The first wildcard can mark a calendar. The entries day and date refer to this calendar.
- {{islamic}}   islamic calendar
- {{hebrew}}    hebrew calendar

The following calendars can still be used, if the intl extension is available:
- {{japanese}}
- {{buddhist}}
- {{chinese}}
- {{persian}}
- {{indian}}
- {{coptic}}
- {{ethiopic}}

Example:
The Chinese New Year is celebrated on the first day and first month of the traditional Chinese calendar.

Table holidays

| id  | comment           | year | except_year | month | day | special             | region | typ | 
| --- | ----------------- | ---- | ----------- | ----- | --- | ------------------- | ------ | --- |  
|     | Chinese New Year  |      |             |  1    | 1   | {{chinese}}         | CN     | 1   |

### Dates with filter conditions

If a holiday is Sunday (or weekend), then in some countries  a substitute day in the following week is an additional holiday.
Example: 
The 5th of May is Children's Day in Japan. Is the 5th of May a Sunday (or the 6th of May a Monday),
then the 6th of May is a holiday. You can define this date with a filter condition.

Table holidays

| id  | comment        | year | except_year | month | day | special             | region | typ | 
| --- | -------------- | ---- | ----------- | ----- | --- | ------------------- | ------ | --- |  
|     | Childrens Day  |      |             |  5    | 5   |                     | JP     | 1   |
|     | Childrens Day+ |      |             |  5    | 6   | {{?D=Mon}}          | JP     | 2   |

### Dates with movement conditions

A holiday date will be postponed under certain conditions. 
If an operation is noted after the condition, then it will only be executed if the condition is true.
Example for special entry: {{?D=Thu}}+1 Day

### Dates without formula

Some dates of holidays can not be described by a rule or it is too difficult to do that.
For these cases, the date must be set for each year. With a special wildcard you can create a list for next years.

| id  | comment          | year      | except_year | month | day | special               | region | typ | 
| --- | -----------------| --------- | ----------- | ----- | --- | --------------------- | -------| --- |  
| 35  | Independence Day | 2018-2020 |             |       |     | {{2018:4/19,5/9,4/29}}| IL     | 1   |

## Requirements

PHP 5.4 - 7.2
