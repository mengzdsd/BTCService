<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * 该脚本用来从linedata查找相应xml文件，从中获取需要的线路信息
 * xml文件在 linedata/<date>/<bus><date><sq>.xml
 * 接收日期[date]和车号[bus]作为输入参数
 * 返回含<tr> element的字符串
 * <tr>
  <th>班 次</th>
  <th>状 态</th>
  <th>核实人</th>
  <th>核 实</th>
  </tr>
  <tr>
  <td>粤BAAAAA2014112101</td>
  <td class="greenColor">已审核</td>
  <td>犹梦哲</td>
  <td><a href="">重审核</a></td>
  </tr>
  <tr>
  <td>粤BBBBBB2014112102</td>
  <td class="redColor">未审核</td>
  <td></td>
  <td><a href="">审核</a></td>
  </tr>
 */

error_reporting(E_ALL);

//$bus = "粤BAAAAA";
//$date = "20141121";
$date = filter_input(INPUT_GET, 'date');
$bus = filter_input(INPUT_GET, 'bus');
$line = filter_input(INPUT_GET, 'line');
$isResult = filter_input(INPUT_GET, 'result');
$isR = ($isResult === 'yes') ? true : false;
$dataDir = "../linedata/" . $date . '/';
// echo strlen($bus), PHP_EOL;
// search ../linedata/<date>/ diretory for bus
$searchDir = realpath($dataDir);
// echo $searchDir, PHP_EOL;
$oDir = dir($searchDir);
if ($oDir->handle) {
    $fileLists = array();

    while (false !== ($file = $oDir->read())) {
        if (!strncmp($file, $bus, 9)) {
//            echo "$file\n";
            $fileLists[] = $file;
        }
    }

    $oDir->close();

    if (empty($fileLists)) {
        echo "NULL";
        return false;
    }

    $xmlDoc = new DOMDocument();
    if ($isR) {
        $strRet = '<tr><th>班 次</th><th>状 态</th><th>审核人</th><th>审核结果</th><th>详细信息</th></tr>';
    } else {
        $strRet = '<tr><th>班 次</th><th>状 态</th><th>审核人</th><th>审核</th></tr>';
    }
    foreach ($fileLists as $filename) {
//        echo $filename, PHP_EOL;
        $filePath = realpath($dataDir . $filename);
        $xmlDoc->load($filePath);
        $idStr = $xmlDoc->getElementsByTagName('Id')->item(0)->nodeValue;
        $strRet .= "<tr><td>$idStr</td>";
        $checker = "";
        $checkerNodes = $xmlDoc->getElementsByTagName('Checker');
        if ($checkerNodes->length === 0) {
            if ($isR) {
                $strRet .= "<td class=\"red-color\">未审核</td><td></td><td></td><td></td></tr>";
            } else {
                $strRet .= "<td class=\"red-color\">未审核</td><td></td><td><a href=\"linecheck.html?id=$idStr&line=$line\">审核</a></td></tr>";
            }
        } else {
            $checker = $checkerNodes->item(0)->getElementsByTagName('Name')->item(0)->nodeValue;
            $checkResult = $xmlDoc->getElementsByTagName('CheckResult')->item(0)->getAttribute('result');

            if ($isR) {
                if ($checkResult === 'yes') {
                    $strRet .= "<td class=\"green-color\">已审核</td><td>$checker</td><td class=\"green-color\">正确</td><td><a href=\"linedetails/resultdetails.php?id=$idStr\" target=\"_blank\">查看</a></td></tr>";
                } else {
                    $strRet .= "<td class=\"green-color\">已审核</td><td>$checker</td><td class=\"red-color\">有问题</td><td><a href=\"linedetails/resultdetails.php?id=$idStr\" target=\"_blank\">查看</a></td></tr>";
                }
            } else {
                $strRet .= "<td class=\"green-color\">已审核</td><td>$checker</td><td><a href=\"linecheck.html?id=$idStr&line=$line\">重审核</a></td></tr>";
            }
        }
//        echo $checker, PHP_EOL;
    }
    echo $strRet;
    return true;
} else {
    echo "NULL";
    return false;
}