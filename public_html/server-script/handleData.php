<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);

$lineName = filter_input(INPUT_POST, 'line');
$cId = filter_input(INPUT_POST, 'cid');
$strCounts = filter_input(INPUT_POST, 'count');
$checkerName = filter_input(INPUT_POST, 'checker');
//$lineName = "c58";
//$cId = "粤BAAAAA2014112102";
//$cId = "粤BAAAAA2014112101";
//$downCount = "0,0,0,2,0,0,0,0,0,0,0,0,0,0,0,4,0,0,0,0,0,0,0,0,0,0,0,7,0,0,0,0,0,0,8,0,0,0,10,0,0,0,0,0,0,1,0,0,0,0,0,0,12";
//$downCount = "0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,4";
//$checkerName = "Wang";

if ($lineName && $strCounts && $checkerName) {
//    echo "服务器已收到数据";
    // 路线站点数据文件
    $stationsInfoFile = realpath("../xml/line/" . $lineName . "-stations.xml");
    $dateStr = substr($cId, 9, 8) . '/';
    // 班次数据文件路径
    $classInfoFile = realpath("../linedata/" . substr($cId, 9, 8) . "/$cId.xml");
    $docStation = new DOMDocument();
    $docStation->load($stationsInfoFile);
    $stationNodeList = $docStation->getElementsByTagName('station');
    $arrStations = []; // used to record the station names
    foreach ($stationNodeList as $station) {
        $idValue = $station->getAttribute('id');
        $stationName = $station->getElementsByTagName('name')->item(0)->nodeValue;
        $arrStations[$idValue] = $stationName;
    }

    $docClassInfo = new DOMDocument();
    $docClassInfo->load($classInfoFile);
    $ticketsNodeList = $docClassInfo->getElementsByTagName('Ticket');
    $arrTUC = []; // used to recored the station name and number of up people should be
    $arrTDC = []; // uesd to recored the station name and number of down people should be
    foreach ($ticketsNodeList as $ticketNode) {
        $upStation = $ticketNode->getElementsByTagName('UpStation')->item(0)->nodeValue;
        $downStation = $ticketNode->getElementsByTagName('DownStation')->item(0)->nodeValue;
        $ticketCount = $ticketNode->getElementsByTagName('Count')->item(0)->nodeValue;
        $countDown = 0;
        $countUp = 0;
        if (array_key_exists($upStation, $arrTUC)) {
            $countUp = $arrTUC[$upStation] + intval($ticketCount);
        } else {
            $countUp = intval($ticketCount);
        }
        $arrTUC[$upStation] = $countUp;
        if (array_key_exists($downStation, $arrTDC)) {
            $countDown = $arrTDC[$downStation] + intval($ticketCount);
        } else {
            $countDown = intval($ticketCount);
        }
        $arrTDC[$downStation] = $countDown;
    }

    $arrCounts = explode('#', $strCounts);
    $arrCountInfos = [];   // used to record the stations and up/down number from vedio check
    // ("stationName" => ['upNumber', 'downNumber'])
    for ($i = 0; $i < count($arrCounts); $i++) {
        $sId = 's' . ($i + 1);
        $arrCountInfos[$arrStations[$sId]] = explode(',', $arrCounts[$i]);
    }

    // Calculate the date from the video and the tickets, and record the abnormal up and down counts
    $comResult = true;
    $expDownCount = 0;
    $arrAbnormalUpRecord = [];
    $arrAbnormalDownRecord = []; // used to record exception situation
    foreach ($arrCountInfos as $key => $value) {
        if (array_key_exists($key, $arrTUC)) {
            if (intval($value[0]) !== $arrTUC[$key]) {
                // have some one do not buy the ticket or the seller do not ticket
                $arrAbnormalUpRecord[$key] = intval($value[0]) - $arrTUC[$key]; 
                $comResult = false;
            }
        }

        if (array_key_exists($key, $arrTDC)) {
            $expDownCount += (intval($value[1]) - $arrTDC[$key]);
        } else {
            $expDownCount += intval($value[1]);
        }
        if ($expDownCount < 0) {
            // some one stay in bus longer then his ticket, record it here
            $arrAbnormalDownRecord[$key] = abs($expDownCount);
            $comResult = false;
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
    $nodeName = $docClassInfo->createElement('Name', $checkerName);
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
//    <CountInfo>
//        <Station>
//            <Name>南头检查站</Name>
//            <UpCount>0</UpCount>
//            <DownCount>4</DownCount>
//        </Station>
//    </DownCount>
    $newCountInfoNode = $docClassInfo->createElement('CountInfo');
    foreach ($arrCountInfos as $key => $value) {
        $strUpCount = strval($value[0]);
        $strDownCount = strval($value[1]);
//        error_log("Up: $strUpCount , Down: $strDownCount", 0);
        if ($strUpCount !== '0' || $strDownCount !== '0') {
            $nodeStation = $docClassInfo->createElement('Station');
            $nodeName = $docClassInfo->createElement('Name', $key);
            $nodeUpCount = $docClassInfo->createElement('UpCount', $strUpCount);
            $nodeDownCount = $docClassInfo->createElement('DownCount', $strDownCount);
            $nodeStation->appendChild($nodeName);
            $nodeStation->appendChild($nodeUpCount);
            $nodeStation->appendChild($nodeDownCount);
            $newCountInfoNode->appendChild($nodeStation);
        }
    }
    $oldCountInfoNodeList = $docClassInfo->getElementsByTagName('CountInfo');
    if ($oldCountInfoNodeList->length === 0) {
        $busLineNode->appendChild($newCountInfoNode);
    } else {
        $oldCountInfoNode = $oldCountInfoNodeList->item(0);
        $busLineNode->replaceChild($newCountInfoNode, $oldCountInfoNode);
    }

    // check result
    if ($comResult) {
        $newNodeCheckResult = $docClassInfo->createElement('CheckResult');
        $newNodeCheckResult->setAttribute('result', 'yes');
    } else {
        $newNodeCheckResult = $docClassInfo->createElement('CheckResult');
        $newNodeCheckResult->setAttribute('result', 'no');
        // create the abnormal sations
        $abnormalStationsNode = $docClassInfo->createElement('AbnormalStations');

        if (!empty($arrAbnormalUpRecord)) {
            foreach ($arrAbnormalUpRecord as $key => $value) {
                $nodeStation = $docClassInfo->createElement('UpStation');
                $nodeName = $docClassInfo->createElement('Name', $key);
                $nodeMoreCount = $docClassInfo->createElement('MoreCount', $value);
                $nodeStation->appendChild($nodeName);
                $nodeStation->appendChild($nodeMoreCount);
                $abnormalStationsNode->appendChild($nodeStation);
            }
        }

        if (!empty($arrAbnormalDownRecord)) {
            foreach ($arrAbnormalDownRecord as $key => $value) {
                $nodeStation = $docClassInfo->createElement('DownStation');
                $nodeName = $docClassInfo->createElement('Name', $key);
                $nodeLessCount = $docClassInfo->createElement('LessCount', $value);
                $nodeStation->appendChild($nodeName);
                $nodeStation->appendChild($nodeLessCount);
                $abnormalStationsNode->appendChild($nodeStation);
            }
        }
        $newNodeCheckResult->appendChild($abnormalStationsNode);
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
    
    echo "yes";
} else {
    echo "no";
}