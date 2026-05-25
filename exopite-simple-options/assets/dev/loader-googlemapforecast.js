/**
 * Google Map Forecast Display
 */
;(function($, window, document, undefined) {
    var pluginName = "googlemapforecast";

    function Plugin(element, options) {
        this.element = element;
        this._name = pluginName;
        this.$element = $(element);
        this.markers = new Map();
        this.init();
    }

    $.extend(Plugin.prototype, {
        init: function() {
            var plugin = this;
            var $field = plugin.$element.closest('.exopite-sof-field');
            
            try {
                var value = plugin.$element.val();
                console.log('Raw field value:', value);
                
                if (!value) {
                    console.error('Field value is empty');
                    return;
                }

                var locations = JSON.parse(value);
                console.log('Parsed locations:', locations);
                
                if (!Array.isArray(locations) || !locations.length) {
                    console.error('No valid locations array found in field value');
                    return;
                }

                // Create map container if it doesn't exist
                var $mapContainer = plugin.$element.next('.google-map');
                if (!$mapContainer.length) {
                    $mapContainer = $('<div>').addClass('google-map').css({
                        width: '100%',
                        height: '400px',
                        marginTop: '10px'
                    });
                    plugin.$element.after($mapContainer);
                }
                
                // Initialize map
                plugin.initMap($mapContainer[0], locations);
                
                // Listen for changes to forecast beaches selection
                var $forecastSelect = $('[data-depend-id="forecastbeaches"]');
                $forecastSelect.on('change', function() {
                    var selectedIds = $(this).val() || [];
                    plugin.updateMarkers(selectedIds);
                });
            } catch (e) {
                console.error('Error initializing map:', e);
                console.error(e);
            }
        },

        initMap: function(container, locations) {
            var plugin = this;
            
            try {
                // Find center point
                var bounds = new google.maps.LatLngBounds();
                var hasValidLocations = false;

                locations.forEach(function(loc) {
                    if (loc.latitude && loc.longitude) {
                        var lat = parseFloat(loc.latitude);
                        var lng = parseFloat(loc.longitude);
                        
                        if (!isNaN(lat) && !isNaN(lng)) {
                            bounds.extend(new google.maps.LatLng(lat, lng));
                            hasValidLocations = true;
                        } else {
                            console.error('Invalid coordinates for location:', loc);
                        }
                    }
                });

                if (!hasValidLocations) {
                    console.error('No valid coordinates found in locations');
                    return;
                }

                // Create map
                var map = new google.maps.Map(container, {
                    zoom: 12,
                    mapTypeId: google.maps.MapTypeId.HYBRID
                });
                map.fitBounds(bounds);

                // If there's only one location, set a reasonable zoom level
                google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                    if (locations.length === 1) {
                        // Set a reasonable zoom level for a single location
                        if (map.getZoom() > 14) {
                            map.setZoom(14);
                        }
                    }
                });

                plugin.map = map;

                // Create markers
                locations.forEach(function(location) {
                    if (!location.latitude || !location.longitude) {
                        console.error('Invalid coordinates for location:', location);
                        return;
                    }

                    var lat = parseFloat(location.latitude);
                    var lng = parseFloat(location.longitude);
                    
                    if (isNaN(lat) || isNaN(lng)) {
                        console.error('Invalid coordinate values:', location);
                        return;
                    }

                    var position = new google.maps.LatLng(lat, lng);

                    var marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: location.title || '',
                        draggable: false,
                        animation: google.maps.Animation.DROP,
                        
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 4,
                            fillColor: "#00FFFF",
                            fillOpacity: 1,
                            strokeColor: "#000000",
                            strokeWeight: 1
                        }
                    });

                    if (location.ID) {
                        plugin.markers.set(location.ID.toString(), {
                            marker: marker,
                            data: location
                        });
                    }
                });

                // Check initial forecast selection
                var $forecastSelect = $('[data-depend-id="forecastbeaches"]');
                var selectedIds = $forecastSelect.val() || [];
                if (selectedIds.length) {
                    plugin.updateMarkers(selectedIds);
                }
            } catch (e) {
                console.error('Error in initMap:', e);
                console.error(e);
            }
        },

        updateMarkers: function(forecastIds) {
            var plugin = this;
            
            plugin.markers.forEach(function(markerInfo, id) {
                var isForecast = forecastIds.includes(id);
                var marker = markerInfo.marker;
                
                marker.setIcon({
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: isForecast ? 10 : 8,
                    fillColor: isForecast ? "#FF6B6B" : "#00FFFF",
                    fillOpacity: 1,
                    strokeColor: isForecast ? "#FF0000" : "#000000",
                    strokeWeight: 1
                });
            });
        }
    });

    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);

jQuery(document).ready(function($) {
    $('.exopite-sof-field-googlemapforecast').googlemapforecast();
});
