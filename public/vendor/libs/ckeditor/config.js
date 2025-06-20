/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function(config) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config
    config.language = 'zh';
    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarCanCollapse = true;
    config.extraPlugins = 'preview,autogrow,tableresize,youtube,lineutils,widget,colorbutton,justify,image2,wordcount'; //gmap,
    config.toolbarGroups = [
        { name: 'clipboard', groups: ['clipboard', 'undo'] },
        { name: 'editing', groups: ['find', 'selection', 'spellchecker'] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'forms' },
        { name: 'tools' },
        { name: 'document', groups: ['mode', 'document', 'doctools'] },
        { name: 'others' },
        '/',
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'] },
        { name: 'styles' },
        { name: 'colors' },
        { name: 'about' }
    ];

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';
    config.allowedContent = true;
    config.youtube_related = false;

    // Simplify the dialog windows.
    config.removeDialogTabs = 'youtubePlugin:txtEmbed;youtubePlugin:txtOR;image:Upload;image:advanced;image2:Upload;link:advanced;';
    // word count
    config.wordcount = {

        // Whether or not you want to show the Word Count
        showWordCount: false,
    
        // Whether or not you want to show the Char Count
        showCharCount: true,
        
        // Maximum allowed Word Count
        // maxWordCount: 4,
    
        // Maximum allowed Char Count
        // maxCharCount: 10
    };
};