<script>
    const baseurl = "{{ asset('') }}";
</script>
<!-- Latest jquery -->
 <script src="{{ asset('frontend/js/jquery-3.6.0.min.js') }}"></script>
 <script src="{{ asset('frontend/js/jquery-ui.min.js') }}"></script>

 <!-- Bootstrap js -->
 <script src="{{ asset('frontend/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
 <script src="{{ asset('frontend/js/bootstrap/bootstrap-notify.min.js') }}"></script>
 <script src="{{ asset('frontend/js/bootstrap/popper.min.js') }}"></script>

 <!-- Iconsax js -->
 <script src="{{ asset('frontend/js/iconsax/iconsax.js') }}"></script>

 <!-- Feather icon js -->
 <script src="{{ asset('frontend/js/feather-icon/feather.min.js') }}"></script>
 <script src="{{ asset('frontend/js/feather-icon/feather-icon.js') }}"></script>

 <!-- Swiper-bundle js -->
 <script src="{{ asset('frontend/js/swiper-slider/swiper-bundle.min.js') }}"></script>
 <script src="{{ asset('frontend/js/swiper.js') }}"></script>

 <!-- Select2 Js-->
 <script src="{{ asset('frontend/js/select2.full.min.js') }}"></script>

 <!-- Script js -->
 <script src="{{ asset('frontend/js/aos.js') }}"></script>
 <script src="{{ asset('frontend/js/custom-aos.js') }}"></script>
 <script src="{{ asset('frontend/js/script.js') }}"></script>

 <!-- jquery-validation js -->
 <script src="{{ asset('frontend/js/jquery-validation/jquery-validate.js') }}"></script>
 <script src="{{ asset('frontend/js/jquery-validation/jquery-validate.min.js') }}"></script>
 <script src="{{ asset('frontend/js/jquery-validation/additional-methods.js') }}"></script>
 <script src="{{ asset('frontend/js/jquery-validation/additional-methods.min.js') }}"></script>

 <!-- toastr js -->
 <script src="{{ asset('frontend/js/toastr.min.js') }}"></script>

 <!-- csrf-token -->
 <meta name="csrf-token" content="{{ csrf_token() }}"/>

 <!-- Emoji js -->
 <script src="{{ asset('frontend/js/emoji-rate.js') }}"></script>

 <!-- Plus minus js -->
 <script src="{{  asset('frontend/js/plus-minus.js')}}"></script>

 <!-- Slick slider js -->
 <script src="{{  asset('frontend/js/slick.min.js')}}"></script>
 <script src="{{  asset('frontend/js/slick-slider.js')}}"></script>

 <!-- Lodaz js -->
 <script src="{{ asset('frontend/js/lodaz/lozad.min.js')}}"></script>

 @stack('js')

 <script>
     $('form').on('submit', function(e) {
        console.log("called");
         var $submitButton = $(this).find('.spinner-btn');

         // If the form contains a submit button with class 'spinner-btn'
         if ($submitButton.length > 0) {
             // Show the spinner and disable the button to prevent multiple submissions
             $submitButton.find('.spinner-border').show(); // Show the spinner
             $submitButton.prop('disabled', true); // Disable the submit button
         }
     });


     const observer = lozad('.lozad', {
         loaded: function(el) {
             $(el).attr('src', $(el).data('src'));
         }
     });
     observer.observe();

     // Location checking
     $.ajax({
         url: "{{ url('check-zone') }}",
         method: 'GET',
         success: function(data) {
             if (!data.zoneSet) {
                toastr.error("{{ __('frontend::static.location_error_message') }}");
             } else {
                 $('#overlay').hide();
                 $('#locationBox').removeClass('show');
                 if (data.location.length > 1) {
                     $('#location').html(`<i class="iconsax" icon-name="location"></i><span>${data.location}</span>`);
                 }
             }
         }
     });

     // Use current location button click
     $('#useCurrentLocationBtn').on('click', function() {
         $(this).find('.spinner-border')?.show();
         $(this).prop("disabled", true);
         $('#selectManuallyBtn').prop("disabled", true);
         getLocation();
     });

     // Close location box
     $('#locationCloseBtn').on('click', function() {
         resetLocationBtn();
     });

     // Get user's location
     function getLocation() {
         if (navigator.geolocation) {
             navigator.geolocation.getCurrentPosition(showPosition, showError);
         } else {
             $('#location').html("Geolocation is not supported by this browser.");
             resetLocationBtn();
         }
     }

     // Reset location button
     function resetLocationBtn() {
         $('#overlay').hide();
         $('#locationBox').removeClass('show');
         $('#useCurrentLocationBtn').find('.spinner-border')?.hide();
         $('#useCurrentLocationBtn').prop("disabled", false);
         $('#selectManuallyBtn').prop("disabled", false);
         $('#locationSelected').modal('hide');
     }

     // Show position from geolocation
     function showPosition(position) {
         const latitude = position.coords.latitude;
         const longitude = position.coords.longitude;

         $.ajax({
             url:  "{{ url('get-address') }}",
             method: 'GET',
             data: {
                 lat: latitude,
                 lng: longitude
             },
             success: function(data) {
                 if (data.status === "OK") {
                     const address = data.results[0].formatted_address;
                     $('#location').html(`<i class="iconsax" icon-name="location"></i><span>${address}</span>`);
                     location.reload();
                 } else {
                     $('#location').html("Unable to get address from location.");
                 }
                 resetLocationBtn();
             },
             error: function() {
                 $('#location').html("Geocoding failed.");
                 resetLocationBtn();
             }
         });
     }

     // Handle geolocation errors
     function showError(error) {
         let errorMessage;
         switch (error.code) {
             case error.PERMISSION_DENIED:
                 errorMessage = "User denied the request for Geolocation.";
                 break;
             case error.POSITION_UNAVAILABLE:
                 errorMessage = "Location information is unavailable.";
                 break;
             case error.TIMEOUT:
                 errorMessage = "The request to get user location timed out.";
                 break;
             case error.UNKNOWN_ERROR:
                 errorMessage = "An unknown error occurred.";
                 break;
         }
         $('#location').html(errorMessage);
         resetLocationBtn();
     }

     // Location search input handler
     $('#locationSearchInput').on('input', function() {
         const value = $(this).val();
         if (value.length > 2) {
             $('#locationSpinner').show();
             $.ajax({
                 url: "{{ url('google-autocomplete') }}",
                 method: 'GET',
                 data: {
                     location: value
                 },
                 success: function(data) {
                     $('#location-list').empty();
                     if (data.error) {
                         $('#location-list').append(`<li class="location"><span class="no-address">${data.error}</span></li>`);
                     } else {
                         const fragment = document.createDocumentFragment();
                         data.forEach(function(prediction) {
                             const li = document.createElement('li');
                             li.className = 'location';
                             li.setAttribute('data-place-id', prediction.place_id);
                             li.innerHTML = `<div><h5>${prediction.structured_formatting.main_text}</h5><h6>${prediction.structured_formatting.secondary_text}</h6></div>`;
                             fragment.appendChild(li);
                         });
                         $('#location-list').append(fragment);
                     }
                 },
                 error: function(xhr) {
                     $('#location-list').empty().append(`<li class="location"><span class="text-danger">An error occurred: ${xhr.status} - ${xhr.statusText}</span></li>`);
                 },
                 complete: function() {
                     $('#locationSpinner').hide();
                 }
             });
         } else {
             $('#location-list').empty();
         }
     });

     // State population
     $('.select-country').on('change', function() {
         const idCountry = $(this).val();
         populateStates(idCountry);
     });

     function populateStates(countryId) {
         $(".select-state").html('');
         $.ajax({
             url: "{{ url('/states') }}",
             type: "POST",
             data: {
                 country_id: countryId,
                 _token: '{{ csrf_token() }}'
             },
             dataType: 'json',
             success: function(result) {
                 $('.select-state').html('<option value="">Select State</option>');
                 result.states.forEach(function(value) {
                     $(".select-state").append(`<option value="${value.id}">${value.name}</option>`);
                 });
                 const defaultStateId = $(".select-state").data("default-state-id");
                 if (defaultStateId) {
                     $('.select-state').val(defaultStateId);
                 }
             }
         });
     }

     // Initialize select2
     $(".select-2").select2();

     // Country code select2
     $('.select-country-code').select2({
         templateResult: function(data) {
             if (!data.id) return data.text;
             return $(`<span><img src="${$(data.element).data('image')}" class="flag-img" />  ${data.text}</span>`);
         },
         templateSelection: function(selection) {
            return selection.id ? selection.text : '';
         }
     });

     $('.book-now-btn').click(function(event) {
         var $button = $(this);
         var spinner = $button.find('.spinner-border');
         var checkLoginUrl = $button.data('check-login-url');
         var loginUrl = $button.data('login-url');

         // Show spinner
         spinner.show();
         $button.prop('disabled', true); // Disable the button while checking login

         // Check if user is logged in by making an AJAX request
         $.ajax({
             url: checkLoginUrl,
             method: 'GET',
             success: function(response) {
                 if (response.logged_in) {
                     $('#bookServiceModal-' + $button.data('service-id')).modal('show');
                 } else {
                     window.location.href = loginUrl;
                 }
             },
             error: function() {
                 alert('An error occurred. Please try again later.');
             },
             complete: function() {
                 spinner.hide();
                 $button.prop('disabled', false);
             }
         });
     });

     // Select location
     $('#location-list').on('click', '.location', function() {
         const placeId = $(this).data('place-id');
         const address = $(this).find('h5').text();
         $('#location').html(`<i class="iconsax" icon-name="location"></i><span>${address}</span>`);
         $.ajax({
             url: "{{ url('get-coordinates') }}",
             method: 'GET',
             data: {
                 place_id: placeId
             },
             success: function(data) {
                 if (data.status === "OK") {
                     location.reload();
                 } else {
                     $('#location').html("Unable to retrieve coordinates.");
                     resetLocationBtn();
                 }
             },
             error: function() {
                 $('#location').html("Failed to retrieve coordinates.");
             }
         });
     });
 </script>
