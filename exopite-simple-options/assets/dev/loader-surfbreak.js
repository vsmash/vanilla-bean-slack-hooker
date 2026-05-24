/**
 * Exopite Simple Options Framework Trumbowyg
 */
; (function ($, window, document, undefined) {

    var pluginName = "exopiteSOFSurfbreak";

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this._name = pluginName;
        this.$element = $(element);
        this.url = '/wp-content/plugins/localknowledge/admin/exopite-simple-options/assets/';
        this.history = [];
        
        // Initialize the map when the document is ready
        var self = this;
        $(document).ready(function() {
            // Show the second tab by default
            $('.exopite-sof-accordion-item').eq(1).removeClass('exopite-sof-accordion-hide').addClass('exopite-sof-accordion-show');
            
            // Initialize the map
            if (typeof init === 'function') {
                init();
            }
            
            // Initialize the plugin
            self.init();
        });
    }

    Plugin.prototype = {

        init: function () {

            var plugin = this;
            plugin.$element.find('.exopitesurfbreak-control').each(function (index, el) {
                if ($(el).parents('.exopite-sof-cloneable__muster').length) return;
                if ($(el).hasClass('disabled')) return;
                var btnftb = el.querySelector('.fittobounds');
                var btnundo = el.querySelector('.undochange');
                // event listen for the fittobounds button
                plugin.notice = el.querySelector('.mapnotice');  // Get the notice element

                var thisinput = el.querySelector('input[type="hidden"]');  // Get the hidden input field
                // get the data-depend-id
                var dependid = $(thisinput).data('depend-id');
                var searchBoxInput = el.querySelector('.map-search-box'); // Get the search box input field

                $('#publish').click(function(e) {
                    var isValid = true;
                    $("#msg-"+dependid).remove();
                    // Example: Check if a required field with id 'my_custom_field' is filled
                    if ($(thisinput).val() === '') {
                        isValid = false;
                        $('#post').before('<div id="msg-'+dependid+'" class="error notice is-dismissible"><p>'+'Please choose a break location on the map before publishing.'+'</p></div>');
                        // scroll to the element
                        $('html, body').animate({
                            scrollTop: $(thisinput).offset().top
                        }, 1000);

                    }

                    // If not valid, prevent the form from being submitted
                    if (!isValid) {
                        e.preventDefault();
                        return false;
                    }
                });

                var thismap = el.querySelector('.exopitesurfbreak');  // Use the correct class
                 // Get map center from data attribute
                 var defaultCenter = new google.maps.LatLng(-33.796, 151.288);
                 var mapCenter = null;
                 try {
                     centerobj = $(el).data('map-center');
                     mapCenter = new google.maps.LatLng(centerobj.lat, centerobj.lng);
                     console.log(mapCenter);
                    } catch(e) {
                    console.log('No valid map center provided');
                    mapCenter = defaultCenter;
                 }
                var mapOptions = {
                    mapId: "2df304dfb8ff94e0", // Add Map ID for Advanced Markers support
                    mapTypeId: google.maps.MapTypeId.SATELLITE,
                    center: mapCenter,
                    zoom: 14
                };
                var map = new google.maps.Map(thismap, mapOptions);
                // Add a click event listener to the map
                map.addListener('click', function(event) {
                    plugin.placeMarker(event.latLng, map, thisinput);  // Pass the hidden input field
                });

                var searchBox = new google.maps.places.SearchBox(searchBoxInput); // Initialize the search box
                map.addListener('bounds_changed', function () {
                    searchBox.setBounds(map.getBounds());
                });

                plugin.map = map;

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


                // Check if the input has an initial value and set markers accordingly
                if (thisinput.value) {
                    console.log('thisinput.value', thisinput.value);
                    try {
                        var initialData = JSON.parse(thisinput.value);
                        console.log('initialData', initialData);
                        if (initialData.stageleft&& initialData.stageleft.lat && initialData.leftaccess && initialData.stageright && initialData.perpendicular) {
                            var stageleft = new google.maps.LatLng(initialData.stageleft.lat, initialData.stageleft.lng);
                            var stageright = new google.maps.LatLng(initialData.stageright.lat, initialData.stageright.lng);
                            var perpendicular = new google.maps.LatLng(initialData.perpendicular.lat, initialData.perpendicular.lng);
                            var leftaccess = new google.maps.LatLng(initialData.leftaccess.lat, initialData.leftaccess.lng);
                            var rightaccess = new google.maps.LatLng(initialData.rightaccess.lat, initialData.rightaccess.lng);
                            plugin.setInitialMarkers(stageleft, stageright, perpendicular, leftaccess, rightaccess, map, thisinput);
                        }else{
                            throw new Error("Invalid initial data");
                        }
                    } catch (e) {
                        console.error("Invalid JSON data in input field:", e);
                    }
                }
                btnftb.addEventListener('click', function (e) {
                    e.preventDefault();
                    var bounds = new google.maps.LatLngBounds();
                    bounds.extend(plugin.stageleft.position);
                    bounds.extend(plugin.stageright.position);
                    bounds.extend(plugin.perpendicular.position);
                    bounds.extend(plugin.leftaccess.position);
                    bounds.extend(plugin.rightaccess.position);
                    plugin.map.fitBounds(bounds);
                });
                btnundo.addEventListener('click', function (e) {
                    e.preventDefault();
                    plugin.undoChange(thisinput, map);  // Add the undo functionality
                });

            });

            plugin.$element.closest('.exopite-sof-wrapper').on('exopite-sof-field-group-item-added-after', function (event, $cloned) {

                // $cloned.find('.exopitemap-control').each(function (index, el) {
                //     var thisinput = el.querySelector('input[type="hidden"]');
                //     var thismap = el.querySelector('.exopitemap');
                //     var mapOptions = {
                //         mapTypeId: google.maps.MapTypeId.SATELLITE,
                //         center: new google.maps.LatLng(-32, 151),
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
        clearMapItem: function(itemName){
            var plugin = this;
            if (plugin[itemName]){
                plugin[itemName].setMap(null);
            }
        }, // removes a single map item if it exists
        clearMap: function(){
            var mapItems = ['stageleft', 'stageright', 'line', 'centermarker', 'perpendicular', 'waveangle', 'leftaccess', 'rightaccess', 'leftaccessline', 'rightaccessline'];
            var plugin = this;
            mapItems.forEach(function(item){
                plugin.clearMapItem(item);
            });
        }, // removes all map items in specified array
        createMarker: function(markerName, icon, map, position, draggable){
            var plugin = this;
            
            // Create pin element with custom styling
            let pinOptions = {
                scale: 1.0,
                // Remove draggable from PinElement as it's not a valid option
            };

            // Set colors based on icon type
            if (icon === 'red-dot') {
                pinOptions.background = "#FF0000";
                pinOptions.borderColor = "#000000";
            } else if (icon === 'green-dot') {
                pinOptions.background = "#00FF00";
                pinOptions.borderColor = "#000000";
            } else if (icon === 'yellow-dot') {
                pinOptions.background = "#FFFF00";
                pinOptions.borderColor = "#000000";
            } else if (icon.includes('wavemarker.svg')) {
                // For wave marker, we'll use a custom glyph
                pinOptions.background = "#0000FF";
                pinOptions.borderColor = "#000000";
                pinOptions.glyph = "🌊";
            }

            const pin = new google.maps.marker.PinElement(pinOptions);

            // Create the marker with the correct draggable property
            const markerOptions = {
                position: position,
                map: map,
                content: pin.element,
                gmpDraggable: draggable, // Use gmpDraggable instead of draggable
                title: markerName // Add title for debugging
            };

            plugin[markerName] = new google.maps.marker.AdvancedMarkerElement(markerOptions);
        },
        createLine: function(lineName, from,to, colour, map, weight=2){
            var plugin=this;
            plugin[lineName] = new google.maps.Polyline({
                path: [from, to], // Fixed path to show wave angle
                geodesic: true,
                strokeColor: colour,
                strokeOpacity: 1.0,
                strokeWeight: weight
            });
            plugin[lineName].setMap(map);
        },
        placeMarkers: function(stageleft,stagemiddle,stageright,perpendicular,leftaccess,rightaccess,map,input,fibounds=false){
            var plugin=this;
            // check for existing markers
            if (!plugin.stageleft){
                fitbounds=true;
            }
            // Remove the previous markers if they exist
            plugin.clearMap();

            // Helper function to get lat/lng from position
            function getLatLng(pos) {
                return {
                    lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                    lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                };
            }

            // Create a new marker for stageleft
            plugin.createMarker('stageleft', 'red-dot', map, stageleft, true);
            // Create a new marker for stageright
            plugin.createMarker('stageright', 'green-dot', map, stageright, true);

            // Calculate center point
            var left = getLatLng(stageleft);
            var right = getLatLng(stageright);
            plugin.center = {
                lat: (left.lat + right.lat) / 2,
                lng: (left.lng + right.lng) / 2
            };

            // Create a new marker for the perpendicular point
            plugin.createMarker('perpendicular', plugin.url+'images/wavemarker.svg', map, perpendicular, true);
            // Create a new marker for the left access point
            plugin.createMarker('leftaccess', 'yellow-dot', map, leftaccess, true);
            // Create a new marker for the right access point
            plugin.createMarker('rightaccess', 'yellow-dot', map, rightaccess, true);

            var bounds = new google.maps.LatLngBounds();
            bounds.extend(stageleft);
            bounds.extend(stageright);
            bounds.extend(perpendicular);
            bounds.extend(leftaccess);
            bounds.extend(rightaccess);

            if (fitbounds) {
                // Add padding to the bounds
                var ne = bounds.getNorthEast();
                var sw = bounds.getSouthWest();
                var latPadding = (ne.lat() - sw.lat()) * 0.3; // 30% padding
                var lngPadding = (ne.lng() - sw.lng()) * 0.3;
                bounds.extend(new google.maps.LatLng(ne.lat() + latPadding, ne.lng() + lngPadding));
                bounds.extend(new google.maps.LatLng(sw.lat() - latPadding, sw.lng() - lngPadding));
                map.fitBounds(bounds);
            }

            // Calculate and store the initial angle and distance
            plugin.perpendicularAngle = plugin.calculateAngleRelativeToLine(stageleft, stageright, perpendicular);
            plugin.perpendicularDistance = plugin.calculateDistance(plugin.center.lat, plugin.center.lng, perpendicular.lat, perpendicular.lng);

            // Create the lines
            plugin.createLine('line', stageleft, stageright, '#FFFF00', map);
            plugin.createLine('waveangle', plugin.center, perpendicular, '#0000FF', map, 2);
            plugin.createLine('leftaccessline', plugin.center, leftaccess, '#FD7567', map, 1);
            plugin.createLine('rightaccessline', plugin.center, rightaccess, '#20e64d', map, 1);

            // Update lines to their initial positions
            plugin.updateLines();

            // Add event listeners for drag events
            if (plugin.stageleft) {
                plugin.stageleft.addListener('dragend', function() {
                    plugin.updatePolyline(plugin.stageleft.position, plugin.stageright.position, input);
                });
                plugin.stageleft.addListener('drag', function() {
                    plugin.updateLines();
                });
            }

            if (plugin.stageright) {
                plugin.stageright.addListener('dragend', function() {
                    plugin.updatePolyline(plugin.stageleft.position, plugin.stageright.position, input);
                });
                plugin.stageright.addListener('drag', function() {
                    plugin.updateLines();
                });
            }

            if (plugin.perpendicular) {
                plugin.perpendicular.addListener('dragend', function() {
                    plugin.handlePerpendicularDrag(plugin.perpendicular.position, input);
                });
                plugin.perpendicular.addListener('drag', function() {
                    plugin.updateLines();
                });
            }

            if (plugin.leftaccess) {
                plugin.leftaccess.addListener('dragend', function() {
                    plugin.handleLeftAccessDrag(plugin.leftaccess.position, input);
                });
                plugin.leftaccess.addListener('drag', function() {
                    plugin.updateLines();
                });
            }

            if (plugin.rightaccess) {
                plugin.rightaccess.addListener('dragend', function() {
                    plugin.handleRightAccessDrag(plugin.rightaccess.position, input);
                });
                plugin.rightaccess.addListener('drag', function() {
                    plugin.updateLines();
                });
            }
        },
        setInitialMarkers: function (stageleft, stageright, perpendicular, leftaccess, rightaccess, map, input) {
            var plugin = this;
            var stagemiddle = new google.maps.LatLng((stageleft.lat() + stageright.lat()) / 2, (stageleft.lng() + stageright.lng()) / 2);
            plugin.center = stagemiddle;
            this.placeMarkers(stageleft,stagemiddle,stageright,perpendicular,leftaccess,rightaccess,map,input,true);
            // Update the hidden input field with the markers' coordinates
            plugin.updateInput(input);

        },
        placeMarker: function(location, map, input) {
            var plugin = this;
            var stageleft, stageright, stagemiddle, perpendicular, fitbounds=false, leftaccess, rightaccess;
            // is there alredy a marker?
            try {
                var oldlocation = JSON.parse(input.value);
                if (oldlocation.stageleft && oldlocation.stageleft.lat && oldlocation.leftaccess && oldlocation.stageright && oldlocation.perpendicular) {
                    var oldCenter = new google.maps.LatLng(
                        (oldlocation.stageleft.lat + oldlocation.stageright.lat) / 2,
                        (oldlocation.stageleft.lng + oldlocation.stageright.lng) / 2
                    );

                    var displacement = new google.maps.LatLng(
                        location.lat() - oldCenter.lat(),
                        location.lng() - oldCenter.lng()
                    );

                    stageleft = new google.maps.LatLng(
                        oldlocation.stageleft.lat + displacement.lat(),
                        oldlocation.stageleft.lng + displacement.lng()
                    );
                    stageright = new google.maps.LatLng(
                        oldlocation.stageright.lat + displacement.lat(),
                        oldlocation.stageright.lng + displacement.lng()
                    );
                    perpendicular = new google.maps.LatLng(
                        oldlocation.perpendicular.lat + displacement.lat(),
                        oldlocation.perpendicular.lng + displacement.lng()
                    );
                    leftaccess = new google.maps.LatLng(
                        oldlocation.leftaccess.lat + displacement.lat(),
                        oldlocation.leftaccess.lng + displacement.lng()
                    );
                    rightaccess = new google.maps.LatLng(
                        oldlocation.rightaccess.lat + displacement.lat(),
                        oldlocation.rightaccess.lng + displacement.lng()
                    );
                    stagemiddle = location;
                    console.log('Displacement', displacement);
                } else {
                    throw new Error("Invalid old location data");
                }

            }
            catch (e)
            {
                // create a new location
                stageleft = new google.maps.LatLng(location.lat() + 0.001, location.lng());
                stageright = new google.maps.LatLng(location.lat() - 0.001, location.lng());
                stagemiddle = new google.maps.LatLng((stageleft.lat() + stageright.lat()) / 2, (stageleft.lng() + stageright.lng()) / 2);
                // perpendicular point is 100m forward from the middle point
                perpendicular = plugin.calculatePerpendicularCoordinate(stageleft.lat(), stageleft.lng(), stageright.lat(), stageright.lng(), 200, 'forward');
                leftaccess = plugin.calculatePerpendicularCoordinate(stageleft.lat(), stageleft.lng(), stageright.lat(), stageright.lng(), 200, 'forward', 170);
                rightaccess = plugin.calculatePerpendicularCoordinate(stageleft.lat(), stageleft.lng(), stageright.lat(), stageright.lng(), 200, 'forward', 10);
                console.log('stageleft', stageleft, 'stageright', stageright, 'perpendicular', perpendicular);
                fitbounds = true;
            }

            plugin.center = stagemiddle;
            plugin.placeMarkers(stageleft,stagemiddle,stageright,perpendicular,leftaccess,rightaccess,map,input);
            // Update the hidden input field with the markers' coordinates
            plugin.updateInput(input);

        },
        handleLeftAccessDrag: function(leftaccess, input) {
            var plugin = this;
            
            // Calculate angles relative to center
            var leftAngle = plugin.calculateCompassAngle(plugin.center, plugin.stageleft.position);
            var middleAngle = plugin.calculateCompassAngle(plugin.center, plugin.perpendicular.position);
            var newAngle = plugin.calculateCompassAngle(plugin.center, leftaccess);

            // Check if the new angle is within the valid range (between stage left and middle)
            if (!plugin.isAngleBetweenClockwise(newAngle, leftAngle, middleAngle)) {
                // If not valid, get the original position from input
                var oldlocation = JSON.parse(input.value);
                var originalLeftAccess = {
                    lat: oldlocation.leftaccess.lat,
                    lng: oldlocation.leftaccess.lng
                };
                // Revert to the previous position
                plugin.leftaccess.position = originalLeftAccess;
                // Update the line to match the reverted position
                plugin.leftaccessline.setPath([plugin.center, originalLeftAccess]);
                // Update input to ensure it reflects the reverted position
                plugin.updateInput(input);
            } else {
                // Update the wave angle and store the new angle and distance
                plugin.updateLeftAccessAngle(leftaccess, input);
            }
        },
        handleRightAccessDrag: function(rightaccess, input) {
            var plugin = this;
            
            // Calculate angles relative to center
            var middleAngle = plugin.calculateCompassAngle(plugin.center, plugin.perpendicular.position);
            var rightAngle = plugin.calculateCompassAngle(plugin.center, plugin.stageright.position);
            var newAngle = plugin.calculateCompassAngle(plugin.center, rightaccess);

            // Check if the new angle is within the valid range (between middle and stage right)
            if (!plugin.isAngleBetweenClockwise(newAngle, middleAngle, rightAngle)) {
                // If not valid, get the original position from input
                var oldlocation = JSON.parse(input.value);
                var originalRightAccess = {
                    lat: oldlocation.rightaccess.lat,
                    lng: oldlocation.rightaccess.lng
                };
                // Revert to the previous position
                plugin.rightaccess.position = originalRightAccess;
                // Update the line to match the reverted position
                plugin.rightaccessline.setPath([plugin.center, originalRightAccess]);
                // Update input to ensure it reflects the reverted position
                plugin.updateInput(input);
            } else {
                // Update the wave angle and store the new angle and distance
                plugin.updateRightAccessAngle(rightaccess, input);
            }
        },
        handlePerpendicularDrag: function(perpendicular, input) {
            var plugin = this;
            
            // Calculate angles relative to center
            var leftAccessAngle = plugin.calculateCompassAngle(plugin.center, plugin.leftaccess.position);
            var rightAccessAngle = plugin.calculateCompassAngle(plugin.center, plugin.rightaccess.position);
            var newAngle = plugin.calculateCompassAngle(plugin.center, perpendicular);

            // Check if the new angle is within the valid range (between left access and right access)
            if (!plugin.isAngleBetweenClockwise(newAngle, leftAccessAngle, rightAccessAngle)) {
                // If not valid, get the original position from input
                var oldlocation = JSON.parse(input.value);
                var originalPerpendicular = {
                    lat: oldlocation.perpendicular.lat,
                    lng: oldlocation.perpendicular.lng
                };
                // Revert to the previous position
                plugin.perpendicular.position = originalPerpendicular;
                // Update the line to match the reverted position
                plugin.waveangle.setPath([plugin.center, originalPerpendicular]);
                // Update input to ensure it reflects the reverted position
                plugin.updateInput(input);
            } else {
                // Update the wave angle and store the new angle and distance
                plugin.updateWaveAngle(perpendicular, input);
            }
        },
        isOnSameSide: function(point, left, right) {
            var plugin=this;
            var newangle = plugin.calculateCompassAngle(plugin.center, point);
            var leftangle = plugin.calculateCompassAngle(plugin.center, left);
            var rightangle = plugin.calculateCompassAngle(plugin.center, right);
            return plugin.isAngleBetweenClockwise(newangle, leftangle, rightangle);
        },
        isPointBetween: function(point, boundaryA, boundaryB) {
            function getLatLng(pos) {
                return {
                    lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                    lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                };
            }

            var p = getLatLng(point);
            var a = getLatLng(boundaryA);
            var b = getLatLng(boundaryB);

            // Check if point is between the boundaries for both lat and lng
            var latBetween = (p.lat >= Math.min(a.lat, b.lat) && p.lat <= Math.max(a.lat, b.lat));
            var lngBetween = (p.lng >= Math.min(a.lng, b.lng) && p.lng <= Math.max(a.lng, b.lng));

            return latBetween && lngBetween;
        },
        updateLeftAccessAngle: function(leftaccess, input) {
            var plugin = this;
            if (plugin.leftaccess) {
                // Helper function to get lat/lng from position
                function getLatLng(pos) {
                    return {
                        lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                        lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                    };
                }

                var access = getLatLng(leftaccess);
                
                // Update the polyline path
                plugin.leftaccessline.setPath([plugin.center, leftaccess]);

                // Calculate the angle and distance relative to the stageleft-stageright line
                var angle = plugin.calculateAngleRelativeToLine(plugin.stageleft.position, plugin.stageright.position, leftaccess);
                var distance = plugin.calculateDistance(plugin.center.lat, plugin.center.lng, access.lat, access.lng);

                // Store the angle and distance
                plugin.leftAccessAngle = angle;
                plugin.leftAccessDistance = distance;

                // Update the position of the left access marker using position property
                plugin.leftaccess.position = leftaccess;

                // Update the hidden input field
                plugin.updateInput(input);
            }
        },
        updateRightAccessAngle: function(rightaccess, input) {
            var plugin = this;
            if (plugin.rightaccess) {
                // Helper function to get lat/lng from position
                function getLatLng(pos) {
                    return {
                        lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                        lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                    };
                }

                var access = getLatLng(rightaccess);
                
                // Update the polyline path
                plugin.rightaccessline.setPath([plugin.center, rightaccess]);

                // Calculate the angle and distance relative to the stageleft-stageright line
                var angle = plugin.calculateAngleRelativeToLine(plugin.stageleft.position, plugin.stageright.position, rightaccess);
                var distance = plugin.calculateDistance(plugin.center.lat, plugin.center.lng, access.lat, access.lng);
                // Store the angle and distance
                plugin.rightAccessAngle = angle;
                plugin.rightAccessDistance = distance;

                // Update the position of the right access marker using position property
                plugin.rightaccess.position = rightaccess;

                // Update the hidden input field
                plugin.updateInput(input);
            }
        },
        updateWaveAngle: function(perpendicular, input) {
            var plugin = this;
            if (plugin.waveangle) {
                // Helper function to get lat/lng from position
                function getLatLng(pos) {
                    return {
                        lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                        lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                    };
                }

                var perp = getLatLng(perpendicular);
                
                // Update the polyline path
                plugin.waveangle.setPath([plugin.center, perpendicular]);

                // Calculate the angle and distance relative to the stageleft-stageright line
                var angle = plugin.calculateAngleRelativeToLine(plugin.stageleft.position, plugin.stageright.position, perpendicular);
                var distance = plugin.calculateDistance(plugin.center.lat, plugin.center.lng, perp.lat, perp.lng);

                // Store the angle and distance
                plugin.perpendicularAngle = angle;
                plugin.perpendicularDistance = distance;

                // Update the position of the perpendicular marker using position property
                plugin.perpendicular.position = perpendicular;

                // Update the hidden input field
                plugin.updateInput(input);
            }
        },
        updatePolyline: function(stageleft, stageright, input) {
            var plugin = this;
            
            // Ensure we have LatLng objects
            stageleft = (stageleft instanceof google.maps.LatLng) ? stageleft : new google.maps.LatLng(stageleft.lat, stageleft.lng);
            stageright = (stageright instanceof google.maps.LatLng) ? stageright : new google.maps.LatLng(stageright.lat, stageright.lng);
            
            // Update the main line between stage markers
            plugin.line.setPath([stageleft, stageright]);

            // Calculate the new center point
            plugin.center = new google.maps.LatLng(
                (stageleft.lat() + stageright.lat()) / 2,
                (stageleft.lng() + stageright.lng()) / 2
            );

            // Get the old location data
            var oldlocation = JSON.parse(input.value);

            // Calculate the new beach angle
            var newBeachAngle = plugin.calculateCompassAngle(stageleft, stageright);
            
            // Calculate the angle difference
            var angleDifference = (newBeachAngle - oldlocation.beachangle) % 360;

            // Maintain all angles relative to the new beach angle
            var newWaveAngle = (oldlocation.waveangle + angleDifference + 360) % 360;
            var newLeftAccess = (oldlocation.leftaccessangle + angleDifference + 360) % 360;
            var newRightAccess = (oldlocation.rightaccessangle + angleDifference + 360) % 360;

            // Calculate distance for consistent marker placement
            var distance = plugin.calculateDistance(stageleft.lat(), stageleft.lng(), stageright.lat(), stageright.lng());

            // Calculate new positions based on the rotated angles
            var perpendicular = plugin.calculatePerpendicularFromAngle(newWaveAngle, plugin.center.lat(), plugin.center.lng(), distance);
            var leftaccess = plugin.calculatePerpendicularFromAngle(newLeftAccess, plugin.center.lat(), plugin.center.lng(), distance);
            var rightaccess = plugin.calculatePerpendicularFromAngle(newRightAccess, plugin.center.lat(), plugin.center.lng(), distance);

            // Update marker positions
            plugin.perpendicular.position = perpendicular;
            plugin.leftaccess.position = leftaccess;
            plugin.rightaccess.position = rightaccess;

            // Update all connecting lines
            plugin.waveangle.setPath([plugin.center, perpendicular]);
            plugin.leftaccessline.setPath([plugin.center, leftaccess]);
            plugin.rightaccessline.setPath([plugin.center, rightaccess]);

            // Update the input field
            plugin.updateInput(input);
        },

        calculatePointFromAngleAndDistance: function(centerLat, centerLng, angle, distance) {
            // Convert angle to radians (compass bearing to math angle)
            var angleRad = ((90 - angle) % 360) * Math.PI / 180;
            
            // Earth's radius in meters
            var R = 6378137;
            
            // Convert distance to angular distance in radians
            var distRad = distance / R;
            
            // Convert center point to radians
            var centerLatRad = centerLat * Math.PI / 180;
            var centerLngRad = centerLng * Math.PI / 180;
            
            // Calculate new position
            var newLat = Math.asin(
                Math.sin(centerLatRad) * Math.cos(distRad) +
                Math.cos(centerLatRad) * Math.sin(distRad) * Math.cos(angleRad)
            );
            
            var newLng = centerLngRad + Math.atan2(
                Math.sin(angleRad) * Math.sin(distRad) * Math.cos(centerLatRad),
                Math.cos(distRad) - Math.sin(centerLatRad) * Math.sin(newLat)
            );
            
            // Convert back to degrees
            return {
                lat: newLat * 180 / Math.PI,
                lng: newLng * 180 / Math.PI
            };
        },
        calculateCompassAngle: function(from, to) {
            return google.maps.geometry.spherical.computeHeading(from, to);
        },

        updateInput: function(input) {
            var plugin = this;
            console.log('updateInput data', plugin);

            // Helper function to get lat/lng from position
            function getLatLng(position) {
                if (!position) return null;
                return {
                    lat: typeof position.lat === 'function' ? position.lat() : position.lat,
                    lng: typeof position.lng === 'function' ? position.lng() : position.lng
                };
            }

            // Calculate the beach angle (angle of the line from stage left to stage right relative to the compass)
            var beachAngle = plugin.calculateCompassAngle(plugin.stageleft.position, plugin.stageright.position);
            // Calculate the wave angle (angle from perpendicular location to center of stage left/right line relative to the compass)
            var waveAngle = plugin.calculateCompassAngle( plugin.center,plugin.perpendicular.position);
            // Calculate the left access angle (angle from perpendicular location to left access point relative to the compass)
            var leftAccessAngle = plugin.calculateCompassAngle(plugin.center, plugin.leftaccess.position);
            // Calculate the right access angle (angle from perpendicular location to right access point relative to the compass)
            var rightAccessAngle = plugin.calculateCompassAngle(plugin.center, plugin.rightaccess.position);

            var stageleftPos = getLatLng(plugin.stageleft.position);
            var stagerightPos = getLatLng(plugin.stageright.position);
            var leftaccessPos = getLatLng(plugin.leftaccess.position);
            var rightaccessPos = getLatLng(plugin.rightaccess.position);
            var centrePos = getLatLng(plugin.center);
            var perpendicularPos = getLatLng(plugin.perpendicular.position);

            var data = {
                stageleft: { lat: stageleftPos.lat, lng: stageleftPos.lng },
                stageright: { lat: stagerightPos.lat, lng: stagerightPos.lng },
                leftaccess: { lat: leftaccessPos.lat, lng: leftaccessPos.lng },
                rightaccess: { lat: rightaccessPos.lat, lng: rightaccessPos.lng },
                centrestage: { lat: centrePos.lat, lng: centrePos.lng },
                perpendicular: { lat: perpendicularPos.lat, lng: perpendicularPos.lng },
                leftaccessangle: leftAccessAngle,
                rightaccessangle: rightAccessAngle,
                beachangle: beachAngle,
                waveangle: waveAngle
            };

            input.value = JSON.stringify(data);
            plugin.saveState(input.value);
        },

        calculateAngleRelativeToLine: function(lineStart, lineEnd, point) {
            // Helper function to get lat/lng from position
            function getLatLng(pos) {
                return {
                    lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                    lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                };
            }

            var start = getLatLng(lineStart);
            var end = getLatLng(lineEnd);
            var p = getLatLng(point);

            // Calculate the angle of the line formed by lineStart and lineEnd
            var angleLine = Math.atan2(end.lng - start.lng, end.lat - start.lat);
            // Calculate the angle of the point relative to lineStart
            var anglePoint = Math.atan2(p.lng - start.lng, p.lat - start.lat);
            return anglePoint - angleLine;
        },

        calculatePerpendicularFromAngleAndDistance: function(lat, lng, angle, distance, lineStart, lineEnd) {
            var plugin = this;
            const R = 6371e3; // Earth radius in meters
            const d = distance / R; // Angular distance in radians
            const φ = plugin.toRadians(lat);
            const λ = plugin.toRadians(lng);

            var angleLine = Math.atan2(lineEnd.lng() - lineStart.lng(), lineEnd.lat() - lineStart.lat());
            var absoluteAngle = angle + angleLine;

            const φ2 = Math.asin(Math.sin(φ) * Math.cos(d) + Math.cos(φ) * Math.sin(d) * Math.cos(absoluteAngle));
            const λ2 = λ + Math.atan2(Math.sin(absoluteAngle) * Math.sin(d) * Math.cos(φ), Math.cos(d) - Math.sin(φ) * Math.sin(φ2));

            return new google.maps.LatLng(plugin.toDegrees(φ2), plugin.toDegrees(λ2));
        },
        calculatePerpendicularFromAngle: function(angle, centerLat, centerLng, distance=100) {
            var plugin = this;
            // Earth's radius in meters
            const R = 6378137;

            // Convert distance to angular distance in radians
            const angularDistance = distance / R;

            // Convert angle to radians
            const bearing = angle * Math.PI / 180;

            // Get the coordinates of the origin
            const lat1 = centerLat * Math.PI / 180;
            const lon1 = centerLng * Math.PI / 180;

            // Calculate the destination point's latitude and longitude in radians
            const lat2 = Math.asin(
                Math.sin(lat1) * Math.cos(angularDistance) +
                Math.cos(lat1) * Math.sin(angularDistance) * Math.cos(bearing)
            );

            const lon2 = lon1 + Math.atan2(
                Math.sin(bearing) * Math.sin(angularDistance) * Math.cos(lat1),
                Math.cos(angularDistance) - Math.sin(lat1) * Math.sin(lat2)
            );

            // Convert the destination point's coordinates back to degrees
            const lat2Degrees = lat2 * 180 / Math.PI;
            const lon2Degrees = lon2 * 180 / Math.PI;

            return new google.maps.LatLng(lat2Degrees, lon2Degrees);
        },
        toRadians: function(degrees) {
            return degrees * Math.PI / 180;
        },
        toDegrees: function(radians) {
            return radians * 180 / Math.PI;
        },
        // calculate the distance between two points
        calculateDistance: function(lat1, lon1, lat2, lon2) {
            var plugin = this;
            // Handle both function and property access for lat/lng
            lat1 = typeof lat1 === 'function' ? lat1() : lat1;
            lon1 = typeof lon1 === 'function' ? lon1() : lon1;
            lat2 = typeof lat2 === 'function' ? lat2() : lat2;
            lon2 = typeof lon2 === 'function' ? lon2() : lon2;

            var R = 6371e3; // Earth's radius in meters
            var φ1 = plugin.toRadians(lat1);
            var φ2 = plugin.toRadians(lat2);
            var Δφ = plugin.toRadians(lat2 - lat1);
            var Δλ = plugin.toRadians(lon2 - lon1);

            var a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ/2) * Math.sin(Δλ/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c; // Distance in meters
        },
        calculateMidpoint: function(lat1, lon1, lat2, lon2) {
            var plugin = this;
            const φ1 = plugin.toRadians(lat1);
            const λ1 = plugin.toRadians(lon1);
            const φ2 = plugin.toRadians(lat2);
            const Δλ = plugin.toRadians(lon2 - lon1);

            const Bx = Math.cos(φ2) * Math.cos(Δλ);
            const By = Math.cos(φ2) * Math.sin(Δλ);
            const φ3 = Math.atan2(Math.sin(φ1) + Math.sin(φ2), Math.sqrt((Math.cos(φ1) + Bx) * (Math.cos(φ1) + Bx) + By * By));
            const λ3 = λ1 + Math.atan2(By, Math.cos(φ1) + Bx);

            return {
                lat: plugin.toDegrees(φ3),
                lon: plugin.toDegrees(λ3)
            };
        },
        calculatePerpendicularCoordinate: function(lat1, lon1, lat2, lon2, distance = 100, direction = 'forward', thisangle=90) {
            var plugin = this;
            const midpoint = plugin.calculateMidpoint(lat1, lon1, lat2, lon2);
            const φ1 = plugin.toRadians(lat1);
            const λ1 = plugin.toRadians(lon1);
            const φ2 = plugin.toRadians(lat2);
            const λ2 = plugin.toRadians(lon2);

            const angle = Math.atan2(λ2 - λ1, φ2 - φ1);
            const perpendicularAngle = direction === 'forward' ? angle - plugin.toRadians(thisangle) : angle + plugin.toRadians(thisangle);

            const R = 6371e3; // Earth radius in meters
            const d = distance / R; // Angular distance in radians
            const φ3 = Math.asin(Math.sin(plugin.toRadians(midpoint.lat)) * Math.cos(d) + Math.cos(plugin.toRadians(midpoint.lat)) * Math.sin(d) * Math.cos(perpendicularAngle));
            const λ3 = plugin.toRadians(midpoint.lon) + Math.atan2(Math.sin(perpendicularAngle) * Math.sin(d) * Math.cos(plugin.toRadians(midpoint.lat)), Math.cos(d) - Math.sin(plugin.toRadians(midpoint.lat)) * Math.sin(φ3));
            return new google.maps.LatLng(plugin.toDegrees(φ3), plugin.toDegrees(λ3));
        },
        calculateRotation: function(angle) {
            return angle - 90; // Adjust as needed to match the visual orientation
        },
        updateMarkerIcon: function(marker, rotation) {
            var plugin = this;

            marker.setIcon({
                url: plugin.url+'images/wavemarker.svg',
                className: 'wavemarker',
                scaledSize: new google.maps.Size(20, 20), // Adjust size as needed
            });
            document.querySelector('.wavemarker').style.transform = `rotate(30deg)`;

        },
        isAngleBetweenClockwise: function (target, left, right) {
            // Normalize all angles to be between 0 and 360
            target = ((target % 360) + 360) % 360;
            left = ((left % 360) + 360) % 360;
            right = ((right % 360) + 360) % 360;

            if (left <= right) {
                return target >= left && target <= right;
            } else {
                return target >= left || target <= right;
            }
        },

        saveState: function(state) {
            var plugin = this;
            plugin.history.push(state);
            console.log('History:', plugin.history);
        },

        undoChange: function(input, map) {
            var plugin = this;
            if (plugin.history.length > 1) {
                plugin.history.pop(); // Remove the current state
                var previousState = plugin.history[plugin.history.length - 1]; // Get the previous state
                input.value = previousState;
                try {
                    var initialData = JSON.parse(previousState);
                    if (initialData.stageleft && initialData.stageleft.lat && initialData.leftaccess && initialData.stageright && initialData.perpendicular) {
                        var stageleft = new google.maps.LatLng(initialData.stageleft.lat, initialData.stageleft.lng);
                        var stageright = new google.maps.LatLng(initialData.stageright.lat, initialData.stageright.lng);
                        var perpendicular = new google.maps.LatLng(initialData.perpendicular.lat, initialData.perpendicular.lng);
                        var leftaccess = new google.maps.LatLng(initialData.leftaccess.lat, initialData.leftaccess.lng);
                        var rightaccess = new google.maps.LatLng(initialData.rightaccess.lat, initialData.rightaccess.lng);
                        plugin.setInitialMarkers(stageleft, stageright, perpendicular, leftaccess, rightaccess, map, input);
                    } else {
                        throw new Error("Invalid initial data");
                    }
                } catch (e) {
                    console.error("Invalid JSON data in input field:", e);
                }
            } else {
                console.warn("No previous state to revert to.");
            }
        },
        updateLines: function() {
            var plugin = this;

            // Helper function to get lat/lng from position
            function getLatLng(pos) {
                return {
                    lat: typeof pos.lat === 'function' ? pos.lat() : pos.lat,
                    lng: typeof pos.lng === 'function' ? pos.lng() : pos.lng
                };
            }

            // Update center point if stage markers exist
            if (plugin.stageleft && plugin.stageright) {
                var left = getLatLng(plugin.stageleft.position);
                var right = getLatLng(plugin.stageright.position);
                plugin.center = {
                    lat: (left.lat + right.lat) / 2,
                    lng: (left.lng + right.lng) / 2
                };
            }

            // Update all lines
            if (plugin.line && plugin.stageleft && plugin.stageright) {
                plugin.line.setPath([plugin.stageleft.position, plugin.stageright.position]);
            }
            if (plugin.waveangle && plugin.center && plugin.perpendicular) {
                plugin.waveangle.setPath([plugin.center, plugin.perpendicular.position]);
            }
            if (plugin.leftaccessline && plugin.center && plugin.leftaccess) {
                plugin.leftaccessline.setPath([plugin.center, plugin.leftaccess.position]);
            }
            if (plugin.rightaccessline && plugin.center && plugin.rightaccess) {
                plugin.rightaccessline.setPath([plugin.center, plugin.rightaccess.position]);
            }
        },
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
        $('.exopite-sof-field-surfbreak').exopiteSOFSurfbreak();

    });

}(jQuery));
