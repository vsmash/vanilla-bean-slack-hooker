;(function ($, window, document, undefined) {

    var pluginName = "exopiteSOFMultipleMap";

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this._name = pluginName;
        this.$element = $(element);
        this.markers = new Map();  // Store markers with field IDs as keys
        this.markerCounter = 0;  // Counter to number markers
        this.init();
    }

    Plugin.prototype = {

        init: function () {
            var plugin = this;
            plugin.$element.find('.exopitemap-control').each(function (index, el) {
                if ($(el).parents('.exopite-sof-cloneable__muster').length) return;
                if ($(el).hasClass('disabled')) return;

                console.info('Exopite Simple Options Framework Multiple Map', el);
                var thisinput = el.querySelector('input[type="hidden"]');  // Get the hidden input field
                plugin.input = thisinput;
                plugin.notice = el.querySelector('.mapnotice');  // Get the notice element
                var thismap = el.querySelector('.exopitemultiplemap');  // Use the correct class
                // find data-bounds value
                var searchBoxInput = el.querySelector('.map-search-box'); // Get the search box input field

                var boundsdata = $(thisinput).data('bounds');
                plugin.boundsdata = boundsdata;
                var mapOptions = {
                    mapId: '2df304dfb8ff94e0',
                    mapTypeId: google.maps.MapTypeId.SATELLITE,
                    center: new google.maps.LatLng(-33.79, 151.289),
                    zoom: 14
                };

                var map = new google.maps.Map(thismap, mapOptions);
                var searchBox = new google.maps.places.SearchBox(searchBoxInput); // Initialize the search box
                map.addListener('bounds_changed', function () {
                    searchBox.setBounds(map.getBounds());
                });

// Listen for the event fired when the user selects a prediction and retrieve more details for that place.
                searchBox.addListener('places_changed', function () {
                    var places = searchBox.getPlaces();

                    if (places.length === 0) {
                        return;
                    }


                    // For each place, get the icon, name and location.
                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function (place) {
                        if (!place.geometry) {
                            console.log("Returned place contains no geometry");
                            return;
                        }

                        // Create a marker for each place.
                        var location = place.geometry.location;

                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });

                if (boundsdata) {
                    var beaches;
                    var bounds = new google.maps.LatLngBounds();
                    // json parse the bounds data
                    var locations = boundsdata;
                    locations.forEach(function (location) {
                        var latLng = new google.maps.LatLng(location.lat, location.lng);
                        bounds.extend(latLng);
                        // create a custom marker for each location
                        const pin = new google.maps.marker.PinElement({
                            scale: location.type === 'forecast' ? 1.3 : 1.0,
                            background: location.type === 'forecast' ? "#FF6B6B" : "#00FFFF",
                            borderColor: location.type === 'forecast' ? "#FF0000" : "#000000",
                            glyph: ""  // Remove the label
                        });
                        
                        var marker = new google.maps.marker.AdvancedMarkerElement({
                            position: latLng,
                            map: map,
                            title: location.title || location.location,
                            content: pin.element
                        });
                        
                        // Store marker with field ID
                        if (location.ID) {
                            console.log('Storing marker for field ID:', location.ID, location);
                            plugin.markers.set(location.ID.toString(), {
                                marker: marker,
                                pin: pin,
                                type: location.type
                            });
                        }
                    });
                    map.fitBounds(bounds);
                    map.panToBounds(bounds);
                }

                // Add a click event listener to the map
                map.addListener('click', function (event) {
                    plugin.placeMarker(event.latLng, map, thisinput);  // Pass the hidden input field
                });

                // Check if the input has an initial value
                if (thisinput.value) {
                    try {
                        var initialCoords = JSON.parse(thisinput.value);  // Parse the JSON string
                        if (Array.isArray(initialCoords) && initialCoords.length > 0) {
                            initialCoords.forEach(function (coords, index) {
                                var initialLatLng = new google.maps.LatLng(coords.lat, coords.lng);
                                plugin.placeMarker(initialLatLng, map, thisinput, index + 1, true);  // Place the initial marker with its number
                            });
                            plugin.markerCounter = initialCoords.length; // Set markerCounter to the number of existing markers
                            var firstLatLng = new google.maps.LatLng(initialCoords[0].lat, initialCoords[0].lng);
                            map.setCenter(firstLatLng);  // Center the map on the first marker
                            map.setZoom(10);
                        }
                    } catch (e) {
                        console.error('Invalid initial coordinates:', e);
                    }
                }
            });

            plugin.$element.closest('.exopite-sof-wrapper').on('exopite-sof-field-group-item-added-after', function (event, $cloned) {
                // Add functionality for cloned items if needed
            });

            // Listen for changes to the default location through Exopite fields
            jQuery(document).on('change', '[data-depend-id="defaultlocation"]', function() {
                const newDefaultId = jQuery(this).val();
                console.log('Default location changed to:', newDefaultId);
                console.log('Current markers:', plugin.markers);
                
                // Reset all markers to their original style
                plugin.markers.forEach((markerInfo, id) => {
                    console.log('Updating marker:', id, 'isDefault:', id === newDefaultId);
                    const newPin = new google.maps.marker.PinElement({
                        scale: id === newDefaultId ? 1.3 : 1.0,
                        background: id === newDefaultId ? "#FF6B6B" : "#00FFFF",
                        borderColor: id === newDefaultId ? "#FF0000" : "#000000",
                        glyph: ""
                    });
                    
                    markerInfo.marker.content = newPin.element;
                });
            });

            // Listen for changes to the default location through Chosen
            jQuery(document).on('chosen:updated change', 'select#defaultlocation', function() {
                const newDefaultId = jQuery(this).val();
                console.log('Default location changed to:', newDefaultId);
                console.log('Current markers:', plugin.markers);
                
                // Reset all markers to their original style
                plugin.markers.forEach((markerInfo, id) => {
                    console.log('Updating marker:', id, 'isDefault:', id === newDefaultId);
                    const newPin = new google.maps.marker.PinElement({
                        scale: id === newDefaultId ? 1.3 : 1.0,
                        background: id === newDefaultId ? "#FF6B6B" : "#00FFFF",
                        borderColor: id === newDefaultId ? "#FF0000" : "#000000",
                        glyph: ""
                    });
                    
                    markerInfo.marker.content = newPin.element;
                });
            });
        },

        placeMarker: function (location, map, input, number, override=false) {
            var plugin = this;

            if(!override) {
                // Check if the location is in valid position
                if (!plugin.isValidMarkerPosition(new google.maps.marker.AdvancedMarkerElement({position: location}))) {
                    // if the marker is the only marker
                    if (plugin.markers.size === 1) {
                        // populate plugin.mapnotice and fade out slowly after five seconds
                        plugin.setNotice('The marker must be within 10km of a break location.');
                    } else {
                        plugin.setNotice('Markers must be within 20km of each other.');
                    }
                    return;
                }
            }

            // If no number is provided, increment the counter
            if (number === undefined) {
                plugin.markerCounter++;
                number = plugin.markerCounter;
            }

            // Create pin element with label
            const pin = new google.maps.marker.PinElement({
                glyph: "",  // Remove the label
                scale: 1.3,
                background: "#FF6B6B",
                borderColor: "#FF0000"
            });

            // Create a new marker
            var marker = new google.maps.marker.AdvancedMarkerElement({
                position: location,
                map: map,
                content: pin.element,
                draggable: true,
                title: "Forecast Location " + number
            });

            // Add marker to the array with type information
            plugin.markers.set(number.toString(), { 
                marker: marker, 
                pin: pin,
                type: 'forecast'  // All new markers are forecast locations
            });

            // Update the hidden input field with the markers' coordinates
            plugin.updateInput(input);

            // Add a position_changed event listener to update the input when the marker is moved
            marker.addListener('position_changed', () => {
                if (plugin.isValidMarkerPosition(marker)) {
                    plugin.updateInput(input);
                } else {
                    // if the marker is the only marker
                    if (plugin.markers.size === 1) {
                        plugin.setNotice('The marker must be within 10km of a break location.');
                    } else {
                        plugin.setNotice('Markers must be within 20km of each other.');
                    }
                    marker.position = location;  // Revert to the original position
                }
            });

            // Add right-click event listener to remove the marker if it's the last one
            marker.addListener('rightclick', () => {
                if (plugin.markers.size > 1 && number === plugin.markerCounter) {
                    // check if remaining markers are valid
                    if (!plugin.remainingMarkersValid()) {
                        plugin.setNotice('This would put breaks out of forecast range. Please move the location or remove the nearby breaks.');
                        return;
                    }

                    marker.map = null;  // Remove marker from map
                    var index = Array.from(plugin.markers.keys()).find(key => plugin.markers.get(key).marker === marker);
                    if (index > -1) {
                        plugin.markers.delete(index);  // Remove marker from array
                        plugin.markerCounter--;  // Decrement the counter
                    }
                    plugin.updateInput(input);  // Update the input
                } else {
                    plugin.setNotice('Only the last marker can be deleted and never the first one.');
                }
            });
        },
        calculateDistance: function (pointA, pointB) {
            var rad = function (x) {
                return x * Math.PI / 180;
            };

            var R = 6371; // Earth’s mean radius in km
            var dLat = rad(pointB.lat() - pointA.lat());
            var dLong = rad(pointB.lng() - pointA.lng());
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(rad(pointA.lat())) * Math.cos(rad(pointB.lat())) * Math.sin(dLong / 2) * Math.sin(dLong / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            var d = R * c;
            return d; // returns the distance in km
        },
        allMarkersValid: function () {
            var plugin = this;
            return Array.from(plugin.markers.values()).every(function (m) {
                return plugin.isValidMarkerPosition(m.marker);
            });
        },
        setNotice: function (html){
            var plugin = this;
            plugin.notice.innerHTML = html;
            plugin.notice.style.display = 'block';
            setTimeout(function () {
                plugin.notice.style.display = 'none';
            }, 5000);
        },

        remainingMarkersValid: function () {
            var plugin = this;
            var onelessthing = Array.from(plugin.markers.values()).slice(0, -1);
            return Array.from(plugin.markers.values()).slice(0, -1).every(function (m) {
                return plugin.isValidMarkerPosition(m.marker,onelessthing);
            });
        },
        isValidMarkerPosition: function (marker, markers=null) {
            var plugin = this;
            if(markers==null){
                markers=Array.from(plugin.markers.values());
            }
            // exclude marker from markers
            console.log(marker);

            var position = marker.getPosition();
            // if this is the first marker
            if (markers.length === 0) {
                // check distance from plugin.boundsdata
                var locations = plugin.boundsdata;

                // if there are no locations, return true
                if (!locations||!locations.length) {
                    return true;
                }
                // make sure the marker is within 10km of one of the locations
                var isValid = locations.some(function (location) {
                    var latLng = new google.maps.LatLng(location.lat, location.lng);
                    return plugin.calculateDistance(latLng, position) <= 10;
                });
            }else{
                return markers.some(function (m) {
                    return m.marker !== marker && plugin.calculateDistance(m.marker.getPosition(), position) <= 20;
                });
            }
        },
        updateInput: function(input) {
            var coordinates = [];
            Array.from(plugin.markers.values()).forEach(function(markerObj) {
                var position = markerObj.marker.position;
                coordinates.push({
                    type: 'forecast',  // All markers are forecast locations
                    lat: position.lat,
                    lng: position.lng,
                    location: markerObj.marker.title
                });
            });
            input.value = JSON.stringify(coordinates);
        }

    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);

;(function ($) {
    "use strict";

    $(document).ready(function () {
        $('.exopite-sof-field-multiplemap').exopiteSOFMultipleMap();
    });

}(jQuery));
