/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * Get bus license from server and insert to buslicense
 */
function getBus(line, xmlHttpR) {
    var busUrl = "server-script/getBus.php?" + "line=" + line;
    xmlHttpR.onreadystatechange = function () {
        if (xmlHttpR.readyState === 4 && xmlHttpR.status === 200) {
            if (xmlHttpR.responseText === "NULL") {
                alert('Can not get the bus info from server.');
            } else {
                document.getElementById('buslicense').innerHTML = xmlHttpR.responseText;
            }
        }
    };
    xmlHttpR.open('GET', busUrl, true);
    xmlHttpR.send();
}
