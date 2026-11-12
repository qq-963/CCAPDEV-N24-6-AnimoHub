// organizations page
// the searching/filtering is done by php now (see OrgController::index),
// so this file only handles the little interactive bits.
// main.js is loaded after this file, so we wait for the page to finish loading first
document.addEventListener("DOMContentLoaded", function () {
  // start the carousel (makeCarousel is in main.js), but only if the page has one
  if (document.getElementById("carousel")) {
    makeCarousel("carousel");
  }

  // send the search form as soon as a dropdown changes, so you don't have to click Search
  var selects = document.querySelectorAll(".filters select");
  for (var i = 0; i < selects.length; i++) {
    selects[i].onchange = function () {
      this.form.submit();
    };
  }
});
