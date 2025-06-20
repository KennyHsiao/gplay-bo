/*jslint browser: true, newcap: false, white: false, onevar: false, plusplus: false, eqeqeq: false, nomen: false */
/*global window:false, $:false, imgmap:false, air:false */

/** GLOBALS SECTION ***********************************************************/

var mapEditor, props;

/** FUNCTION SECTION **********************************************************/

/**
 *	Handles mouseover on props row.
 */
function gui_row_mouseover(e) {
    if (mapEditor.is_drawing) { return; } //exit if in drawing state
    if (mapEditor.viewmode === 1) { return; } //exit if preview mode
    var obj = (mapEditor.isMSIE) ? window.event.srcElement : e.currentTarget;
    if (typeof obj.aid == 'undefined') { obj = obj.parentNode; }
    //console.log(obj.aid);
    mapEditor.highlightArea(obj.aid);
}

/**
 *	Handles mouseout on props row.
 */
function gui_row_mouseout(e) {
    if (mapEditor.is_drawing) { return; } //exit if in drawing state
    if (mapEditor.viewmode === 1) { return; } //exit if preview mode
    var obj = (mapEditor.isMSIE) ? window.event.srcElement : e.currentTarget;
    if (typeof obj.aid == 'undefined') { obj = obj.parentNode; }
    mapEditor.blurArea(obj.aid);
}

/**
 *	Handles click on props row.
 */
function gui_row_click(e) {
    if (mapEditor.viewmode === 1) { return; } //exit if preview mode
    var obj = (mapEditor.isMSIE) ? window.event.srcElement : e.currentTarget;
    //var multiple = (e.originalTarget.name == 'img_active');
    //mapEditor.log(e.originalTarget);
    if (typeof obj.aid == 'undefined') { obj = obj.parentNode; }
    //gui_row_select(obj.aid, false, multiple);
    gui_row_select(obj.aid, false, false);
    mapEditor.currentid = obj.aid;
}

/**
 *	Handles click on a property row.
 *	@author	Adam Maschek (adam.maschek(at)gmail.com)
 *	@date	2006-06-06 16:55:29
 */
function gui_row_select(id, setfocus, multiple) {
    if (mapEditor.is_drawing) { return; } //exit if in drawing state
    if (mapEditor.viewmode === 1) { return; } //exit if preview mode
    if (!document.getElementById('img_active_' + id)) { return; }
    //if (!multiple)
    gui_cb_unselect_all();
    document.getElementById('img_active_' + id).checked = 1;
    if (setfocus) {
        document.getElementById('img_active_' + id).focus();
    }
    //remove all background styles
    for (var i = 0; i < props.length; i++) {
        if (props[i]) {
            props[i].style.background = '';
        }
    }
    //put highlight on actual props row
    props[id].style.background = '#e7e7e7';
}


/**
 *	Unchecks all checboxes/radios.
 */
function gui_cb_unselect_all() {
    for (var i = 0; i < props.length; i++) {
        if (props[i]) {
            document.getElementById('img_active_' + i).checked = false;
        }
    }
}

/**
 *	Gets the position of the cursor in the input box.
 *	@author	Diego Perlini
 *	@url	http://javascript.nwbox.com/cursor_position/
 */
function getSelectionStart(obj) {
    if (obj.createTextRange) {
        var r = document.selection.createRange().duplicate();
        r.moveEnd('character', obj.value.length);
        if (r.text === '') { return obj.value.length; }
        return obj.value.lastIndexOf(r.text);
    } else {
        return obj.selectionStart;
    }
}

/**
 *	Sets the position of the cursor in the input box.
 *	@link	http://www.codingforums.com/archive/index.php/t-90176.html
 */
function setSelectionRange(obj, start, end) {
    if (typeof end == "undefined") { end = start; }
    if (obj.setSelectionRange) {
        obj.focus(); // to make behaviour consistent with IE
        obj.setSelectionRange(start, end);
    } else if (obj.createTextRange) {
        var range = obj.createTextRange();
        range.collapse(true);
        range.moveEnd('character', end);
        range.moveStart('character', start);
        range.select();
    }
}

function gui_areaChanged(area) {
    var id = area.aid;
    if (props[id]) {
        if (area.shape) {
            props[id].getElementsByTagName('select')[0].value = area.shape;
        }
        if (area.lastInput) {
            props[id].getElementsByTagName('input')[2].value = area.lastInput;
        }
    }
}

function gui_selectArea(obj) {
    gui_row_select(obj.aid, true, false);
}

function gui_zoom() {
    var scale = document.getElementById('dd_zoom').value;
    var pic = document.getElementById('pic_container').getElementsByTagName('img')[0];
    if (typeof pic == 'undefined') { return false; }
    if (typeof pic.oldwidth == 'undefined' || !pic.oldwidth) {
        pic.oldwidth = pic.width;
    }
    if (typeof pic.oldheight == 'undefined' || !pic.oldheight) {
        pic.oldheight = pic.height;
    }
    pic.width = pic.oldwidth * scale;
    pic.height = pic.oldheight * scale;
    mapEditor.scaleAllAreas(scale);
}

function gui_loadImage(src, w, h) {
    //reset zoom dropdown
    document.getElementById('dd_zoom').value = '1';
    var pic = document.getElementById('pic_container').getElementsByTagName('img')[0];
    if (typeof pic != 'undefined') {
        //delete already existing pic
        pic.parentNode.removeChild(pic);
        delete mapEditor.pic;
    }
    mapEditor.loadImage(src, w, h);
}

/** INIT SECTION **************************************************************/

//instantiate the imgmap component, setting up some basic config values
mapEditor = new imgmap({
    mode: "editor",
    custom_callbacks: {
        // 'onStatusMessage': function(str) { gui_statusMessage(str); }, //to display status messages on gui
        // 'onHtmlChanged': function(str) { gui_htmlChanged(str); }, //to display updated html on gui
        // 'onModeChanged': function(mode) { gui_modeChanged(mode); }, //to switch normal and preview modes on gui
        'onAddArea': function(id) { gui_addArea(id); }, //to add new form element on gui
        // 'onRemoveArea': function(id) { gui_removeArea(id); }, //to remove form elements from gui
        'onAreaChanged': function(obj) { gui_areaChanged(obj); },
        'onSelectArea': function(obj) { gui_selectArea(obj); } //to select form element when an area is clicked
    },
    pic_container: document.getElementById('pic_container'),
    bounding_box: true
});

//array of form elements
props = [];
