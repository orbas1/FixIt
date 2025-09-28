// Custom-flatpickr JS
(function () {
  // 1. Default Date
  flatpickr("#datetime-local", {
    minDate: new Date()
  });

  // 7.Range-date
  flatpickr("#range-date", {
    mode: "range",
  });

  // Time-picker

  //9.Time-picker
  flatpickr("#time-picker", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    minTime: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  });
  //By-default-  Inline Calender
  flatpickr("#inline-calender", {
    inline: true,
  });

})();
