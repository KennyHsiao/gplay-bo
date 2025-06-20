pn = editor.Panels;
panelViews = pn.addPanel({
    id: "views"
});
panelViews.get("buttons").add([
    {
        attributes: {
            title: "Open Code"
        },
        className: "fa fa-file-code-o",
        command: "open-code",
        togglable: false, //do not close when button is clicked again
        id: "open-code"
    }
]);

pn = editor.Panels;
panelViews = pn.addPanel({
  id: 'options'
});
panelViews.get('buttons').add([{
  attributes: {
    title: 'Toggle Rulers'
  },
  context: 'toggle-rulers', //prevents rulers from being toggled when another views-panel button is clicked
  label: `<svg width="18" viewBox="0 0 16 16"><path d="M0 8a.5.5 0 0 1 .5-.5h15a.5.5 0 0 1 0 1H.5A.5.5 0 0 1 0 8z"/><path d="M4 3h8a1 1 0 0 1 1 1v2.5h1V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2.5h1V4a1 1 0 0 1 1-1zM3 9.5H2V12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9.5h-1V12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/></svg>`,
  command: 'ruler-visibility',
  id: 'ruler-visibility'
}]);

editor.on('run:preview', () => editor.stopCommand('ruler-visibility'));

// traitInputAttr = { placeholder: '例子. 輸入文字' };
//     editor.I18n.addMessages({
//     zh: {
//         assetManager: {
//             addButton: '添加圖片',
//             inputPlh: 'http://path/to/the/image.jpg',
//             modalTitle: '選擇圖片',
//             uploadTitle: '點擊或者拖拽圖片上傳',
//         },
//         domComponents: {
//             names: {
//                 '': 'Box',
//                 wrapper: 'Body',
//                 text: '文字',
//                 comment: '評論',
//                 image: '圖片',
//                 video: '視頻',
//                 label: '文本',
//                 link: '超鏈接',
//                 map: '地圖',
//                 tfoot: '表格末尾',
//                 tbody: '表格主體',
//                 thead: '表頭',
//                 table: '表格',
//                 row: '行',
//                 cell: '單元格',
//             },
//         },
//         deviceManager: {
//             device: '設備',
//             devices: {
//                 desktop: '桌面',
//                 tablet: '平板',
//                 mobileLandscape: 'Mobile Landscape',
//                 mobilePortrait: 'Mobile Portrait',
//             },
//         },
//         panels: {
//             buttons: {
//                 titles: {
//                 preview: '預覽',
//                 fullscreen: '全屏',
//                 'sw-visibility': '查看組件',
//                 'export-template': '查看代碼',
//                 'open-sm': '打開樣式管理器',
//                 'open-tm': '設置',
//                 'open-layers': '打開布局管理器',
//                 'open-blocks': '打開塊',
//                 },
//             },
//         },
//         selectorManager: {
//             label: 'Classes',
//             selected: 'Selected',
//             emptyState: '- State -',
//             states: {
//                 hover: 'Hover',
//                 active: 'Click',
//                 'nth-of-type(2n)': 'Even/Odd',
//             },
//         },
//         styleManager: {
//             empty: '設置樣式前選擇請一個元素',
//             layer: '層級',
//             fileButton: '圖片',
//             sectors: {
//                 general: '常規',
//                 layout: '布局',
//                 typography: '版式',
//                 decorations: '裝飾',
//                 extra: '擴展',
//                 flex: '盒子模型',
//                 dimension: '尺寸',
//             },
//             // The core library generates the name by their `property` name
//             properties: {
//                 // float: 'Float',
//             },
//         },
//     }
// });
