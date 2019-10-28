<?php
/**
.---------------------------------------------------------------------------.
|  Software: PHPcheck - simple Test class for output in web browser         |
|   Version: 1.3.21                                                         |
|      Date: 04.06.2018                                                     |
| ------------------------------------------------------------------------- |
| Copyright © 2015..2018 Peter Junk (alias jspit). All Rights Reserved.     |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
 */
 class PHPcheck{
  const version = '1.3.20';
  const DISPLAY_PRECISION = 16;
  const DEFAULT_FLOAT_PRECISION = 14;
  //
  public $cmpFloatPrecision = self::DEFAULT_FLOAT_PRECISION;
  //CSS for getHtml
  private $defaultCss = 'table.phpchecktab {
    border-collapse: collapse; 
    border: 1px solid black; 
    padding: 4px;
  }
  table.phpchecktab > thead > tr > th { border: 1px solid black;padding: 4px;}
  table.phpchecktab > tbody > tr > td { border: 1px solid black;padding: 4px;}
  
  table.phpchecktab > tbody > tr > th:first-child{
    min-width:160px;
  }
  table.phpchecktab > tbody > tr > td:nth-child(4){
    overflow: auto;
  }
  #form_phpcheck {
    position:relative;
  }
  .Send_Form_Element{
    position:absolute;
    top: -30px;
    left:650px;
  }
  .test_Error {
    font:bold 12pt Arial;
    color:#A00;
  }
  ';
  
  private $fileName = '';
  private $file = array();
  private $checks = array();
  private $tabSpace = 4;
  
  private $filterFkt = 'default';
  private $outputVariant = "";
  private $maxResultChars = 80;
  
  private $css = "";
  private $lastWarning = array();
  //
  private $startActive = false;
  private $startMcTime = 0.0;
  private $startLine = 0;
  private $checkComment = '';
  
  private $tsInstanceCreate;
    
  private $autoStartWithCheck = true;
  
  private $oldErrorLevel = array();
  
  private $obStartCalled = false;
  private $obContent = "";
  
  private $nameSubmitButton = "SubmitButton_phpcheck";
  
  private $headline = "";
 
 /* 
  * create a instance of phpcheck
  */
  public function __construct(){
    set_error_handler(array($this,'checkErrorHandler'));
    ini_set('serialize_precision', self::DISPLAY_PRECISION);
    
    $this->tsInstanceCreate = microtime(true);
  }
  
 /* 
  * the method marks the start of new test
  * param $comment set a title/comment of following test  
  */
  public function start($comment=''){
    $this->startActive = true;
    $this->checkComment = $comment;
    //start line
    $backtrace = debug_backtrace();
    $this->startLine = $backtrace[0]['line'];
    $currFileName = $backtrace[0]['file'];
    $this->cacheScriptFile($currFileName);
    //time
    $this->startMcTime = microtime(true);
  }

 /* 
  * the method marks the start of new test with Output
  * param $comment set a title/comment of following test 
  * works how start() and expectOutput()  
  */
  public function startOutput($comment=''){
    $this->startActive = true;
    $this->checkComment = $comment;
    //start line
    $backtrace = debug_backtrace();
    $this->startLine = $backtrace[0]['line'];
    $currFileName = $backtrace[0]['file'];
    $this->cacheScriptFile($currFileName);
    //ob_start
    $this->expectOutput();
    //time
    $this->startMcTime = microtime(true);
  }  

 /* 
  * use this method's to finish a test
  * param $actual the result of test  
  * param $testResult true/false for test was ok or not
  * param $comment set a title/comment, if method start use this comment will be overwerite
  */
  public function check($actual,$testResult,$comment=''){
    $mTime = microtime(true);
    //ob_get_clean();
    $this->addCheckArr($actual,$testResult,$comment,$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }
  
 /*
  * finish a test
  * param $actual the actual result
  * param $expected the expected result
  * param $comment new comment
  * param $delta if not 0, then check abs($expected-$actual) <= $delta
  */  
  public function checkEqual($actual,$expected,$comment='', $delta = 0){
    $mTime = microtime(true);
    $equal = $expected===$actual;
    if(!$equal){
      if($delta > 0) {
        //for delta > 0 compare as float
        $equal = abs($expected-$actual) <= $delta ;
      }
      elseif(is_float($actual) OR is_float($expected)) {
        $equal = $this->roundPrecision($actual,$this->cmpFloatPrecision) 
               == $this->roundPrecision($expected,$this->cmpFloatPrecision);        
      }
    }
    $this->addCheckArr($actual,$equal,$comment,$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }

 /*
  * finish a test
  * param $actual the actual result, if is string it will be show as hex-string
  * param $expected the expected result
  * param $comment new comment
  */  
  public function checkEqualHex($actual,$expected,$comment=''){
    $mTime = microtime(true);
    //ob_get_clean();
    $testResult = $expected===$actual;
    if(is_string($actual) and $actual !== ""){
      $actual = implode(' ',str_split(bin2hex($actual),2));
    }
    $this->addCheckArr(strtoupper($actual),$testResult,$comment,$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }

 /*
  * finish a test
  * param $actual the actual result html-Code
  * param $containStrings: if not "" the result must contain Strings
  * param $ignoreLibXmlErrors: ignor Errors how unknown Tags
  */  
  public function checkHTML($actual = null, $containStrings = "", $ignoreLibXmlErrors = false){
    $mTime = microtime(true);
    
    if($actual === null AND $this->obStartCalled = true) {
      $actual = ob_get_clean();
      $this->obStartCalled = false;
    }
    //else ob_get_clean();
    $htmlErrors = $this->getValidateHtmlError($actual,$ignoreLibXmlErrors);
    $testResult = $htmlErrors === "";
    if($containStrings !== "") {
      $testResult = $testResult && $this->strContains($actual,$containStrings);
    }
    $this->addCheckArr($actual,$testResult,"",$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }

 /*
  * finish a test
  * param $actual the actual result 
  * param $containStrings: if not "" the result must contain Strings
  */  
  public function checkContains($actual, $containStrings = ""){
    $mTime = microtime(true);
    $testResult = true;
    if($containStrings !== "") {
      $testResult = $this->strContains(var_export($actual,true),$containStrings);
    }
    $this->addCheckArr($actual,$testResult,"",$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }

  
 /*
  * finish a test
  * param $actual the actual result html-Code
  */  
  public function checkOutput($containStrings = ""){
    $mTime = microtime(true);
    $actual = $this->getOutput();
    $testResult = $this->strContains($actual,$containStrings);
    $this->addCheckArr($actual,$testResult,"",$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }
 
 /*
  * make a multiple test
  * par $user: function-name, closure, static method "class::method" or array($class,'method')
  * par $data: multiple array with comment as key and array(par,par2,..,expectedValue)
  */  
  public function checkMultiple($userFct,array $data) {
    $backtrace = debug_backtrace();
    $checkLine = $backtrace[0]['line'];
    $currFileName = $backtrace[0]['file'];
    $this->cacheScriptFile($currFileName);

    //parameter -> code
    $fctMixValToCode = function($mixVal){
      if(is_resource($mixVal)) return '{resource}';
      $code = var_export($mixVal, true);
      $code = preg_replace('~^(\w+)(::__.*| ?\(.+)~isu','{$1}',$code);
      return $code;
    };
    
    foreach($data as $comment => $checkValues) {
      $expectedResult = array_pop($checkValues);
      $phpCodeInfo = '$expected = '
        .$fctMixValToCode($expectedResult)
        .";\r\n";
      if(is_Array($userFct) AND isset($userFct[0]) AND isset($userFct[1])) {
        $className = is_object($userFct[0]) 
          ? get_class($userFct[0]) 
          : trim(var_export($userFct[0],true),"' ")
        ;
        $strCodeuserFct = $className.'->'.$userFct[1];
      }
      else {
        $strCodeuserFct = trim(var_export($userFct,true),"' ");
      }
      $valuesCode = array_map($fctMixValToCode, $checkValues);     
      $phpCodeInfo .= '$result = '
        .$strCodeuserFct
        .'('.implode(",",$valuesCode).');';
      //
      $mTime = microtime(true);
      $actual = call_user_func_array($userFct, $checkValues);
      $mcTime = microtime(true) - $mTime;
      //
      $this->checks[] = array(
        'line' => $checkLine,
        'comm' => is_numeric($comment) ? "" : $this->checkComment.$comment,
        'taskCode' => $phpCodeInfo,
        'result'   => $actual,
        'filterResult' => $this->filter($actual),
        'test'   => $actual == $expectedResult,
        'warning' => $this->lastWarning,
        'mctime' => $mcTime,
      );
    }
    $this->startLine = $checkLine;
    $this->startActive = $this->autoStartWithCheck; 
    $this->lastWarning = array();
    $this->checkComment = '';
    //retur a array of results from this check
    $offset = count($this->checks) - count($data);
    return array_slice($this->checks, $offset, count($data));
  }
  
  /* 
  * use this method's to finish a test
  * param $closure a function with testobject 
  * param $exceptionTyp the exception or "" for all
  * param $comment set a title/comment, if method start use this comment will be overwerite
  */
  public function checkException($closure,$exceptionTyp = "",$comment=''){
    
    $mTime = microtime(true);
    if(is_callable($closure)) {
      try{
        $closure();
        $actual = "";
        $testResult = false;
      }
      catch(exception $e){
        $testResult = $exceptionTyp !== "" ? $e instanceOf $exceptionTyp : true;
        $actual = "Exception: ".get_class($e);
      }
    }
    else {
      $testResult = false;
      $actual = "PHPchek-Error: first parameter must be a closure";
    }    
    $this->addCheckArr($actual,$testResult,$comment,$mTime);
    $lastResult = $this->getLastResult();
    $this->startMcTime = microtime(true);
    return $lastResult;
  }
 
  
 /*
  * prepare the output buffer for expect a output
  * to check a output you must use expectOutput or startOutput
  */  
  public function expectOutput(){
    if(!$this->obStartCalled) {
      $this->obStartCalled = true;
      $this->obContent = "";
      ob_start();
    }
  }

 /*
  * stop the output buffering and get the output 
  */  
  public function getOutput(){
    if($this->obStartCalled) {
      $this->obStartCalled = false;
      $this->obContent = ob_get_clean();
    }
    return $this->obContent;
  }
  

 /*
  * return a array of resultarrays from all collected checks
  array ( 0 => array (
    'line' => 3,            //Line number source           
    'comm' => "1.test",     //Comment
    'taskCode' => '$result = 3 * 4;',  //source code ckeck
    'result' => 12,         //result that will be checked
    'filterResult' => "12", //result after filter
    'test' => true,         //result of check
    'warning' =>            //Errors and warnings that occur during the test 
    array (
    ),
    'mctime' => 6.9E-6,     //Time required by the test
  ))
  */
  public function getResults(){
    return $this->checks;
  }

 /*
  * return last result or result with the specified key
  * return false if error
  */
  public function getLastResult($key = null){
    $lastResult = end($this->checks);
    reset($this->checks);
    if($key === null) return $lastResult;
    if(isset($lastResult[$key])) return $lastResult[$key];
    return false;
  }
  
 /* 
  * return the PHP Code (taskCode) as highlightPhpString from the last check 
  */
  public function getLastPHPcode(){
    return $this->highlightPhpString($this->getLastResult('taskCode'));  
  }

 /*
  * returns the number of failed tests
  */
  public function getErrorCount(){
    $ErrorCount = 0;
    foreach($this->checks as $check){
      if(!$check['test']) $ErrorCount++;
    }
    return $ErrorCount;
  }
  
 /* 
  * return count of check-calls
  */
  public function getCheckCount(){
    return count($this->checks);
  }

 /* 
  * Delete collected results from all checks 
  */
  public function clearChecks(){
    $this->checks = array();
    return true;
  }
  
 /* 
  * set new error level and push old level
  * return old level
  * use this to show warnings and errors only in results
  * and not in top of site
  */
  public function setErrorLevel($level){
    $oldLevel = error_reporting($level);
    $this->oldErrorLevel[] = $oldLevel; 
    return $oldLevel;
  }

 /* 
  * restore error level 
  * return: current level
  */
  public function restoreErrorLevel(){
    if(count($this->oldErrorLevel)) {
      error_reporting(array_shift($this->oldErrorLevel));
    }
    return error_reporting();
  }
  
  //set Tabspace for view PHP-Code
  public function setTabSpacePHPview($tabSpace = 4){
    $this->tabSpace = $tabSpace;
  }
  
  /*
   * true (default): check is automatically start for new test
   * false : start must be called
   */
  public function setStartWithCheck($enable = true){
    $this->autoStartWithCheck = (bool)$enable;
    if(!(bool)$enable ) $this->startActive = false;
    return true;
  }

 /*
  * set a new result filter for getTable
  * results are filtered with "var_export" (default) 
  * If you want present text or html without filter, 
  * call setResultFilter with parameter empty string
  * $filtername = 'html' : present strings without filter, other with "var_export"
  */  
  public function setResultFilter($filterName = 'default'){
    $this->filterFkt = $filterName;
  }
  
 /*
  * set text for additional Headline
  */
  public function setHeadline($text) {
    $this->headline = $text;
  }
  
 /*
  * set variant for output
  * Valid variant: "" , "form" 
  * "form" creates a button, that allow form-elements send with POST
  */  
  public function setOutputVariant($variant){
    $this->outputVariant = strtolower($variant);
  }

 /*
  * get count (cycle number) for POST-cykles
  * return 0 after first Start
  */  
  public function getPostCount() {
    return isset($_POST[$this->nameSubmitButton]) 
      ? key($_POST[$this->nameSubmitButton])
      : 0 ;      
  }
  
  
 /*
  * create a full table with results
  * return: html-string
  * param: tableAttribut e.g. 'style=""'
  * param: set $clearResults = false if you may not delete collected results
  */
  public function getTable($tableAttribut = "", $clearResults = true){
    $testResults = $this->getResults();
    //
    $tableArr = array();
    foreach($testResults as $key => $el){
      $comment = $el['comm'] ? $el['comm'] : ("Test ".($key+1));
      $tableArr[$key]['comm'] = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
      
      $tableArr[$key]['line'] = $el['line'];
      
      $tableArr[$key]['taskCode'] = $this->highlightPhpString($el['taskCode']);
      
      //Result
      $result = "";
      foreach($el['warning'] as $error) {
        $result .= $this->getErrName($error["errcode"]).": ".$error["errmsg"];
        $result .= " (".basename($error["fileName"])." line ".$error["line"].")<br>";
      }
      $result .= $el['mctime'] ? ('['.sprintf('%.1f',$el['mctime']*1000).' ms]<br>') : "";
      //$result .= $this->filter($el['result']);
      $result .= $el['filterResult'];
      $tableArr[$key]['result'] = $result;
      //test Ok
      $tableArr[$key]['test'] = $el['test'] ? 'Ok' : '<div class="test_Error">Error</div>';;
    }
    $html = $this->table($tableAttribut,array('Comment','Line','Code','Result','Test'),$tableArr);
    if($clearResults) $this->checks = array();
    return $html;
  }
  
 /*
  * return default html header for a site as string
  */
  public function getHtmlHeader($title = 'Test'){
    $html = '<!DOCTYPE html>
      <html lang="de">
      <head>
      <title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>
    ';
    $this->addCSS(); //load Defaults if CSS not set
    if($this->css != ""){
      $html .= '
      <style>'.$this->css.'
      </style>
    ';
    }
    $html .= '
      </head>
      <body>
    ';
    
    return $html;
  }
  
 /*
  * add CSS to header
  */
  public function addCSS($css = ""){
    if(empty($css) AND empty($this->css)){
      $this->css = $this->defaultCss;
    }
    else {
      $this->css .= "\r\n".$css;
    }
    return true;
  }
  
 /* 
  * return default html footer as string
  */
  public function getHtmlFooter(){
    $html ='
      </body>
      </html>
    ';
    return $html;
  }
  
 /*
  * get total Info (Number of checks, errors..) as HTML
  */
  public function getTotalInfo(){
    $totalTime = sprintf("%.2f",microtime(true)-$this->tsInstanceCreate);
    $html = '<b>'.basename($this->fileName);
    $html .= ' Total: '.$this->getCheckCount().' Tests, '.$this->geterrorCount().' Errors</b><br>';
    if($this->headline != "") $html .= $this->headline . "<br>";
    $html .= 'PHPCheck V'.self::version.', OS: '.PHP_OS;
    $html .= ', PHP-Version: '.PHP_VERSION.' ('. PHP_INT_SIZE * 8 .' Bit), Time: '.$totalTime.' s';
    $html .= ', Memory: '.sprintf('%.1f',memory_get_peak_usage(true)/1024/1024).'M ('.ini_get('memory_limit').')';
    $html .= "<br/>\r\n";
    return $html;
  }
 
 /* 
  * provides a complete tabular analysis as a web page
  * typical use
    $t = new phpcheck();
    $t->start("first test");
    $result = 5 + 4;  //our first test
    $t->checkEqual($result,9); //end first test
    echo $t->getHTML();  //show analysis
  */
  public function getHtml($withFooter = true){
    if($this->css == "") $this->addCSS($this->defaultCss);
    
    $html = $this->gethtmlHeader('Test '.basename($this->fileName));
    $html .= $this->getTotalInfo();
    
    if($this->outputVariant == "form") {
      $html .= '<form id="form_phpcheck" method="POST">';
    }
    $html .= $this->getTable('class="phpchecktab"');
    if($this->outputVariant == "form") {
      //$postZyklus = isset($_POST[$this->nameSubmitButton]) ? ($_POST[$this->nameSubmitButton] + 1) : 0;
      $postZyklus = $this->getPostCount() + 1;
      $nameSubmit = $this->nameSubmitButton."[".$postZyklus."]";
      $html .= '<div class="Send_Form_Element"><input type="submit" name="'.$nameSubmit;
      $html .= '"  value="Send Form Elements as POST"> Cycle:'.$postZyklus.'</div></form>';
    }
    if($withFooter) $html .= $this->gethtmlFooter();
    
    return $html;
  }
  
 /*
  * Helper
  */
  
 /*
  * return a random ASCII-String
  * param $length (default 10 chars)
  */
  public function getRandomString($length = 10){
    $characters = str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $charactersLength = strlen($characters);
    while($charactersLength < $length) {
      $characters .= str_shuffle($characters);
      $charactersLength += $charactersLength;
    }
    return substr($characters,0,$length);
  }
  
 /*
  * Syntax highlighting of a string with php-Code
  */
  public function highlightPhpString($phpCode){
    $phpTags = array('<?php ',' ?>');
    $phpCode = str_replace("\t",str_repeat(' ',$this->tabSpace), $phpCode);  //Tabs ersetzen
    $codeHighlight = highlight_string($phpTags[0].rtrim($phpCode).$phpTags[1],true);
    $codeHighlight = str_replace(array('&lt;?php','?&gt;'),'',$codeHighlight);
    return preg_replace('/&nbsp;/','',$codeHighlight,1);
  }

/* 
 * validate a html-string and get the error notice
 * return "" if ok and not error found
 * return error-message if errror
 * param: HTML as string
 * param: flag $ignoreLibXmlErrors true/false default false
 */
  public static function getValidateHtmlError($html, $ignoreLibXmlErrors = false){
    if(preg_match('~</?body>~',$html) == 0){
      $html = '<body>'.$html.'</body>';
    }
    $code = '<?xml encoding="utf-8" ?><!DOCTYPE html>'.$html;
    $doc = new DOMDocument();
    $previousUseErrors = libxml_use_internal_errors(true);
    $loadError = !$doc->loadHTML($code); 
    $errors = libxml_get_errors();
    libxml_use_internal_errors($previousUseErrors);
    if($loadError) {
      return 'DOM Load Error';
    }
    if($errors and !$ignoreLibXmlErrors) {
      $errMsg = array();
      foreach($errors as $LibXMLError){
        $errMsg[] = "Ln:".$LibXMLError->line.
          " - ".trim($LibXMLError->message);
      }    
      return implode("<br>\r\n",$errMsg);
    }
    $node = $doc->getElementsByTagName("body")->item(0);
    $fragment = $doc->saveHTML($node);
    $fragment = htmlspecialchars_decode($fragment,ENT_QUOTES); 
    $html = htmlspecialchars_decode($html,ENT_QUOTES); 
    $pattern = array(
      '~[\x00-\x20"\']~',
      '~</(p|dt|dd|li|option|thead|th|tbody|tr|td|tfoot|colgroup)>~i'
    );
    list($fragment,$html) = preg_replace($pattern,'',array($fragment,$html));
    if($fragment === $html) return "";
    return 'Error html structure';
  }

 /*
  * @return string version of class
  *  return false if error, "" Versioninfo not found
  * @param class: object, string (optional)
  */
  public function getClassVersion($class=NULL){
    if($class===NULL) $class = __CLASS__;
    try{
      $rc = new ReflectionClass($class);
    }
    catch (Exception $e) {
      return false;
    }
    //const Version = "4.5"
    $constArr = $rc->getConstants();
    $keys = preg_grep('~^version$~i',array_keys($constArr));
    if(isset($keys[0])) return (string)$constArr[$keys[0]];
    //as doc comment @version (doc-comment must begin with /**  
    if($docComment = $rc->getDocComment()){
      $r = preg_match('/@Version[:= \t]*(\S+)/is',$docComment,$matches);
      if($r)return $matches[1];
      $r = preg_match('/Version[:= \t]*(\S+)/is',$docComment,$matches);
      if($r)return $matches[1];
    }
    return "";
  }
  
 /*
  * @return Float-Value with reduced precision
  * @param $floatValue: input (float)
  * @param $overallPrecision: 1..20 (default 14)
  */
  public function roundPrecision($floatValue, $overallPrecision = self::DEFAULT_FLOAT_PRECISION)
  {
    $p = min(20,max(0,$overallPrecision-1));
    $f =(float)sprintf('%.'.$p.'e',$floatValue);
    return $f;
  }

  
/*
 * protected
 */
 
  protected function addCheckArr(
    $actual,      //actual Value, result of useraction
    $testResult,  //testresult (true/false)
    $comment,     //additional Comment
    $mTime       // current microtime(true);
  ) {
    //
    $backtrace = debug_backtrace();
    $checkLine = $backtrace[1]['line'];
    $currFileName = $backtrace[1]['file'];
    $this->cacheScriptFile($currFileName);
    //test-code + time
    if($this->startActive) {
      $codeArr = array_slice($this->file,$this->startLine,$checkLine-$this->startLine);
      $codeLines = implode('',$codeArr);
      $mcTime = $mTime - $this->startMcTime;
      $comment = $comment ? $comment : $this->checkComment;
      $line = $this->startLine +1;
    }
    else {
      $line = $checkLine-1;
      $codeLines = implode('',array_slice($this->file,$line,1));
      $mcTime = null;
    }
    
    $this->checks[] = array(
      'line' => $line,
      'comm' => $comment,
      'taskCode' => $codeLines,
      'result'   => $actual,
      'filterResult' => $this->filter($actual),
      'test'   => (bool)$testResult,
      'warning' => $this->lastWarning,
      'mctime' => $mcTime,
    );
    $this->startLine = $checkLine;
    $this->startActive = $this->autoStartWithCheck; 
    $this->lastWarning = array();
    $this->checkComment = '';
  }
  
  
  private function table($att, $titleArr, array $dataArr) {
    $html = '<table '.$att.'>'."\r\n";
    //col title
    $html .= '<thead><tr>';
    foreach($titleArr as $i => $title) {
      $html .= '<th>'. $title. '</th>'; 
    }
    $html .= '</tr></thead>'."\r\n";
    //table 
    $html .= '<tbody>'."\r\n";    
    foreach($dataArr as $k => $subArr) {
      $html .= '<tr>'."\r\n";
      foreach($subArr as $i => $colValue) {
        $html .= '<td>'.$colValue."</td>\r\n";
      }
      $html .= '</tr>'."\r\n";
    }

    $html .= '</tbody>'."\r\n".'</table>'."\r\n";
    return $html;
  }
  
 /*
  * the internal error handler, do not use
  */  
  public function checkErrorHandler($errcode, $errmsg, $fileName, $line) {
    $this->lastWarning[] = array(
      "errcode" => $errcode,
      "errmsg" => $errmsg,
      "fileName" => $fileName, 
      "line" => $line
    );
    return false;  //do normal error handling
  }
  
 /*
  * get Error-Name from given code-number
  */
  private function getErrName($errorCode) {
    $errCodeNames = array(
      E_ERROR               => "Error",   //1 
      E_WARNING             => "Warning", //2
      E_PARSE               => "Parse Error", //4 
      E_NOTICE              => "Notice",  //8
      E_CORE_ERROR          => "Core Error", //16
      E_CORE_WARNING        => "Core Warning",//32
      E_COMPILE_ERROR       => "Compile Error",//64
      E_COMPILE_WARNING     => "Compile Warning",//128
      E_USER_ERROR          => "User Error",  //256
      E_USER_WARNING        => "User Warning", //512
      E_USER_NOTICE         => "User Notice",  //1024
      E_STRICT              => "Strict Notice",//2048
      E_RECOVERABLE_ERROR   => "Recoverable Error",
    );
    return isset($errCodeNames[$errorCode]) ? $errCodeNames[$errorCode] : ("Unknown error ".$errorCode);
  }
  
  private function filter($result) {
    if($this->filterFkt == 'default') {
      $code = var_export($result,true);
      //Remove control characters and double spaces
      $code = preg_replace('/[\r\n\t ]+/s'," ",$code);
      if(strlen($code) > $this->maxResultChars) { 
        $code = substr($code,0,$this->maxResultChars).' ..';
      }
      $code = htmlspecialchars($code,ENT_NOQUOTES|ENT_IGNORE,'UTF-8',false);
    }
    elseif($this->filterFkt == 'html') {
      if(is_string($result)){
        $code = $result;
      }
      else {
        $code = htmlspecialchars(var_export($result,true),ENT_NOQUOTES,'UTF-8',false);
        $code = '<pre>'.$code.'</pre>';
      }
    }
    elseif(is_callable($this->filterFkt)) {
      $fkt = $this->filterFkt;
      $code = $fkt($result);
    }
    else {
      $code = $result; //without filter
    }

    return $code;
    
  }
  
  //return true if ok
  private function cacheScriptFile($filename){
    $filename = realpath($filename);
    if($filename !== false) {
      if($filename !== $this->fileName) {
        //php-file new load
        $this->fileName = $filename;
        $this->file = file($filename);
      }
      return true;
    }
    return false;
  }
  
 /*
  * check if $haystack contains all $needles
  * return true/false
  * param $haystack: The string to search in
  * param $needles: a array of strings or a list of strings
  *  if needle is a list, the elements must be present in the order (>V 1.3.16)
  * param $delimiter: Delimiter for stringlist 
  */  
  private function strContains($haystack, $needles, $delimiter = ",") {
    if($needles === "") return true;
    if(is_string($needles)) {
      $needles = explode($delimiter,$needles);
    }
    elseif(!is_array($needles)) $needles = array(var_export($needles,true));
    
    $oldPos = 0;
    foreach($needles as $needle) {
      $curPos = strpos($haystack,$needle,$oldPos);
      if($curPos === false OR $curPos < $oldPos) return false;
      $oldPos = $curPos;
    }
    return true;
  }
  

}
