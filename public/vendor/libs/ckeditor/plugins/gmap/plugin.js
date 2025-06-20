/**
 * Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 *
 * Basic sample plugin inserting abbreviation elements into the CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

(function() {
    window.gmap_c = function () {
      // Do nothing
    }

    // Check if jQuery is already loaded to avoid redundancy and overriding
    // existing 3rd-party jQuery plugins' bindings to jQuery prototype.
    if (typeof jQuery == 'undefined') {
        // This is asynchronous loading.
        // See also CKEDITOR.scriptLoader.queue.
        CKEDITOR.scriptLoader.load('//code.jquery.com/jquery-1.11.0.min.js');
    }

    setTimeout(function(){
      if (typeof google === 'object' && typeof google.maps === 'object') {
      } else {
        CKEDITOR.scriptLoader.load('//maps.googleapis.com/maps/api/js?key=your_google_api_key&libraries=places&callback=gmap_c');
      }
    }, 3000);
    CKEDITOR.plugins.add('gmap', {
        // Declare dependencies.
        requires: 'widget',
        // Declare the supported languages.
        lang: 'zh',
        // The plugin initialization logic goes inside this method.

        init: function (editor) {
            var pluginTranslation = editor.lang.gmap;

            // Register our dialog file -- this.path is the plugin folder path.
            CKEDITOR.dialog.add('gmap', this.path + 'dialogs/gmap.js');

            gmapParserPath = CKEDITOR.getUrl(this.path + 'mapParser.html');
            // Declare a new widget.
            editor.widgets.add('gmap', {
                // Bind the widget to the Dialog command.
                dialog: 'gmap',

                // Declare the elements to be upcasted back.
                // Otherwise, the widget's code will be ignored.
                // Basically, we will allow all divs with 'gmap_div' class,
                // including their alignment classes, and all iframes with
                // 'gmap_iframe' class, and then include
                // all their attributes.
                // Read more about the Advanced Content Filter here:
                // * http://docs.ckeditor.com/#!/guide/dev_advanced_content_filter
                // * http://docs.ckeditor.com/#!/guide/plugin_sdk_integration_with_acf
                allowedContent: 'div(!gmap_div,align-left,align-right,align-center,responsive-map)[*];'
                + 'iframe(!gmap_iframe,responsive-map-iframe)[*];',

                // Declare the widget template/structure, containing the
                // important elements/attributes. This is a required property of widget.
                template:
                '<div id="" class="gmap_div" data-lat="" data-lon="" data-width="" data-height="" ' +
                'data-zoom="" data-popup-text="" data-alignment="" data-responsive=""></div>',

                // This will be executed when going from the View Mode to Source Mode.
                // This is usually used as the function to convert the widget to a
                // dummy, simpler, or equivalent textual representation.
                downcast: function(element) {
                    // Get the id of the div element.
                    var divId = element.attributes["id"];
                    // Get the numeric part of divId: gmap_div-1399121271748.
                    // We'll use that number for quick fetching of target iframe.
                    var iframeId = "gmap_iframe-" + divId.substring(9);
                    // The current user might have changed the map's zoom level
                    // via mouse events/zoom bar. The marker might have been
                    // dragged also which means its lat/lon had changed.
                    var mapContainer = editor.document.$.getElementById(iframeId).contentDocument.getElementById("gmap_container");

                    // Get the current map states.
                    var currentZoom = mapContainer.getAttribute("data-zoom");
                    var currentLat = mapContainer.getAttribute("data-lat");
                    var currentLon = mapContainer.getAttribute("data-lon");

                    // Update the saved corresponding values in data attributes.
                    element.attributes["data-zoom"] = currentZoom;
                    element.attributes["data-lat"] = currentLat;
                    element.attributes["data-lon"] = currentLon;

                    // Fetch the other data attributes needed for
                    // updating the full path of the map.
                    var width = element.attributes["data-width"];
                    var height = element.attributes["data-height"];
                    var popUpText = element.attributes["data-popup-text"];
                    var styler = element.attributes["data-styler"];
                    var responsive = element.attributes["data-responsive"];

                    // Build the updated full path to the map renderer.
                    var mapParserPathFull = gmapParserPath + "?lat=" + currentLat + "&lon=" + currentLon + "&width=" + width + "&height=" + height + "&zoom=" + currentZoom + "&text=" + popUpText + '&styler=' + styler + "&responsive=" + responsive;

                    // Update also the iframe's 'src' attributes.
                    // Updating 'data-cke-saved-src' is also required for
                    // internal use of CKEditor.
                    element.children[0].attributes["src"] = mapParserPathFull;
                    element.children[0].attributes["data-cke-saved-src"] = mapParserPathFull;

                    // Return the DOM's textual representation.
                    return element;
                },

                // Required property also for widgets, used when switching
                // from CKEditor's Source Mode to View Mode.
                // The reverse of downcast() method.
                upcast: function(element) {
                    // If we encounter a div with a class of 'gmap_div',
                    // it means that it's a widget and we need to convert it properly
                    // to its original structure.
                    // Basically, it says to CKEditor which div is a valid widget.
                    if (element.name == 'div' && element.hasClass('gmap_div')) {
                        return element;
                    }
                },
            });
            // Create a toolbar button that executes the above command.
            editor.ui.addButton('gmap', {

                // The text part of the button (if available) and the tooltip.
                label: pluginTranslation.buttonLabel,
                command: 'gmap',
                icon: this.path + 'icons/gmap.png',
                toolbar: 'insert,1'
            });

            if (typeof editor.config.contentsCss == 'object') {
                editor.config.contentsCss.push(CKEDITOR.getUrl(this.path + 'css/contents.css'));
            }

            else {
                editor.config.contentsCss = [editor.config.contentsCss, CKEDITOR.getUrl(this.path + 'css/contents.css')];
            }
        }
    });
})();