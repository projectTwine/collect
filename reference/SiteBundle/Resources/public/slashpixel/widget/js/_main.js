window.onload = function() {

  var reviewSection = document.getElementById("reviewSection");
  var writeSection = document.getElementById("writeSection");

  //var triggerButton = document.getElementById("js-show-writesection");

  function showSection() {
    reviewSection.style.display="none";
    writeSection.style.display="block";
    return false;
  }

  //triggerButton.onclick = showSection;

};