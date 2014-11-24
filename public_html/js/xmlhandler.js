/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getXHttp() {
    if (window.ActiveXObject) {
        xhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } else {
        xhttp = new XMLHttpRequest();
    }
    
    return xhttp;
}

function loadXMLDoc(filename) {
    var xhttp = getXHttp();
    
    xhttp.open("GET", filename, false);
    try {
        xhttp.responseType = "msxml-document";
    } catch(err) {} // helping IE11
    xhttp.send();
    return xhttp.responseXML;
}

function checkFolder(path) {
    var xhttp = getXHttp();
    try {
        xhttp.open("GET", path, false);
    } catch(err) {
        return err.toString();
    }
    xhttp.send();
    return xhttp.responseText.toString();
}