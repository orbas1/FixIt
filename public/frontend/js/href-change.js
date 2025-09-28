// changes href on click
function changeBooking() {
    if (document.getElementById('optionone').checked) {
        document.getElementById('linkid').href = "/front-end/service-booking.html";
    }
    if (document.getElementById('optiontwo').checked) {
        document.getElementById('linkid').href = "/front-end/custom-service-booking.html";
    }
}