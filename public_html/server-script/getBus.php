<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* 该脚本用来从xml文件中获取线路的班车牌照信息
 * 线路牌照信息的xml文件在 ../xml/line/<line>-bus.xml
 * 接收线路名作为输入参数
 * 返回含有所有班车车牌的option字符串， <option>粤BAAAAA</option><option>粤BBBBBB</option>
 */

/* xml 文件格式
 * <buses>
    <bus>粤BAAAAA</bus>
    <bus>粤BBBBBB</bus>
    <bus>粤BCCCCC</bus>
    <bus>粤BDDDDD</bus>
    <bus>粤BEEEEE</bus>
    <bus>粤BFFFFF</bus>
    <bus>粤BGGGGG</bus>
    <bus>粤BHHHHH</bus>
    <bus>粤BIIIII</bus>
</buses>
 */

error_reporting(E_ALL);

//$lineName = "c58";
$lineName = filter_input(INPUT_GET,'line');
$fileDir = "../xml/line/";
$busInfoFile = realpath($fileDir . $lineName . "-bus.xml");

// echo "$busInfoFile\n";

$xmlDoc = new DOMDocument();
$strRet = "";
if ($xmlDoc->load($busInfoFile)) {
//    echo "Open file Success.\n";
    $busNodeList = $xmlDoc->getElementsByTagName('bus');
    foreach ($busNodeList as $bus) {
//        echo $bus->nodeValue, PHP_EOL;
        $strRet .= '<option>';
        $strRet .= $bus->nodeValue;
        $strRet .= '</option>';
    }
    echo $strRet;
    return true;
} else {
    echo "NULL";
    return false;
}
