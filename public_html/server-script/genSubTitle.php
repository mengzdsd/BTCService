#!/usr/bin/php
<?php

/*
 *
*/

error_reporting(E_ALL);

if ($argc != 5) {
?>
Usage:
<?php echo basename($argv[0]); ?> <Input_srt_file> <Start_video_time> <End_video_time> <Name_of_output_srt>

  Input_srt_file : the path of srt file generated from PDA.
  Start_video_time : The video start time, format is HH:MM:SS.
  End_video_time : The video end time, format is HH:MM:SS.
  Name_of_out_srt : The name of the output srt file.

  Example : <?php echo basename($argv[0]); ?> ~/2015-01-01-test.srt 11:00:04 11:05:56 srtVideo.srt

<?php
} else {
  $strCurDir = getcwd();
  $strIFRealPath = realpath($argv[1]);
  $strOFRealPath = dirname($strIFRealPath) . DIRECTORY_SEPARATOR . $argv[4];
  $strVideoStartTime = $argv[2];
  $strVideoEndTime = $argv[3];

  // Set default timezion to Asia/Chongqing
  date_default_timezone_set('Asia/Chongqing');

  $rsIF = fopen($strIFRealPath,'r') or die ("Error: Unable to open $strIFRealPath, please check it.");

  if (file_exists($strOFRealPath)) {
    unlink($strOFRealPath) or die ("Error: $strOFRealPath already exist, and can not remove it.");
  }
  $rsOF = fopen($strOFRealPath,'x');
  if (!$rsOF) {
    fclose($rsIF);
    die ("Error: Unable to create $strOutputFilePath to write, please check it.");
  }
  
  $n = 0;
  $arrSrtSections = array();
  $arrTempSection = array();
  while (!feof($rsIF)) {
    $line = fgets($rsIF);
    $intLineLength = strlen($line);
    if ($intLineLength > 0) {
      if (strlen($line) !== 2) {
        $arrTempSection[$n] = strTrim($line,"\r\n");
        $n++;
      } else {
        array_push($arrSrtSections,$arrTempSection);
        $arrTempSection = array();
        $n = 0;
      }
    }
  }
  array_push($arrSrtSections,$arrTempSection);
  unset($arrTempSection);
  
  fclose($rsIF);

  $strVideoDate = getDateStr($strIFRealPath);
  
  $intVideoStartTimeStamp = getTimeStamp($strVideoDate,$strVideoStartTime);
  $intVideoEndTimeStamp = getTimeStamp($strVideoDate,$strVideoEndTime);
  
  $arrSrtDatas = array();
  foreach ($arrSrtSections as $arrSrtSection) {
    $arrTimes = getTimeStamps($strVideoDate,$arrSrtSection[1]);
    $arrTemp = array($arrTimes[0],$arrTimes[1],$arrSrtSection[2]);
    array_push($arrSrtDatas,$arrTemp);
  }

  $intStartN = 0;
  $intEndN = 0;
  $intCount = count($arrSrtDatas);
  if (($intVideoEndTimeStamp <= $arrSrtDatas[0][0]) || ($intVideoStartTimeStamp >= $arrSrtDatas[$intCount-1][1])) {
    $errorMessage = <<<EOD
Error: The video's period is not in the period of this srt file.
(The start time of video later on this srt file end, or the end time of this video early than tis srt file start.)

EOD;
    die($errorMessage);
  }
  
  for ($i = 0; $i < $intCount; $i++) {
    if ($intVideoStartTimeStamp < $arrSrtDatas[$i][1]) {
      $intStartN = $i;
      break;
    }
  }
  
  for ($i = 0; $i < $intCount; $i++) {
    if ($intVideoEndTimeStamp <= $arrSrtDatas[$i][0]) {
      $intEndN = $i - 1;
      break;
    }
    $intEndN = $i;
  }
  
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
    fputs($rsOF,$strTimeLine);
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
      fputs($rsOF,$strTimeLine);
      fputs($rsOF,$arrSrtDatas[$i][2]);
      fputs($rsOF,"\r\n\r\n");
    }
  }
  
  fclose($rsOF);
  echo "Success : generate $strOFRealPath for video.", PHP_EOL;
  exit(0);
}
?>

<?php	# Functions
/*
 * Convert a date time to unix stamp
 * $strDate : YYYY-MM-DD
 * $strTime : hh:mm:ss
 * return: the seconds of time stamp
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

/*
 * Trim the specified characters in the end of a string
 * $str : the string need to be trimed
 * $strTrim : the charaters need to be cut
 * return: the string after trimed.
 */
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

/*
 * Conver the seconds stamp to the time str the srt file need.
 * $intSeconds : the seconds from the beginning
 * return : the string with the format (HH:MM:SS,SSS)
 */
function secondsToStr($intSeconds) {
  return date('H:i:s',$intSeconds) . ',000';
}

/*
 * Get the date string from the input file
 * $strInputFile : the real path of the input file.
 * return : the date string with format (YYYY-MM-DD)
 */
function getDateStr($strInputFile) {
  $strFileName = basename($strInputFile);
  if (preg_match('/\d{4}-\d{2}-\d{2}/', $strFileName, $matches)) {
    return $matches[0];
  } else {
    return date('Y-m-d', filemtime($strInputFile));
  }
}

?>
