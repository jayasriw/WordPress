jQuery(document).ready(function () {
    var api_key = jobportal_google_map_single_vars.api_key,
        lng = jobportal_google_map_single_vars.lng,
        lat = jobportal_google_map_single_vars.lat,
        map_zoom = parseInt(jobportal_google_map_single_vars.map_zoom),
        map_type = jobportal_google_map_single_vars.map_type,
        map_style = jobportal_google_map_single_vars.map_style,
        map_marker = jobportal_google_map_single_vars.map_marker;


    var element = jQuery('#google_map');
    if (element != null) {
        var styles;
        var bounds = new google.maps.LatLngBounds();
        var silver = [{
            "featureType": "landscape",
            "elementType": "labels",
            "stylers": [{
                "visibility": "off"
            }]
        },
            {
                "featureType": "transit",
                "elementType": "labels",
                "stylers": [{
                    "visibility": "off"
                }]
            },
            {
                "featureType": "poi",
                "elementType": "labels",
                "stylers": [{
                    "visibility": "off"
                }]
            },
            {
                "featureType": "water",
                "elementType": "labels",
                "stylers": [{
                    "visibility": "off"
                }]
            },
            {
                "featureType": "road",
                "elementType": "labels.icon",
                "stylers": [{
                    "visibility": "off"
                }]
            },
            {
                "stylers": [{
                    "hue": "#00aaff"
                },
                    {
                        "saturation": -100
                    },
                    {
                        "gamma": 2.15
                    },
                    {
                        "lightness": 12
                    }
                ]
            },
            {
                "featureType": "road",
                "elementType": "labels.text.fill",
                "stylers": [{
                    "visibility": "on"
                },
                    {
                        "lightness": 24
                    }
                ]
            },
            {
                "featureType": "road",
                "elementType": "geometry",
                "stylers": [{
                    "lightness": 57
                }]
            }
        ];

        styles = silver;
        if (map_style) {
            styles = JSON.parse(map_style);
        }

        if (lat !== '' && lng !== '') {
            var marker;
            var position = new google.maps.LatLng(lat, lng);
            var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
            var isDraggable = w > 1024;
            var mapOptions = {
                mapTypeId: map_type,
                center: position,
                draggable: true,
                scrollwheel: true,
                styles: styles,
                mapTypeControl: false,
                streetViewControl: true,
                rotateControl: false,
                zoomControl: true,
                fullscreenControl: true,
            };
            var map = new google.maps.Map(document.getElementById("google_map"), mapOptions);
            bounds.extend(position);

            marker_size = new google.maps.Size(48, 48);
            var marker_icon = {
                url: map_marker,
                size: marker_size,
                scaledSize: new google.maps.Size(48, 48),
            };

            marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: marker_icon,
                animation: google.maps.Animation.DROP,
                title: "marker_single"
            });

            google.maps.event.addListenerOnce(marker, 'map_changed', function() {
                setTimeout(function() {
                    var markerDiv = $('div[title="marker_single"]');
                    if (markerDiv.length) {
                        markerDiv.addClass('marker');
                    }
                }, 100);
            });

            var infowindow = new google.maps.InfoWindow({
                maxWidth: 370,
            });

            function jobportal_my_location(map) {

                var my_location = {};
                var my_lat = '';
                var my_lng = '';

                var isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                if (!isSecureContext) {
                    jQuery('.my-location').hide();
                    return;
                }

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

                    }, function (error) {
                        var errorMessage = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Please allow location access to use this feature.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Unable to determine current location.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Location request timed out.';
                                break;
                            default:
                                errorMessage = 'Unable to determine location. Please try again.';
                                break;
                        }

                        jQuery('.my-location').hide();
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    });
                } else {
                    jQuery('.my-location').hide();
                }

                function CenterControl(controlDiv, map) {
                    const controlUI = document.createElement("div");
                    controlUI.style.backgroundColor = "#fff";
                    controlUI.style.border = "2px solid #fff";
                    controlUI.style.borderRadius = "3px";
                    controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
                    controlUI.style.cursor = "pointer";
                    controlUI.style.width = "48px";
                    controlUI.style.height = "48px";
                    controlUI.style.margin = "10px";
                    controlUI.style.textAlign = "center";
                    controlUI.title = "My Location";
                    controlDiv.appendChild(controlUI);

                    // Set CSS for the control interior.
                    const controlText = document.createElement("div");
                    controlText.style.fontSize = "18px";
                    controlText.style.lineHeight = "37px";
                    controlText.style.paddingLeft = "5px";
                    controlText.style.paddingRight = "5px";
                    controlText.innerHTML = "<i class='fas fa-location'></i>";
                    controlUI.appendChild(controlText);

                    controlUI.addEventListener('click', () => {
                        if (my_location.lat && my_location.lng) {
                            map.panTo(my_location);
                        } else {
                            alert('Determining your location...');
                        }
                    });

                    jQuery('.my-location').on('click', function (e) {
                        e.preventDefault();
                        if (my_location.lat && my_location.lng) {
                            map.panTo(my_location);
                        } else {
                            alert('Determining your location...');
                        }
                    });
                }

                const centerControlDiv = document.createElement("div");
                CenterControl(centerControlDiv, map);

                centerControlDiv.index = 1;
                map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(centerControlDiv);
            };
            jobportal_my_location(map);

            google.maps.event.addListener(marker, 'click', function () {
                infowindow.setContent('');
                infowindow.open(map, this);
            });

            map.fitBounds(bounds);
            var boundsListener = google.maps.event.addListener((map), 'idle', function (event) {
                this.setZoom(map_zoom);
                google.maps.event.removeListener(boundsListener);
            });
        } else {
            document.getElementById('google_map').style.height = 'auto';
        }
    }
});
