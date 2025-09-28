@push('js')
<script async src="https://maps.googleapis.com/maps/api/js?key={{$googleMapKey}}&libraries=geometry&callback=initMap" defer></script>
<script>
    let map;
    let markers = [];
    let allServicemen = @json($servicemen);
    let infoWindow; 

    function initMap() {
        infoWindow = new google.maps.InfoWindow();
        const center = { lat: 21.224314516817937, lng: 72.83040983841619 };

        map = new google.maps.Map(document.getElementById('map_canvas'), {
            zoom: 12,
            center: center
        });

        function generateInfoContent(serviceman) {
            const rating = serviceman.review ? `${serviceman.review.toFixed(1)} / 5` : "UNRATED";
            return `<div style="display: flex; flex-direction: column; align-items: center; font-family: Arial, sans-serif;">
                        <img src="${serviceman.image}" alt="Serviceman Image" width="70" height="70" style="border-radius: 50%; margin-bottom: 10px;">
                        <h3 style="margin: 5px 0; font-size: 16px; color: #333;">${serviceman.name}</h3>
                        <p style="margin: 2px 0; font-size: 14px; color: #777;">Phone: <strong>${serviceman.phone}</strong></p>
                        <p style="margin: 2px 0; font-size: 14px; color: #777;">Email: <strong>${serviceman.email}</strong></p>
                        <p style="margin: 2px 0; font-size: 14px; color: #777;">Rating: <strong>${rating}</strong></p>
                    </div>`;
        }

        function addMarker(serviceman) {
            const marker = new google.maps.Marker({
                position: { lat: serviceman.lat, lng: serviceman.lng },
                map: map,
                title: serviceman.name,
                icon: {
                    url: serviceman.image,
                    scaledSize: new google.maps.Size(50, 50),
                }
            });
            markers.push(marker);

            const infoContent = generateInfoContent(serviceman);
            google.maps.event.addListener(marker, 'mouseover', function() {
                infoWindow.setContent(infoContent);
                infoWindow.open(map, marker);
            });

            google.maps.event.addListener(marker, 'mouseout', function() {
                infoWindow.close();
            });
        }

        function showAllServicemenMarkers() {
            markers.forEach(marker => marker.setMap(null));
            allServicemen.forEach(serviceman => {
                if (serviceman.lat && serviceman.lng) addMarker(serviceman);
            });
            map.setCenter(center);
            map.setZoom(12);
        }

        function fetchAndShowServiceman(servicemanId, $button) {
            $.ajax({
                url: '{{ route("backend.serviceman-cordinates.index", ":id") }}'.replace(':id', servicemanId),
                method: 'GET',
                success: function(response) {
                    if (response.lat && response.lng) {
                        addMarker(response);
                        map.setCenter({ lat: response.lat, lng: response.lng });
                        map.setZoom(15);
                    }
                    toggleButtonLoading($button, false);
                    $('#show-all-servicemen').show();
                },
                error: function() {
                    alert('Location not found');
                    toggleButtonLoading($button, false);
                }
            });
        }

        function toggleButtonLoading($button, isLoading) {
            $button.find('.spinner-border').toggleClass('d-none', !isLoading);
            $button.find('.btn-text').text(isLoading ? 'Loading...' : 'View Location');
            $button.prop('disabled', isLoading);
        }

        // Initial marker setup for all servicemen with locations
        allServicemen.forEach(serviceman => {
            if (serviceman.lat && serviceman.lng) addMarker(serviceman);
        });

        // Handle click event for "View Location" buttons
        $('button.view-location-btn').on('click', function() {
            const $button = $(this);
            const servicemanId = $button.data('serviceman-id');
            toggleButtonLoading($button, true);
            markers.forEach(marker => marker.setMap(null));
            fetchAndShowServiceman(servicemanId, $button);
        });

        $('#show-all-servicemen').on('click', function() {
            showAllServicemenMarkers();
            $(this).hide();
        });
    }
</script>
@endpush
