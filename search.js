function search() {
  var xhttp = new XMLHttpRequest();
  var input = document.getElementById("input").value;
  // console.log("input: ",input);
  if (!validateSearchInput(input)) { 
    return;
  }
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      renderOutput(xhttp);
    }
  };
  xhttp.open("GET", "http://localhost/~andresvera/Alert-Logic/search.php?input="+input, true);
  xhttp.send();
}

function validateSearchInput(str) {
  var pattern = /^([a-zA-Z]+ [a-zA-Z]+)/;
  if (!pattern.test(str)) { 
    alert("Please enter the first and last name of an actor.");
    return false;
  }
  return true;
}

function renderOutput(xhttp) {
  document.getElementById("output").innerHTML = xhttp.responseText;
}