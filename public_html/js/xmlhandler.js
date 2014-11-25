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