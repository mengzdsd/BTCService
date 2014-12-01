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
 * <table>
  <tr>
  <td>
  <label for="s1">AAAAAA</label>
  <input id="s1" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s2">AAAAAA</label>
  <input id="s2" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s3">AAAAAA</label>
  <input id="s3" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s4">AAAAAA</label>
  <input id="s4" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s5">AAAAAA</label>
  <input id="s5" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s6">AAAAAA</label>
  <input id="s6" type="number" min="0" value="0">
  </td>
  </tr>
  <tr>
  <td>
  <label for="s7">AAAAAA</label>
  <input id="s7" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s8">AAAAAA</label>
  <input id="s8" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s9">AAAAAA</label>
  <input id="s9" type="number" min="0" value="0">
  </td>
  <td>
  <label for="s10">AAAAAA</label>
  <input id="s10" type="number" min="0" value="0">
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
    $strRet = '<table><tr>';
    $sep = 1;
    $stationNodeList = $xmlDoc->getElementsByTagName('station');
    foreach ($stationNodeList as $station) {
        $idValue = $station->getAttribute('id');
        $stationName = $station->getElementsByTagName('name')->item(0)->nodeValue;
//        echo $idValue . '->' . $stationName, PHP_EOL;
        $strRet .= "<td><label for=\"$idValue\">$stationName</label><input id=\"$idValue\" type=\"number\" min=\"0\" value=\"0\"></td>";
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