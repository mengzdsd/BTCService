<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);

$lineName = filter_input(INPUT_POST, 'line');
$cId = filter_input(INPUT_POST, 'cid');
$downCount = filter_input(INPUT_POST, 'downCount');
$checkerName = filter_input(INPUT_POST, 'checker');
//$lineName = "c58";
//$cId = "粤BAAAAA2014112102";
//$cId = "粤BAAAAA2014112101";
//$downCount = "0,0,0,2,0,0,0,0,0,0,0,0,0,0,0,4,0,0,0,0,0,0,0,0,0,0,0,7,0,0,0,0,0,0,8,0,0,0,10,0,0,0,0,0,0,1,0,0,0,0,0,0,12";
//$downCount = "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,4";
//$checkerName = "Wang";

if ($lineName && $downCount && $checkerName) {
    echo "服务器已收到数据";
    // 路线站点数据文件
    $stationsInfoFile = realpath("../xml/line/" . $lineName . "-stations.xml");
    $dateStr = substr($cId, 9, 8) . '/';
    // 班次数据文件路径
    $classInfoFile = realpath("../linedata/" . substr($cId, 9, 8) . "/$cId.xml");
    $docStation = new DOMDocument();
    $docStation->load($stationsInfoFile);
    $stationNodeList = $docStation->getElementsByTagName('station');
    $arrStations = [];
    foreach ($stationNodeList as $station) {
        $idValue = $station->getAttribute('id');
        $stationName = $station->getElementsByTagName('name')->item(0)->nodeValue;
        $arrStations[$idValue] = $stationName;
    }

    $docClassInfo = new DOMDocument();
    $docClassInfo->load($classInfoFile);
    $ticketsNodeList = $docClassInfo->getElementsByTagName('Ticket');
    $arrTicketDownCount = [];
    foreach ($ticketsNodeList as $ticketNode) {
        $downStation = $ticketNode->getElementsByTagName('DownStation')->item(0)->nodeValue;
        $ticketCount = $ticketNode->getElementsByTagName('Count')->item(0)->nodeValue;
        $count = 0;
        if (array_key_exists($downStation, $arrTicketDownCount)) {
            $count = $arrTicketDownCount[$downStation] + intval($ticketCount);
        } else {
            $count = intval($ticketCount);
        }
        $arrTicketDownCount[$downStation] = $count;
    }
//    print_r($arrTicketDownCount);

    $arrDownCount = preg_split("/,/", $downCount);
    $arrDC = [];
    for ($i = 0; $i < count($arrDownCount); $i++) {
        if ($arrDownCount[$i] != 0) {
            $sId = 's' . ($i + 1);
            $arrDC[$arrStations[$sId]] = intval($arrDownCount[$i]);
        }
    }
//    print_r($arrDC);
    //
    $comResult = false;
    if (count($arrTicketDownCount) === count($arrDC)) {
        foreach ($arrDC as $key => $value) {
            if (array_key_exists($key, $arrTicketDownCount)) {
                if ($arrTicketDownCount[$key] === $value) {
                    $comResult = true;
                } else {
                    $comResult = false;
                    break;
                }
            } else {
                $comResult = false;
                break;
            }
        }
    }

//    if ($comResult) {
//        echo "True", PHP_EOL;
//    } else {
//        echo "False", PHP_EOL;
//    }
    // 将提交的数据写入审核数据文件
    // 审核人
//    <Checker>
//        <Name>犹梦哲</Name>
//    </Checker>
//    echo $docClassInfo->saveXML();
//    $textNode = $docClassInfo->createTextNode($checkerName);
    $nodeName = $docClassInfo->createElement('Name',$checkerName);
//    $NameNode->appendChild($textNode);
    $newCheckerNode = $docClassInfo->createElement('Checker');
    $newCheckerNode->appendChild($nodeName);
    
    // root element
    $busLineNode = $docClassInfo->documentElement;
    
    $oldCheckerNodeList = $docClassInfo->getElementsByTagName('Checker');
    if ($oldCheckerNodeList->length === 0) {
        $busLineNode->appendChild($newCheckerNode);
    } else {
        $oldCheckerNode = $oldCheckerNodeList->item(0);
        $busLineNode->replaceChild($newCheckerNode, $oldCheckerNode);
    }

    // 下站人数信息
//    <DownCount>
//        <Staion>
//            <Name>南头检查站</Name>
//            <Count>4</Count>
//        </Staion>
//        <Staion>
//            <Name>沙井中心站</Name>
//            <Count>1</Count>
//        </Staion>
//        <Staion>
//            <Name>松岗医院</Name>
//            <Count>2</Count>
//        </Staion>
//        <Staion>
//            <Name>时代广场</Name>
//            <Count>1</Count>
//        </Staion>
//    </DownCount>
    $newDownCountNode = $docClassInfo->createElement('DownCount');
    foreach ($arrDC as $key => $value) {
        $nodeStation = $docClassInfo->createElement('Staion');
        $nodeName = $docClassInfo->createElement('Name', $key);
        $nodeCount = $docClassInfo->createElement('Count', $value);
        $nodeStation->appendChild($nodeName);
        $nodeStation->appendChild($nodeCount);
        $newDownCountNode->appendChild($nodeStation);
    }
    $oldDownCountNodeList = $docClassInfo->getElementsByTagName('DownCount');
    if ($oldDownCountNodeList->length === 0) {
        $busLineNode->appendChild($newDownCountNode);
    } else {
        $oldDownCountNode = $oldDownCountNodeList->item(0);
        $busLineNode->replaceChild($newDownCountNode, $oldDownCountNode);
    }
    
    // check result
    if ($comResult) {
        $newNodeCheckResult = $docClassInfo->createElement('CheckResult','yes');
    } else {
        $newNodeCheckResult = $docClassInfo->createElement('CheckResult','no');
    }
    $oldCheckResultNodeList = $docClassInfo->getElementsByTagName('CheckResult');
    if ($oldCheckResultNodeList->length === 0) {
        $busLineNode->appendChild($newNodeCheckResult);
    } else {
        $oldCheckResultNode = $oldCheckResultNodeList->item(0);
        $busLineNode->replaceChild($newNodeCheckResult, $oldCheckResultNode);
    }
    

//    echo PHP_EOL;
//    echo $docClassInfo->saveXML();
    $docClassInfo->save($classInfoFile);
} else {
    echo "服务器收到数据有错，请重新提交";
}