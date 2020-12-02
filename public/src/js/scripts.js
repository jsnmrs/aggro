(function cTime () {
  "use strict";

  var relativeDates = document.querySelectorAll(".ago"),
    now = moment(), // eslint-disable-line no-undef
    currentDate,
    i;

  // Display relative dates via moment.js
  for (i = 0; i < relativeDates.length; i++) {
    currentDate = moment(relativeDates[i].dataset.date); // eslint-disable-line no-undef
    relativeDates[i].innerHTML = currentDate.from(now);
  }
}());
