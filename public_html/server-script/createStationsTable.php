<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * 该脚本用来从xml获取站点名， 然后组成站点的输入表格
 * xml文件在 xml/line/<line>-stations.xml
 * 接收日期[line]作为输入参数
 * 返回含<table> element的字符串
 * <table>样例
 * <table id="downCountTalbe">
  <tr>
  <td>
  AAAAAA(上/下)
  <input id="s1u" type="number" min="0" value="0">
  <input id="s1d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s2u" type="number" min="0" value="0">
  <input id="s2d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s3u" type="number" min="0" value="0">
  <input id="s3d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s4u" type="number" min="0" value="0">
  <input id="s4d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s5u" type="number" min="0" value="0">
  <input id="s5d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s6u" type="number" min="0" value="0">
  <input id="s6d" type="number" min="0" value="0">
  </td>
  </tr>
  <tr>
  <td>
  AAAAAA(上/下)
  <input id="s7u" type="number" min="0" value="0">
  <input id="s7d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s8u" type="number" min="0" value="0">
  <input id="s8d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s9u" type="number" min="0" value="0">
  <input id="s9d" type="number" min="0" value="0">
  </td>
  <td>
  AAAAAA(上/下)
  <input id="s10u" type="number" min="0" value="0">
  <input id="s10d" type="number" min="0" value="0">
  </td>
  </tr>
  </table>
 */

error_reporting(E_ALL);

//$lineName = 'c58';
$lineName = filter_input(INPUT_GET,'line');
$fileDir = "../xml/line/";
$stationsInfoFile = realpath($fileDir . $lineName . "-stations.xml");

$xmlDoc = new DOMDocument();
$strRet = "";

if ($xmlDoc->load($stationsInfoFile)) {
//    echo "Open file Success.\n";
    $strRet = <<<'EOD'
<table id="downCountTalbe">
<tr>
EOD;
    
    $sep = 1;
    $stationNodeList = $xmlDoc->getElementsByTagName('station');
    foreach ($stationNodeList as $station) {
        $idValue = $station->getAttribute('id');    // station id
        $stationName = $station->getElementsByTagName('name')->item(0)->nodeValue;      // Station name
        $upIdValue = $idValue . 'u';
        $downIdValue = $idValue . 'd';
        
        $strTd = "<td>$stationName(上/下)<br/>\n<input id=\"$upIdValue\" type=\"number\" min=\"0\" value=\"0\">\n<input id=\"$downIdValue\" type=\"number\" min=\"0\" value=\"0\">\n</td>\n";
        $strRet .= $strTd;
        if (($sep % 6) === 0) {
            $strRet .= "</tr><tr>";
        }
        $sep++;
    }
    $numberOfStation = --$sep;
    $strRet .= '</tr><table>';
    $strRet = "$numberOfStation|" . $strRet;
    echo $strRet;
    return true;
} else {
    echo "NULL";
    return false;
}