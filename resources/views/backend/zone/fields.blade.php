@use('app\Helpers\Helpers')
@php
    $payment_methods = Helpers::getPaymentMethodConfigs();

@endphp

    <div class="form-group row">
        <label class="col-md-2" for="name">{{ __('static.language.languages') }}</label>
        <div class="col-md-10">
            <ul class="language-list">
                @forelse (\App\Helpers\Helpers::getLanguages() as $lang)
                    @if(isset($zone))
                        <li>
                            <a href="{{ route('backend.zone.edit', ['zone' => $zone->id, 'locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                        </li>
                    @else
                    <a href="{{ route('backend.zone.create', ['locale' => $lang->locale]) }}"
                                class="language-switcher {{ request('locale') === $lang->locale ? 'active' : '' }}"
                                target="_blank"><img src="{{ @$lang?->flag ?? asset('admin/images/No-image-found.jpg') }}"
                                    alt=""> {{ @$lang?->name }} ({{ @$lang?->locale }})<i
                                    data-feather="arrow-up-right"></i></a>
                    @endif
                @empty
                    <li>
                        <a href="{{ route('backend.zone.edit', ['zone' => $zone->id, 'locale' => Session::get('locale', 'en')]) }}"
                            class="language-switcher active" target="blank"><img
                                src="{{ asset('admin/images/flags/LR.png') }}" alt="">English<i
                                data-feather="arrow-up-right"></i></a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>


<input type="hidden" name="locale" value="{{ request('locale') }}">
<div class="form-group row">
    <label class="col-md-2" for="name">{{ __('static.zone.name') }}
        ({{ request('locale', app()->getLocale()) }})<span> *</span></label>
    <div class="col-md-10 input-copy-box">
        <input class="form-control" type="text" id="name" name="name"
            placeholder="{{ __('static.zone.enter_name') }} ({{ request('locale', app()->getLocale()) }})"
            value="{{ isset($zone->name) ? $zone->getTranslation('name', request('locale', app()->getLocale())) : old('name') }}">
        @error('name')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        <!-- Copy Icon -->
        <span class="input-copy-icon" data-tooltip="Copy">
            <i data-feather="copy"></i>
        </span>
    </div>
</div>

<div class="form-group row">
    <label for="country"
        class="col-md-2">{{ __('static.settings.currency') }}<span>
            *</span></label>
    <div class="col-md-10 error-div select-dropdown">
        <select class="select-2 form-control select-country"
            id="currency_id" name="currency_id"
            data-placeholder="{{ __('static.settings.select_currency') }}">
            <option class="select-placeholder" value=""></option>
            @forelse ($currencies as $key => $option)
                <option class="option" value={{ $key }}
                    @if ($zone?->currency_id ?? old('currency_id')) @if ($key == $zone?->currency_id) selected @endif
                    @endif>{{ $option }}</option>
                @empty
                    <option value="" disabled></option>
                @endforelse
        </select>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="payment_methods">{{ __('static.zone.payment_methods') }}<span> *</span> </label>
    <div class="col-md-10 error-div select-dropdown">
        <select id="blog_payment_methods" class="select-2 form-control" id="payment_methods[]" search="true" name="payment_methods[]"
            data-placeholder="{{ __('static.zone.select_payment_methods') }}" multiple>
            <option></option>
            @foreach ($payment_methods as $key => $value)
                <option value="{{ $value['slug'] }}"
                    {{ (is_array(old('payment_methods')) && in_array($key, old('payment_methods'))) || (isset($zone?->payment_methods) && in_array($value['slug'], $zone?->payment_methods)) ? 'selected' : '' }}>
                    {{ $value['name'] }}</option>
            @endforeach
        </select>
        @error('payment_methods.*')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="">{{ __('static.zone.place_points') }}<span> *</span></label>
    <div class="col-md-10">
        <input class="form-control" type="text" id="place_points" name="place_points"
            placeholder="{{ __('static.zone.select_place_points') }}"
            value="{{ isset($zone->locations) ? json_encode($zone->locations, true) : old('place_points') }}" readonly>
        @error('place_points')
            <span class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="search-box">{{ __('static.zone.search_location') }}</label>
    <div class="col-md-10">
        <input id="search-box" class="form-control" type="text"
            placeholder="{{ __('static.zone.search_locations') }}">
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.zone.map') }}</label>
    <div class="col-md-10">
        <div class="map-warper dark-support rounded overflow-hidden">
            <div class="map-container" id="map-container"></div>
        </div>
        <div id="coords"></div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2" for="role">{{ __('static.status') }}</label>
    <div class="col-md-10">
        <div class="editor-space">
            <label class="switch">
                @if (isset($zone))
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        {{ $zone->status ? 'checked' : '' }}>
                @else
                    <input class="form-control" type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" id="" value="1"
                        checked>
                @endif
                <span class="switch-state"></span>
            </label>
        </div>
    </div>
</div>

@push('js')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_map_api_key') }}&libraries=places,geometry,drawing&callback=initMap">
    </script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $("#zoneForm").validate({
                    ignore: [],
                    rules: {
                        "name": "required",
                        "place_points": "required",
                    }
                });
                
                $('#submitBtn').click(function(e) {
                    e.preventDefault();

                    if ($("#zoneForm").valid()) {
                        $("#zoneForm").submit();
                    }
                });

                let mapInstance, shapeManager, currentShape = null;
                let existingPolygon = @json(isset($zone->locations) ? $zone->locations : null);

                function initMap() {
                    setupMap();
                    setupDrawingManager();
                    setupGeolocation();
                    loadExistingPolygon();
                    searchBox();
                }

                function setupMap() {
                    const startLocation = {
                        lat: 21.20764938296402,
                        lng: 72.77381805168456
                    };
                    const mapOptions = {
                        zoom: 13,
                        center: startLocation,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    };
                    mapInstance = new google.maps.Map($('#map-container')[0], mapOptions);
                }

                function setupDrawingManager() {
                    shapeManager = new google.maps.drawing.DrawingManager({
                        drawingMode: google.maps.drawing.OverlayType.POLYGON,
                        drawingControl: true,
                        drawingControlOptions: {
                            position: google.maps.ControlPosition.TOP_CENTER,
                            drawingModes: [google.maps.drawing.OverlayType.POLYGON]
                        },
                        polygonOptions: {
                            editable: true
                        }
                    });
                    shapeManager.setMap(mapInstance);
                    google.maps.event.addListener(shapeManager, "overlaycomplete", handleOverlayComplete);
                }

                function setupGeolocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(centerMapOnUser);
                    }
                }

                function centerMapOnUser(position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    mapInstance.setCenter(userLocation);
                }

                function handleOverlayComplete(event) {
                    if (currentShape) {
                        currentShape.setMap(null);
                    }
                    currentShape = event.overlay;
                    currentShape.type = event.type;
                    const vertices = currentShape.getPath().getArray();
                    const coordinatesArray = vertices.map(vertex => {
                        return {
                            lat: vertex.lat(),
                            lng: vertex.lng()
                        };
                    });

                    // Ensure the polygon is closed
                    if (coordinatesArray[0].lat !== coordinatesArray[coordinatesArray.length - 1].lat ||
                        coordinatesArray[0].lng !== coordinatesArray[coordinatesArray.length - 1].lng) {
                        coordinatesArray.push(coordinatesArray[0]);
                    }

                    $('#place_points').val(JSON.stringify(coordinatesArray));
                }

                function loadExistingPolygon() {
                    if (existingPolygon) {
                        const coordinates = existingPolygon.map(coord => new google.maps.LatLng(coord.lat, coord
                            .lng));
                        currentShape = new google.maps.Polygon({
                            paths: coordinates,
                            editable: true,
                            map: mapInstance
                        });
                        mapInstance.fitBounds(getPolygonBounds(currentShape));
                    }
                }

                function getPolygonBounds(polygon) {
                    const bounds = new google.maps.LatLngBounds();
                    polygon.getPath().forEach(function(vertex) {
                        bounds.extend(vertex);
                    });
                    return bounds;
                }

                function searchBox() {
                    var input = document.getElementById('search-box');
                    var searchBox = new google.maps.places.SearchBox(input);

                    mapInstance.addListener('bounds_changed', function() {
                        searchBox.setBounds(mapInstance.getBounds());
                    });

                    searchBox.addListener('places_changed', function() {
                        var places = searchBox.getPlaces();
                        if (places.length == 0) {
                            return;
                        }

                        var bounds = new google.maps.LatLngBounds();
                        places.forEach(function(place) {
                            if (!place.geometry) {
                                return;
                            }

                            if (place.geometry.viewport) {
                                bounds.union(place.geometry.viewport);
                            } else {
                                bounds.extend(place.geometry.location);
                            }
                        });

                        mapInstance.fitBounds(bounds);
                    });
                }

                // Initialize map
                initMap();
            });
        })(jQuery);
    </script>
@endpush
