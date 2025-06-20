/**
 * Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 *
 * The abbr plugin dialog window definition.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Our dialog definition.
CKEDITOR.dialog.add( 'gmap', function( editor ) {
    var gmapContainer = '';

    // Access the current translation file.
    var pluginTranslation = editor.lang.gmap;

    // Dialog's function callback for the gmap Map Widget.
    return {
        title: pluginTranslation.dialogTitle,
        minWidth: 320,
        minHeight: 125,

        contents: [{
            // Create a Location tab.
            id: 'location_tab',
            label: pluginTranslation.locationTabLabel,
            elements: [
                {
                    id: 'geocode',
                    className: 'gmap_geocode',
                    type: 'text',
                    label: pluginTranslation.googleSearchFieldLabel,
                    style: 'margin-top: -7px;',

                    setup: function(widget) {
                        this.setValue('');
                    },
                    onShow: function (widget) {
                        // Get the DOM reference for the Search field.
                        var input = jQuery('.gmap_geocode input')[0];

                        // Set a diffused/default text for better user experience.
                        // This will override the Google's default placeholder text:
                        // 'Enter a location'.
                        jQuery('.gmap_geocode input').attr('placeholder', pluginTranslation.googleSearchFieldHint);

                        // Bind the Search field to the Autocomplete widget.
                        jQuery('.gmap_geocode input').each(function(i){
                            "use strict";
                            var autocomplete = new google.maps.places.Autocomplete(this);
                            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                              var place = autocomplete.getPlace();
                              jQuery('.gmap_latitude input').val(place.geometry.location.lat());
                              jQuery('.gmap_longitude input').val(place.geometry.location.lng());
                            });
                        });
                        // var autocomplete = new google.maps.places.Autocomplete(input);

                        // Fix for the Google's type-ahead search displaying behind
                        // the widgets dialog window.
                        // Basically, we want to override the z-index of the
                        // Seach Autocomplete list, in which the styling is being set
                        // in real-time by Google.
                        // Make a new DOM element.
                        var stylesheet = jQuery('<style type="text/css" />');
                        stylesheet.html('.pac-container { z-index: 100000 !important;} ');
                        // Append to the main document's Head section.
                        jQuery('head').append(stylesheet);
                    },
                    commit: function(widget) {
                        // Remove the iframe if it has one.
                        widget.element.setHtml('');
                        jQuery.ajaxSetup({
                            async: false
                        });
                        // Retrieve the value in the Search field.
                        var geocode = jQuery('.gmap_geocode input').val();
                        var latitude, longitude;
                        /*
                        if (geocode != '') {

                          // No need to call the encodeURIComponent().
                          var geocodingRequest = '//maps.googleapis.com/maps/api/geocode/json?address=' + geocode + '&sensor=false';

                          // Disable the asynchoronous behavior temporarily so that
                          // waiting for results will happen before proceeding
                          // to the next statements.
                          jQuery.ajaxSetup({
                              async: false
                          });

                          // Geocode the retrieved place name.
                          jQuery.getJSON(geocodingRequest, function(data) {
                              if (data['status'] != 'ZERO_RESULTS') {
                                  // Get the Latitude and Longitude object in the
                                  // returned JSON object.
                                  latitude = data.results[0].geometry.location.lat;
                                  longitude = data.results[0].geometry.location.lng;
                              }

                              // Handle queries with no results or have some
                              // malformed parameters.
                              else {
                                  alert('The Place could not be Geocoded properly. Kindly choose another one.')
                              }
                          });

                        }
                        */
                        // Get the Lat/Lon values from the corresponding fields.
                        var latInput = jQuery('.gmap_latitude input').val();
                        var lonInput = jQuery('.gmap_longitude input').val();

                        // Get the data-lat and data-lon values.
                        // It is empty for yet to be created widgets.
                        var latSaved = widget.element.data('lat');
                        var lonSaved = widget.element.data('lon');

                        // Used the inputted values if it's not empty or
                        // not equal to the previously saved values.
                        // latSaved and lonSaved are initially empty also
                        // for widgets that are yet to be created.
                        // Or if the user edited an existing map, and did not edit
                        // the lat/lon fields, and the Search field is empty.
                        if ((latInput != '' && lonInput != '') && ((latInput != latSaved && lonInput != lonSaved) || geocode == '')) {
                            latitude = latInput;
                            longitude = lonInput;
                        }

                        var width = jQuery('.gmap_map_width input').val() || '400';
                        var height = jQuery('.gmap_map_height input').val() || '400';
                        var zoom = jQuery('select.gmap_zoom').val();
                        var popUpText = jQuery('.gmap_popup-text input').val();
                        var styler = jQuery('select.styler').val();
                        var alignment = jQuery('select.gmap_alignment').val();


                        // Get a unique timestamp:
                        var milliseconds = new Date().getTime();

                        // Set/Update the widget's data attributes.
                        widget.element.setAttribute('id', 'gmap_div-' + milliseconds);

                        widget.element.data('lat', latitude);
                        widget.element.data('lon', longitude);
                        widget.element.data('width', width);
                        widget.element.data('height', height);
                        widget.element.data('zoom', zoom);
                        widget.element.data('popup-text', popUpText);
                        widget.element.data('styler', styler);
                        widget.element.data('alignment', alignment);

                        // Remove the previously set alignment class.
                        // Only one alignment class is set per map.
                        widget.element.removeClass('align-left');
                        widget.element.removeClass('align-right');
                        widget.element.removeClass('align-center');

                        // Set the alignment for this map.
                        widget.element.addClass('align-' + alignment);

                        // Returns 'on' or 'undefined'.
                        var responsive = jQuery('.gmap_responsive input:checked').val();

                        // Use 'off' if the Responsive checkbox is unchecked.
                        if (responsive == undefined) {
                            responsive = 'off';

                            // Remove the previously set responsive map class,
                            // if there's any.
                            widget.element.removeClass('responsive-map');
                        }

                        else {
                            // Add a class for styling.
                            widget.element.addClass('responsive-map');
                        }

                        // Set the 'responsive' data attribute.
                        widget.element.data('responsive', responsive);

                        // Build the full path to the map renderer.
                        gmapParserPathFull = gmapParserPath + '?lat=' + latitude + '&lon=' + longitude + '&width=' + width + '&height=' + height + '&zoom=' + zoom + '&text=' + popUpText + '&styler=' + styler + '&responsive=' + responsive;

                        // Create a new CKEditor DOM's iFrame.
                        var iframe = new CKEDITOR.dom.element('iframe');

                        // Setup the iframe characteristics.
                        iframe.setAttributes({
                            'scrolling': 'no',
                            'id': 'gmap_iframe-' + milliseconds,
                            'class': 'gmap_iframe',
                            'width': width + 'px',
                            'height': height + 'px',
                            'frameborder': 0,
                            'allowTransparency': true,
                            'src': gmapParserPathFull,
                            'data-cke-saved-src': gmapParserPathFull
                        });

                        // If map is responsive.
                        if (responsive == 'on') {
                            // Add a class for styling.
                            iframe.setAttribute('class', 'gmap_iframe responsive-map-iframe');
                        }

                        // Insert the iframe to the widget's DIV element.
                        widget.element.append(iframe);

                        // Reset/clear the map iframe/DOM object reference.
                        gmapContainer = '';
                    },
                },

                { // Dummy element serving as label/text container only.
                    type: 'html',
                    id: 'map_label',
                    className: 'gmap_label',
                    style: 'margin-bottom: -10px;',
                    html: '<p>' + pluginTranslation.manualCoordinatesFieldLabel + '</p>'
                },

                {
                    // Create a new horizontal group.
                    type: 'hbox',
                    // Set the relative widths of Latitude, Longitude and Zoom fields.
                    widths: [ '50%', '50%' ],
                    children: [
                        {
                            id: 'map_latitude',
                            className: 'gmap_latitude',
                            type: 'text',
                            label: pluginTranslation.manualLatitudeFieldLabel,

                            setup: function(widget) {
                                // Set the Lat values if widget has previous value.
                                if (widget.element.data('lat') !== '') {
                                    // Update the data-lat based on the map lat in iframe.
                                    // Make sure that mapContainer is set.
                                    // Also avoids setting it again since zoom/longitude
                                    // might already computed/set this object.
                                    if (gmapContainer === '') {
                                        gmapContainer = widget.element.getChild(0).$.contentDocument.getElementById('gmap_container');
                                    }

                                    var currentLat = gmapContainer.getAttribute('data-lat');

                                    this.setValue(currentLat);
                                }
                            },
                        },

                        {
                            id: 'map_longitude',
                            className: 'gmap_longitude',
                            type: 'text',
                            label: pluginTranslation.manualLongitudeFieldLabel,

                            setup: function(widget) {
                                // Set the Lon values if widget has previous value.
                                if (widget.element.data('lat') !== '') {
                                    // Update the data-lon based on the map lon in iframe.
                                    // Make sure that mapContainer is set.
                                    // Also avoids setting it again since zoom/latitude
                                    // might already computed/set this object.
                                    if (gmapContainer === '') {
                                        gmapContainer = widget.element.getChild(0).$.contentDocument.getElementById('gmap_container');
                                    }

                                    var currentLon = gmapContainer.getAttribute('data-lon');

                                    this.setValue(currentLon);
                                }
                            },
                        },
                    ]
                },

                {
                    id: 'popup_text',
                    className: 'gmap_popup-text',
                    type: 'text',
                    label: pluginTranslation.popupTextFieldLabel,
                    style: 'margin-bottom: 8px;',

                    setup: function(widget) {
                        // Set the Lat values if widget has previous value.
                        if (widget.element.data('popup-text') != '') {
                            this.setValue(widget.element.data('popup-text'));
                        }

                        else {
                            // Set a diffused/default text for better user experience.
                            jQuery('.gmap_popup-text input').attr('placeholder', pluginTranslation.popupTextFieldHint)
                        }
                    },
                },
            ]
        },

            {
                // Create an Options tab.
                id: 'options_tab',
                label: pluginTranslation.optionsTabLabel,
                elements: [
                    {
                        // Create a new horizontal group.
                        type: 'hbox',
                        style: 'margin-top: -7px;',
                        // Set the relative widths of Latitude, Longitude and Zoom fields.
                        widths: [ '38%', '38%', '24%' ],
                        children: [
                            {
                                id: 'width',
                                className: 'gmap_map_width',
                                type: 'text',
                                label: pluginTranslation.mapWidthFieldLabel,

                                setup: function(widget) {
                                    // Set a diffused/default text for better user experience.
                                    jQuery('.gmap_map_width input').attr('placeholder', '400')

                                    // Set the map width value if widget has a previous value.
                                    if (widget.element.data('width') != '') {
                                        this.setValue(widget.element.data('width'));
                                    }
                                },
                            },

                            {
                                id: 'height',
                                className: 'gmap_map_height',
                                type: 'text',
                                label: pluginTranslation.mapHeightFieldLabel,

                                setup: function(widget) {
                                    // Set a diffused/default text for better user experience.
                                    jQuery('.gmap_map_height input').attr('placeholder', '400');

                                    // Set the map height value if widget has a previous value.
                                    if (widget.element.data('height') != '') {
                                        this.setValue(widget.element.data('height'));
                                    }
                                },
                            },

                            {
                                // Create a select list for Zoom Levels.
                                // 'className' attribute is used for targeting this element in jQuery.
                                id: 'map_zoom',
                                className: 'gmap_zoom',
                                type: 'select',
                                label: pluginTranslation.mapZoomLevelFieldLabel,
                                width: '70px',
                                items: [['1'], ['2'], ['3'], ['4'],['5'], ['6'], ['7'], ['8'], ['9'], ['10'], ['11'], ['12'], ['13'], ['14'], ['15'], ['16'], ['17'], ['18'], ['19'], ['20']],

                                // This will execute also every time you edit/double-click the widget.
                                setup: function(widget) {
                                    // Set this Zoom Level's select list when
                                    // the current location has been initialized and set previously.
                                    if (widget.element.data('zoom') != '') {
                                        // Update the data-zoom based on the map zoom level in iframe.
                                        // Make sure that mapContainer is set.
                                        // Also avoids setting it again since latitude/longitude
                                        // might already computed/set this object.
                                        if (gmapContainer === '') {
                                            gmapContainer = widget.element.getChild(0).$.contentDocument.getElementById('gmap_container');
                                        }

                                        var currentZoom = gmapContainer.getAttribute('data-zoom');

                                        this.setValue(currentZoom);
                                    }

                                    // Set the Default Zoom Level value.
                                    else {
                                        this.setValue('14');
                                    }
                                },
                            }
                        ]
                    },
                    {
                        // Create a new horizontal group.
                        type: 'hbox',
                        // Set the relative widths for the tile and overview map fields.
                        widths: [ '50%', '50%' ],
                        children: [
                            {
                                // Create a select list for map tiles.
                                // 'className' attribute is used for targeting this element in jQuery.
                                type: 'select',
                                id: 'map_styler',
                                className: 'styler',
                                label: pluginTranslation.baseMapStyleLabel,
                                items: [['default'], ['nature'], ['hopper'], ['even_lighter'], ['flat_green'], ['paper']],

                                // This will execute also every time you edit/double-click the widget.
                                setup: function (widget) {
                                    // Set the Tile data attribute.
                                    if (widget.element.data('styler') != '') {
                                        this.setValue(widget.element.data('styler'));
                                    }

                                    else {
                                        // Set the default value.
                                        this.setValue('default');
                                    }
                                },
                            }
                        ]
                    },
                    {
                        // Create a new horizontal group.
                        type: 'hbox',
                        // Set the relative widths for alignment and responsive options.
                        widths: [ '20%', '80%' ],
                        children: [
                            {
                                // Create a select list for Map Alignment.
                                // 'className' attribute is used for targeting this element in jQuery.
                                id: 'map_alignment',
                                className: 'gmap_alignment',
                                type: 'select',
                                label: pluginTranslation.mapAlignmentSelectFieldLabel,
                                items: [[pluginTranslation.mapAlignmentSelectOptionLeft, 'left'], [pluginTranslation.mapAlignmentSelectOptionRight, 'right'], [pluginTranslation.mapAlignmentSelectOptionCenter, 'center']],
                                style: 'margin-bottom: 4px;',

                                // This will execute also every time you edit/double-click the widget.
                                setup: function(widget) {
                                    // Set this map alignment's select list when
                                    // the current map has been initialized and set previously.
                                    if (widget.element.data('alignment') != '') {
                                        // Set the alignment.
                                        this.setValue(widget.element.data('alignment'));
                                    }

                                    // Set the Default alignment value.
                                    else {
                                        this.setValue('left');
                                    }
                                },
                            },

                            {
                                type: 'checkbox',
                                id: 'map_responsive',
                                className: 'gmap_responsive',
                                label: pluginTranslation.responsiveMapCheckboxLabel,
                                style: 'margin-top: 18px;',

                                // This will execute also every time you edit/double-click the widget.
                                setup: function(widget) {
                                    // Set the Responsive check button, when editing widget.
                                    if (widget.element.data('responsive') != '') {
                                        if (widget.element.data('responsive') == 'on') {
                                            this.setValue('true');
                                        }

                                        else {
                                            this.setValue('');
                                        }
                                    }

                                    // Set the default value for new ones.
                                    else {
                                        this.setValue('true');
                                    }
                                },
                            }
                        ]
                    }
                ]
            }]
    };
});
