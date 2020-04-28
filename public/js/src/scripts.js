(function cCallout () {
  "use strict";

  var externalLinks = document.querySelectorAll(".external"),
    relativeDates = document.querySelectorAll(".timeago"),
    now = moment(), // eslint-disable-line no-undef
    currentDate,
    i;

  // Display relative dates via moment.js
  for (i = 0; i < relativeDates.length; i++) {
    currentDate = moment(relativeDates[i].getAttribute("title")); // eslint-disable-line no-undef
    relativeDates[i].innerHTML = currentDate.from(now);
  }

  // Open links in new tabs
  for (i = 0; i < externalLinks.length; i++) {
    externalLinks[i].addEventListener("click", newTab, false);
  }

  function newTab (event) {
    var thisLink = event.target.href;
    window.open(thisLink);
  }
}());
