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
  $objDateTime = DateTime::createFromFormat('Y-m-d H:i:s',$strDateTime);
  $intTimeStamp = $objDateTime->getTimestamp();
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

function secondsToStr($intSeconds) {
  return gmdate('H:i:s',$intSeconds) . ',000';
}

$strInputFilePath = "./test.srt";
$strOutputFilePath = "output.srt";
$strVideoStartTime = "11:00:04";
$strVideoEndTime = "11:05:56";
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
# echo $intVideoStartTimeStamp, PHP_EOL;
# echo $intVideoEndTimeStamp, PHP_EOL;
# 
# $arrSTs = getTimeStamps($strVideoDate,$arrSrtSections[0][1]);
# echo $arrSTs[0], PHP_EOL;
# echo $arrSTs[1], PHP_EOL;
#
$intRt = 0;
$arrSrtDatas = array();
foreach ($arrSrtSections as $arrSrtSection) {
  $arrTimes = getTimeStamps($strVideoDate,$arrSrtSection[1]);
  $arrTemp = array($arrTimes[0],$arrTimes[1],$arrSrtSection[2]);
  array_push($arrSrtDatas,$arrTemp);
}
#print_r($arrSrtDatas);
$intStartN = 0;
$intEndN = 0;
$intCount = count($arrSrtDatas);
#echo $intCount, PHP_EOL;
#echo $arrSrtDatas[0][0] . ' - ' . $arrSrtDatas[$intCount-1][1] , PHP_EOL;
if (($intVideoEndTimeStamp <= $arrSrtDatas[0][0]) || ($intVideoStartTimeStamp >= $arrSrtDatas[$intCount-1][1])) {
  $errorMessage = <<<EOD
Error: The video's period is not in the period of this srt file.
(The start time of video later on this srt file end, or the end time of this video early than tis srt file start.)

EOD;
  die($errorMessage);
}

for ($i = 0; $i < $intCount; $i++) {
#  echo $intVideoStartTimeStamp . ' - ' . $arrSrtDatas[$i][1] , PHP_EOL;
  if ($intVideoStartTimeStamp < $arrSrtDatas[$i][1]) {
    $intStartN = $i;
    break;
  }
}

for ($i = 0; $i < $intCount; $i++) {
#  echo $intVideoEndTimeStamp . ' + ' . $arrSrtDatas[$i][0] , PHP_EOL;
  if ($intVideoEndTimeStamp <= $arrSrtDatas[$i][0]) {
    $intEndN = $i - 1;
    break;
  }
  $intEndN = $i;
}

#echo $intStartN . ' : ' . $intEndN , PHP_EOL;
#echo secondsToStr(1419937556 - 1419937204) , PHP_EOL;

if ($intStartN == $intEndN) {
  $intElapse = $arrSrtDatas[$intStartN][0] - $intVideoStartTimeStamp;
  $strStartTime = "";
  $strEndTime = "";
  if ($intElapse > 0) {
    $strStartTime = secondsToStr($intElapse);
  } else {
    $strStartTime = '00:00:00,000';
  }

  $intElapse = $arrSrtDatas[$intEndN][1] - $intVideoEndTimeStamp;
  if ($intElapse > 0) {
    $strEndTime = secondsToStr($intVideoEndTimeStamp - $intVideoStartTimeStamp);
  } else {
    $strEndTime = secondsToStr($arrSrtDatas[$intEndN][1] - $intVideoStartTimeStamp);
  }

  $strTimeLine = $strStartTime . ' --> ' . $strEndTime . "\r\n";
  fputs($rsOF,"0\r\n");
#  echo $strTimeLine;
  fputs($rsOF,$strTimeLine);
#  echo $arrSrtDatas[$intStartN][2] . "\r\n";
  fputs($rsOF,$arrSrtDatas[$intStartN][2]);
} else {
  for ($i = $intStartN; $i <=$intEndN; $i++) {
    $strStartTime = "";
    $strEndTime = "";
    if ($i == $intStartN) {
      $intElapse = $arrSrtDatas[$i][0] - $intVideoStartTimeStamp;
      if ($intElapse > 0 ) {
	$strStartTime = secondsToStr($intElapse);
      } else {
	$strStartTime = '00:00:00,000';
      }
      $strEndTime = secondsToStr($arrSrtDatas[$i][1] - $intVideoStartTimeStamp); 
    } elseif ($i == $intEndN) {
      $intElapse = $arrSrtDatas[$i][1] - $intVideoEndTimeStamp;
      if ($intElapse > 0 ) {
	$strEndTime = secondsToStr($intVideoEndTimeStamp - $intVideoStartTimeStamp);
      } else {
	$strEndTime = secondsToStr($arrSrtDatas[$i][1] - $intVideoStartTimeStamp);
      }
      $strStartTime = secondsToStr($arrSrtDatas[$i][0] - $intVideoStartTimeStamp);
    } else {
      $strStartTime = secondsToStr($arrSrtDatas[$i][0] - $intVideoStartTimeStamp);
      $strEndTime = secondsToStr($arrSrtDatas[$i][1] - $intVideoStartTimeStamp);
    }
    $strSeq = ($i - $intStartN) . "\r\n";
    $strTimeLine = $strStartTime . ' --> ' . $strEndTime . "\r\n";
    fputs($rsOF,$strSeq);
#    echo $strTimeLine;
    fputs($rsOF,$strTimeLine);
#    echo $arrSrtDatas[$i][2] . "\r\n";
    fputs($rsOF,$arrSrtDatas[$i][2]);
    fputs($rsOF,"\r\n\r\n");
  }
}

fclose($rsOF);
