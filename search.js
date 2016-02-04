function search(str) {
  var xhttp;
  if (str.length == 0) { 
    document.getElementById("output").innerHTML = "";
    return;
  }
  // console.log("str: ",str);
  xhttp=new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      renderOutput(xhttp);
    }
  };
  xhttp.open("GET", "http://localhost/~andresvera/Alert-Logic/search.php?q="+str, true);
  xhttp.send();
}

function renderOutput(xhttp) {
  document.getElementById("output").innerHTML = xhttp.responseText;
}