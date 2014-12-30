<?php

/*
 *
*/

error_reporting(E_ALL);

/*
 * $strDate : YYYY-MM-DD
 * $strTime : hh:mm:ss
 */
function getTimeStamp($strDate,$strTime) {
  $strDateTime = $strDate . ' ' . $strTime;
  $objDateTime = date_create_from_format('Y-m-d H:i:s',$strDateTime);
  $intTimeStamp = date_timestamp_get($objDateTime);
  return $intTimeStamp;
}

/*
 * $strDate : YYYY-MM-DD
 * $strTimes : hh:mm:ss,sss --> hh:mm:ss,sss
 */
function getTimeStamps($strDate,$strTimes) {
  $arrTimes = explode(' --> ',$strTimes);
  $arrTime1 = explode(',',$arrTimes[0]);
  $arrTime2 = explode(',',$arrTimes[1]);
  $intTimeStamp1 = getTimeStamp($strDate,$arrTime1[0]);
  $intTimeStamp2 = getTimeStamp($strDate,$arrTime2[0]);
  return array($intTimeStamp1,$intTimeStamp2);
}

function strTrim($str,$strTrim) {
  $len = strlen($strTrim);
  if (substr($str,-$len) == $strTrim) {
    $str = chop($str);
    $str = chop($str);
    return $str;
  } else {
    return $str;
  }
}

$strInputFilePath = "./test.srt";
$strOutputFilePath = "output.srt";
$strVideoStartTime = "11:00:05";
$strVideoEndTime = "11:05:55";
$strVideoDate = "2014-12-30";

$strIFRealPath = realpath($strInputFilePath);
$strOFRealPath = realpath($strOutputFilePath);
# echo $strOFRealPath, PHP_EOL;
if (file_exists($strOutputFilePath)) {
  unlink($strOFRealPath) or die ("Error: $strOutputFilePath already exist, and can not remove it.");
}

$rsIF = fopen($strIFRealPath,'r') or die ("Error: Unable to open $strInputFilePath, please check it.");
$rsOF = fopen($strOutputFilePath,'x');
if (!$rsOF) {
  fclose($rsIF);
  die ("Error: Unable to create $strOutputFilePath, please check it.");
}

$n = 0;
$arrSrtSections = array();
$arrTempSection = array();
while (!feof($rsIF)) {
#  fputs($rsOF,fgets($rsIF));
  $line = fgets($rsIF);
  $intLineLength = strlen($line);
  if ($intLineLength > 0) {
    if (strlen($line) !== 2) {
#      echo $n . ': ' . $line;
      $arrTempSection[$n] = strTrim($line,"\r\n");
      $n++;
    } else {
#      echo "--------------------", PHP_EOL;
      array_push($arrSrtSections,$arrTempSection);
      $arrTempSection = array();
      $n = 0;
    }
  }
}
array_push($arrSrtSections,$arrTempSection);
unset($arrTempSection);

fclose($rsIF);
print_r($arrSrtSections);

$intVideoStartTimeStamp = getTimeStamp($strVideoDate,$strVideoStartTime);
$intVideoEndTimeStamp = getTimeStamp($strVideoDate,$strVideoEndTime);
echo $intVideoStartTimeStamp, PHP_EOL;
echo $intVideoEndTimeStamp, PHP_EOL;

$arrSTs = getTimeStamps($strVideoDate,$arrSrtSections[0][1]);
echo $arrSTs[0], PHP_EOL;
echo $arrSTs[1], PHP_EOL;

fclose($rsOF);
