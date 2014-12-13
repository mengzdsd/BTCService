<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html lang="zh">
    <head>
        <meta charset="UTF-8">
        <title><?php
            $classId = filter_input(INPUT_GET, 'id');
            echo $classId;
            ?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
        <div id="shiftId">
            <?php
            $classInfo = "<p>班次： " . $classId . "</p>";
            echo $classInfo;
            ?>
        </div>
        <?php
        $classInfoFile = realpath("../linedata/" . substr($classId, 9, 8) . "/$classId.xml");
        $classInfoDoc = new DOMDocument();
        $classInfoDoc->load($classInfoFile);
        $startStation = $classInfoDoc->getElementsByTagName('StartStation')->item(0)->nodeValue;
        $endStation = $classInfoDoc->getElementsByTagName('EndStation')->item(0)->nodeValue;
        $runDate = $classInfoDoc->getElementsByTagName('Date')->item(0)->nodeValue;
        $startTime = $classInfoDoc->getElementsByTagName('StartTime')->item(0)->nodeValue;
        $stopTime = $classInfoDoc->getElementsByTagName('StopTime')->item(0)->nodeValue;
        $sumTicketsCount = $classInfoDoc->getElementsByTagName('TicketCount')->item(0)->nodeValue;
        $totalAmount = floatval($classInfoDoc->getElementsByTagName('TicketAmount')->item(0)->nodeValue);
        $strResult = $classInfoDoc->getElementsByTagName('CheckResult')->item(0)->getAttribute('result');

        $busDriver = [];
        $busDriver['name'] = $classInfoDoc->getElementsByTagName('Driver')->item(0)->getElementsByTagName('Name')->item(0)->nodeValue;
        $busDriver['callnumber'] = $classInfoDoc->getElementsByTagName('Driver')->item(0)->getElementsByTagName('CallNumber')->item(0)->nodeValue;
        $ticketSeller = [];
        $ticketSeller['name'] = $classInfoDoc->getElementsByTagName('TicketSeller')->item(0)->getElementsByTagName('Name')->item(0)->nodeValue;
        $ticketSeller['callnumber'] = $classInfoDoc->getElementsByTagName('TicketSeller')->item(0)->getElementsByTagName('CallNumber')->item(0)->nodeValue;

        $ticketsNodeList = $classInfoDoc->getElementsByTagName('Ticket');
        $arrTickets = [];
        foreach ($ticketsNodeList as $ticketNode) {
            $upStation = $ticketNode->getElementsByTagName('UpStation')->item(0)->nodeValue;
            $downStation = $ticketNode->getElementsByTagName('DownStation')->item(0)->nodeValue;
            $ticketCount = $ticketNode->getElementsByTagName('Count')->item(0)->nodeValue;
            $ticketPrice = floatval($ticketNode->getElementsByTagName('Price')->item(0)->nodeValue);
            $ticketFee = $ticketPrice * $ticketCount;
            $stationKey = $upStation . '-' . $downStation;
            if (array_key_exists($stationKey, $arrTickets)) {
                $ticketCount = $arrTickets[$stationKey][0] + $ticketCount;
                $ticketFee = $arrTickets[$stationKey][1] + $ticketFee;
                $arrTickets[$stationKey] = [$ticketPrice, $ticketCount, $ticketFee];
            }
            $arrTickets[$stationKey] = [$ticketPrice, $ticketCount, $ticketFee];
        }

        $downStationNodeList = $classInfoDoc->getElementsByTagName('CountInfo')->item(0)->getElementsByTagName('Station');
        $arrStationCount = [];
        foreach ($downStationNodeList as $downStationNode) {
            $stationName = $downStationNode->getElementsByTagName('Name')->item(0)->nodeValue;
            $upCount = $downStationNode->getElementsByTagName('UpCount')->item(0)->nodeValue;
            $downCount = $downStationNode->getElementsByTagName('DownCount')->item(0)->nodeValue;
            $arrStationCount[$stationName] = array($upCount, $downCount);
        }
        

        $arrAbnormalUpCount = [];
        $arrAbnormalDownCount = [];
        if ($strResult === 'no') {
            $abnormalStationsNode = $classInfoDoc->getElementsByTagName('AbnormalStations')->item(0);
            $abnormalUpStationsNodeList = $abnormalStationsNode->getElementsByTagName('UpStation');
            if (!empty($abnormalUpStationsNodeList)) {
                foreach ($abnormalUpStationsNodeList as $abnormalStationNode) {
                    $stationName = $abnormalStationNode->getElementsByTagName('Name')->item(0)->nodeValue;
                    $moreCount = $abnormalStationNode->getElementsByTagName('MoreCount')->item(0)->nodeValue;
                    $arrAbnormalUpCount[$stationName] = $moreCount;
                }
            }
            $abnormalDownStationsNodeList = $abnormalStationsNode->getElementsByTagName('DownStation');
            if (!empty($abnormalDownStationsNodeList)) {
                foreach ($abnormalDownStationsNodeList as $abnormalStationNode) {
                    $stationName = $abnormalStationNode->getElementsByTagName('Name')->item(0)->nodeValue;
                    $lessCount = $abnormalStationNode->getElementsByTagName('LessCount')->item(0)->nodeValue;
                    $arrAbnormalDownCount[$stationName] = $lessCount;
                }
            }
        }
        ?>
        <div id="runTime">
            <?php
            $arDate = explode('-', $runDate);
            $pRunTime = "<p>运行时间： $arDate[0]年$arDate[1]月$arDate[2]日 $startTime - $stopTime</p>";
            echo $pRunTime;
            ?>
        </div>
        <div id="totalAmount">
            <?php
            $pAmount = "<p>售票总金额： " . number_format($totalAmount, 2) . " 元</p>";
            echo $pAmount;
            ?>
        </div>
        <div id="checkResult">
            <?php
            $pStr = "";
            if ($strResult === 'yes') {
                $pStr = "<p>审核结果： <span class=\"green-color\">正确</span></p>";
            } else {
                $pStr = "<p>审核结果： <span class=\"red-color\">有异常</span></p>";
            }
            echo $pStr;
            ?>
        </div>
        <div id="eeInfo">
            <table>
                <tr>
                    <th></th>
                    <th>姓名</th>
                    <th>电话</th>
                </tr>
                <tr>
                    <td>司机</td>
                    <td>
                        <?php
                        echo $ticketSeller['name'];
                        ?>
                    </td>
                    <td>
                        <?php
                        echo $ticketSeller['callnumber'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>售票员</td>
                    <td>
                        <?php
                        echo $busDriver['name'];
                        ?>
                    </td>
                    <td>
                        <?php
                        echo $busDriver['callnumber'];
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <div id="ticketInfo">
            <p>售票信息：</p>
            <table>
                <tr>
                    <th>区间</th>
                    <th>单价</th>
                    <th>票数</th>
                    <th>金额</th>
                </tr>
                <?php
                foreach ($arrTickets as $key => $value) {
                    $strPrice = number_format($value[0], 2);
                    $strAmount = number_format($value[2], 2);
                    $strContent = "<tr><td>$key</td><td>$strPrice</td><td>$value[1]</td><td>$strAmount</td></tr>";
                    echo $strContent;
                }
                ?>
            </table>
        </div>
        <div id="updownInfo">
            <p>站点上下站人数： （来自监控视频的审核）</p>
            <table>
                <tr>
                    <th>站名</th>
                    <th>上站人数</th>
                    <th>下站人数</th>
                </tr>
                <?php
                foreach ($arrStationCount as $key => $value) {
                    $strContent = "<tr><td>$key</td><td>$value[0]</td><td>$value[1]</td></tr>";
                    echo $strContent;
                }
                ?>
            </table>
        </div>
        <div id="abnormalInfo">
            <?php
            if ($strResult === 'no') {
                if (!empty($arrAbnormalUpCount)) {
                    $pStr = "<p>下面是异常的上站人数：</p>";
                    $tableStr = "<table><tr><th>站名</th><th>无票人数</th></tr>";
                    foreach ($arrAbnormalUpCount as $key => $value) {
                        $tableStr .= "<tr><td>$key</td><td>$value</td></tr>";
                    }
                    $tableStr .= "</table>";
                    echo $pStr;
                    echo $tableStr;
                }

                if (!empty($arrAbnormalDownCount)) {
                    $pStr = "<p>下面是异常的下站人数：</p>";
                    $tableStr = "<table><tr><th>站名</th><th>过站人数</th></tr>";
                    foreach ($arrAbnormalDownCount as $key => $value) {
                        $tableStr .= "<tr><td>$key</td><td>$value</td></tr>";
                    }
                    $tableStr .= "</table>";
                    echo $pStr;
                    echo $tableStr;
                }
            }
            ?>
        </div>
    </body>
</html>