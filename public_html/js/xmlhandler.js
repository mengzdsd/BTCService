/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//function checkFolder(path) {
//    var xhttp = getXHttp();
//    try {
//        xhttp.open("GET", path, false);
//    } catch(err) {
//        return err.toString();
//    }
//    xhttp.send();
//    return xhttp.responseText.toString();
//}

function readData(xmldoc) {
    var lineId = xmldoc.getElementsByTagName("Id")[0].childNodes[0].nodeValue;
//    var lineId = xmldoc.getElementById('id').nodeValue;
    var status;
    if (xmldoc.getElementById('check')) {
        status = true;
    } else {
        status = false;
    }
    var checker;
    if (xmldoc.getElementById('checker')) {
        checker = xmldoc.getElementById('checker').nodeValue;
    } else {
        checker = null;
    }

    var arr = [lineId, status, checker];
    return arr;
}

function createTableHead() {
    var trNode = arguments[0].createElement('tr');
    for (var i = 1; i < arguments.length; i++) {
        var thNode = arguments[0].createElement('th');
        thNode.innerHTML = arguments[i];
        trNode.appendChild(thNode);
    }
    return trNode;
}

function createCheckMain(xmldoc, doc) {
    var tableNode = createStationTable(xmldoc,doc);

    // create p node <p>班次： xxxxxxxxxxxxx</p>
    var pNode1 = doc.createElement('p');
    pNode1.innerHTML = "班次：xxxxxxxxx";
    // create p node <p>请选择每个站的下站人数：</p>
    var pNode2 = doc.createElement('p');
    pNode2.innerHTML = "请选择每个站的下站人数：";
    // create button <button id="submitbtn" class="btn" onclick="submitData()">提 交</button>
    var buttonNode = doc.createElement('button');
    buttonNode.setAttribute("id","submitbtn");
    buttonNode.setAttribute("class","btn");
    buttonNode.setAttribute("onclick","submitData()");
    buttonNode.innerHTML = "提 交";
    // create div node <div id="main">
    var divMainNode = doc.createElement('div');
    divMainNode.setAttribute("id", "main");
    divMainNode.appendChild(pNode1);
    divMainNode.appendChild(pNode2);
    divMainNode.appendChild(tableNode);
    divMainNode.appendChild(buttonNode);

    return divMainNode;
} 

function createStationTable(xmldoc,doc) {
    var stationNodes = xmldoc.getElementsByTagName('station');
    // var containNode = doc.createElement('contian');
    var tdNodesArray = [];
    var lengthOfArray = 0;
    var numberOfStation = stationNodes.length;
    for (var i = 0; i < numberOfStation; i++) {
        // create lable node <label for="sx">虎门中心站</label>
        var forAttr = "st" + (i + 1).toString();
//        console.log(forAttr);
        var stationName = stationNodes.item(i).childNodes[0].nodeValue;
        var labelNode = doc.createElement('label');
        labelNode.setAttribute("for",forAttr);
        labelNode.innerHTML = stationName;
        // create input node <input id="sx" type="number"/>
        var inputNode = doc.createElement('input');
        inputNode.setAttribute("id",forAttr);
        inputNode.setAttribute("type","number");
        // create form node
        var formNode = doc.createElement('form');
        formNode.appendChild(labelNode);
        formNode.appendChild(inputNode);
        // create td node 
        var tdNode = doc.createElement('td');
        tdNode.appendChild(formNode);
        // create containNode
        //containNode.appendChild(tdNode);
        lengthOfArray = tdNodesArray.push(tdNode);
//        console.log(lengthOfArray);
    }
    
    var tableNode = doc.createElement('table');
    var numberOfRow;
    var mod = numberOfStation % 6;
    if (mod === 0 ) {
        numberOfRow = numberOfStation / 6;
    } else {
        numberOfRow = ((numberOfStation - mod) / 6) + 1;
    }
    console.log(numberOfRow);
    var line = 0;
  // var tdNodes = contianNode.getElementsByTagName('td');
    for (var i = 0; i < numberOfRow; i++) {
        var trNode = doc.createElement('tr');
        var j = line;
        for (j; j < (line + 6); j++) {
            if (j < numberOfStation) {
                // trNode.appendChild(tdNodes.item(j));
                trNode.appendChild(tdNodesArray.shift());
            } 
        }
        line = j;
        tableNode.appendChild(trNode);
    }
//    while (tdNodesArray.length !== 0) {
//        var trNode = doc.createElement('tr');
//        for (var i = 0; i < 6; i++) {
//            trNode.appendChild(tdNodesArray.shift());
//        }
//        tableNode.appendChild(trNode);
//    }
    
    return tableNode;
}
