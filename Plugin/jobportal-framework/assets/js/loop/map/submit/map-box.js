jQuery(document).ready(function () {

    var api_key = jobportal_map_box_submit_vars.api_key,
        lng = jobportal_map_box_submit_vars.lng,
        lat = jobportal_map_box_submit_vars.lat,
        map_zoom = jobportal_map_box_submit_vars.map_zoom,
        map_style = jobportal_map_box_submit_vars.map_style,
        map_marker = jobportal_map_box_submit_vars.map_marker,
        form_submit = jobportal_map_box_submit_vars.form_submit;

    mapboxgl.accessToken = api_key;
    var map_location = new mapboxgl.Map({
        container: 'mapbox_location',
        style: 'mapbox://styles/mapbox/' + map_style,
        zoom: map_zoom,
        center: [lng,lat],
     });

    var geocoder = new MapboxGeocoder({
        accessToken: mapboxgl.accessToken,
        mapboxgl: mapboxgl,
    });

    var stores = {
        "id": "locations",
        "type": "FeatureCollection",
        "features": [{
            "type": "Feature",
            "geometry": {
                "type": "Point",
                "coordinates": [lng, lat],
            },
            "properties": {
                "iconSize": [48, 48],
                "icon": map_marker,
            }
        }]
    };

    jQuery(form_submit).find('#search-location').each(function () {
        var val = jQuery(this).attr('placeholder');
        jQuery(this).attr('placeholder', '');
        jQuery('.mapboxgl-ctrl-geocoder--input').attr('placeholder', val);
        jQuery('.mapboxgl-ctrl-geocoder--input').attr('autocomplete', 'off');

        var value = jQuery(this).val();
        if (val != '') {
            jQuery('.mapboxgl-ctrl-geocoder--input').val(value);
        }
    });

    jQuery('.mapboxgl-ctrl-geocoder--input').on('change', function () {
        var val = jQuery(this).val();
        if (val != '') {
            jQuery('#search-location-error').hide();
            jQuery('input[name="jobportal_map_address"]').attr('value',val);
        }
    });

    jQuery(form_submit).find('input[name="jobportal_longtitude"]').on('change', function () {
        var lng = jQuery(this).val();
        var lat = jQuery(form_submit).find('input[name="jobportal_latitude"]').val();
        searchCoordinates(lng,lat);
    });

    jQuery(form_submit).find('input[name="jobportal_latitude"]').on('change', function () {

        var lat = jQuery(this).val();
        var lng = jQuery(form_submit).find('input[name="jobportal_longtitude"]').val();
        searchCoordinates(lng,lat);
    });

    stores.features.forEach(function (store, i) {
        store.properties.id = i;
    });

    map_location.on('load', function (e) {
        addMarkers();
    });

    document.getElementById('geocoder').appendChild(
        geocoder.onAdd(map_location)
    );


    geocoder.on('result', function (ev) {
        var lat = ev.result.geometry['coordinates'][1];
        var lng = ev.result.geometry['coordinates'][0];
        var locationValue = lat + ',' + lng;

        jQuery(form_submit).find('input[name="jobportal_map_location"]').val(locationValue);
        jQuery(form_submit).find('input[name="jobportal_map_address"]').val(ev.result.place_name);
        jQuery('#search-location').val(ev.result.place_name).trigger('change');
        jQuery('.mapboxgl-marker:last-child').remove();

        if (jQuery(form_submit).find('input[name="jobportal_map_location"]').val() !== '') {
            jQuery(form_submit).find('input[name="jobportal_longtitude"]').val(lng);
            jQuery(form_submit).find('input[name="jobportal_latitude"]').val(lat);
        }

        newMarkers(lng, lat, ev.result.geometry['coordinates'])
    });

    addlocation(map_location);

    //function
    function searchCoordinates(lng,lat) {

        var location = jQuery(form_submit).find('input[name="jobportal_map_location"]');
        var address = jQuery(form_submit).find('input[name="jobportal_map_address"]');
        var url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?access_token=' + api_key + '';

        fetch(url).then(response => response.json()).then(data => {
            var result = data.features[0];

        location.val(lng + ',' + lat);
        if(result.place_name){
            geocoder.setInput(result.place_name)._geocode(result.place_name);
            address.attr('value',result.place_name);
        }
        newMarkers(lng,lat,result.center);
        });
    }

    function newMarkers(lng,lnt,coordinates) {
        stores.features.forEach(function (marker) {
            var el = document.createElement('div');
            el.id = "marker-" + marker.properties.id;
            el.className = 'marker';
            el.style.backgroundImage = 'url(' + marker.properties.icon + ')';
            el.style.width = marker.properties.iconSize[0] + 'px';
            el.style.height = marker.properties.iconSize[1] + 'px';

            var newMap = new mapboxgl.Map({
                container: 'mapbox_location',
                style: 'mapbox://styles/mapbox/' + map_style,
                zoom: map_zoom,
                center: [lng,lnt],
            });

            new mapboxgl.Marker(el, {
                offset: [0, -50 / 2]
            }).setLngLat(coordinates).addTo(newMap);
        });
    };

    function addlocation(map) {

        var my_location = {};
        var my_lat = '';
        var my_lng = '';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                my_lat = position.coords.latitude;
                my_lng = position.coords.longitude;

                my_location = {
                    lat: parseFloat(my_lat),
                    lng: parseFloat(my_lng)
                };

            }, function () {

                //handleLocationError(true, infowindow, map.getCenter());
            });
        } else {
            // Browser doesn't support Geolocation
            handleLocationError(false, infowindow, map.getCenter());
        }

        function CenterControl(controlDiv, map) {

            // Set CSS for the control border.
            const controlUI = document.createElement("div");
            controlUI.style.backgroundColor = "#fff";
            controlUI.style.border = "2px solid #fff";
            controlUI.style.borderRadius = "3px";
            controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
            controlUI.style.cursor = "pointer";
            controlUI.style.width = "40px";
            controlUI.style.height = "40px";
            controlUI.style.margin = "10px";
            controlUI.style.textAlign = "center";
            controlUI.title = "My location";
            controlDiv.appendChild(controlUI);

            // Set CSS for the control interior.
            const controlText = document.createElement("div");
            controlText.style.fontSize = "18px";
            controlText.style.lineHeight = "37px";
            controlText.style.paddingLeft = "5px";
            controlText.style.paddingRight = "5px";
            controlText.innerHTML = "<i class='fas fa-location'></i>";
            controlUI.appendChild(controlText);

            // Setup the click event listeners: simply set the map to Chicago.
            controlUI.addEventListener('click', () => {jQuery(form_submit).
            find('input[name="jobportal_map_location"]').val(parseFloat(my_lat) + ',' + parseFloat(my_lng));
            });

            jQuery('.my-location').on('click', function (e) {
                e.preventDefault();

                marker_location.setLngLat([parseFloat(my_lng), parseFloat(my_lat)]).addTo(map_location);

                map_location.flyTo({
                    center: [
                        parseFloat(my_lng),
                        parseFloat(my_lat)
                    ],
                    essential: true
                });

                jQuery(form_submit).find('input[name="jobportal_map_location"]').val(parseFloat(my_lat) + ',' + parseFloat(my_lng));
            });
        }

        const centerControlDiv = document.createElement("div");
        CenterControl(centerControlDiv, map);

        centerControlDiv.index = 1;

    };

    function addMarkers() {
        map_location.addLayer({
            "id": "locations",
            "type": "symbol",
            /* Add a GeoJSON source containing jobs coordinates and information. */
            "source": {
                "type": "geojson",
                "data": stores
            },
            "layout": {
                'icon-image': 'grocery-15',
                "icon-allow-overlap": true,
            }
        });

        stores.features.forEach(function (marker) {
            var el = document.createElement('div');
            el.id = "marker-" + marker.properties.id;
            el.className = 'marker';
            el.style.backgroundImage = 'url(' + marker.properties.icon + ')';
            el.style.width = marker.properties.iconSize[0] + 'px';
            el.style.height = marker.properties.iconSize[1] + 'px';

            new mapboxgl.Marker(el, {
                offset: [0, -50 / 2]
            }).setLngLat(marker.geometry.coordinates).addTo(map_location);

            var location = jQuery(form_submit).find('input[name="jobportal_map_location"]');
            var address = jQuery(form_submit).find('input[name="jobportal_map_address"]');
            var lng  = marker.geometry.coordinates[0];
            var lat  = marker.geometry.coordinates[1];
            var url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?access_token=' + api_key + '';

            fetch(url).then(response => response.json()).then(data => {
                var result = data.features[0];

                location.val(lat + ',' + lng);
                if(result){
                    geocoder.setInput(result.place_name);
                    address.attr('value',result.place_name);
                    newMarkers(lng,lat,result.center);
                }
            });


            el.addEventListener('click', function (e) {
                /* Highlight listing in sidebar */
                var activeItem = document.getElementsByClassName('active');
                e.stopPropagation();
                if (activeItem[0]) {
                    activeItem[0].classList.remove('active');
                }
            });
        });
    }
});
