/**
 * Exopite Simple Options Framework Trumbowyg
 */
; (function ($, window, document, undefined) {

    var pluginName = "exopiteSOFMap";

    // The actual plugin constructor
    function Plugin(element, options) {

        this.element = element;
        this._name = pluginName;
        this.$element = $(element);
        this.init();

    }

    Plugin.prototype = {

        init: function () {

            var plugin = this;
            plugin.$element.find('.exopitemap-control').each(function (index, el) {
                if ($(el).parents('.exopite-sof-cloneable__muster').length) return;
                if ($(el).hasClass('.disabled')) return;
                var thisinput = el.querySelector('input[type="hidden"]');  // Get the hidden input field
                var thismap = el.querySelector('.exopitemap');  // Use the correct class
                var mapOptions = {
                    mapTypeId: google.maps.MapTypeId.SATELLITE,
                    center: new google.maps.LatLng(-32, 151),
                    zoom: 12
                };
                var map = new google.maps.Map(thismap, mapOptions);

                // Add a click event listener to the map
                map.addListener('click', function(event) {
                    plugin.placeMarker(event.latLng, map, thisinput);  // Pass the hidden input field
                });
                // Check if the input has an initial value
                if (thisinput.value) {
                    var initialCoords = thisinput.value.split(',');
                    if (initialCoords.length === 2) {
                        var initialLatLng = new google.maps.LatLng(parseFloat(initialCoords[0]), parseFloat(initialCoords[1]));
                        plugin.placeMarker(initialLatLng, map, thisinput);  // Place the initial marker
                        map.setCenter(initialLatLng);  // Center the map
                        map.setZoom(10);
                    }
                }
            });

            plugin.$element.closest('.exopite-sof-wrapper').on('exopite-sof-field-group-item-added-after', function (event, $cloned) {

                // $cloned.find('.exopitemap-control').each(function (index, el) {
                //     var thisinput = el.querySelector('input[type="hidden"]');
                //     var thismap = el.querySelector('.exopitemap');
                //     var mapOptions = {
                //         mapTypeId: google.maps.MapTypeId.SATELLITE,
                //         center: new google.maps.LatLng(-33.796, 151.288),
                //         zoom: 12
                //     };
                //     var map = new google.maps.Map(thismap, mapOptions);
                //
                //     map.addListener('click', function(event) {
                //         plugin.placeMarker(event.latLng, map, thisinput);
                //     });
                //     // Check if the input has an initial value
                //     if (thisinput.value) {
                //         var initialCoords = thisinput.value.split(',');
                //         if (initialCoords.length === 2) {
                //             var initialLatLng = new google.maps.LatLng(parseFloat(initialCoords[0]), parseFloat(initialCoords[1]));
                //             plugin.placeMarker(initialLatLng, map, thisinput);  // Place the initial marker
                //             map.setCenter(initialLatLng);  // Center the map
                //         }
                //     }
                // });

            });

        },


        placeMarker: function(location, map, input) {
            var plugin = this;

            // Remove the previous marker if it exists
            if (plugin.marker) {
                plugin.marker.map = null;
            }

            // Create pin element
            const pin = new google.maps.marker.PinElement({
                scale: 1.0,
                background: "#FF0000",
                borderColor: "#000000"
            });

            // Create a new marker
            plugin.marker = new google.maps.marker.AdvancedMarkerElement({
                position: location,
                map: map,
                content: pin.element,
                draggable: true  // Make the marker draggable
            });

            // Update the hidden input field with the marker's coordinates
            input.value = location.lat() + ',' + location.lng();

            // Add a position_changed event listener to update the input when the marker is moved
            plugin.marker.addListener('position_changed', () => {
                const position = plugin.marker.position;
                input.value = position.lat() + ',' + position.lng();
            });
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

; (function ($) {
    "use strict";

    $(document).ready(function () {

        $('.exopite-sof-field-map').exopiteSOFMap();

    });

}(jQuery));
