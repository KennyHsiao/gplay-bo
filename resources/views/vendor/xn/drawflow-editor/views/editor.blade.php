<div class="drawflow-wrapper">
    <div class="col">
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="facebook">
        <i class="fab fa-facebook"></i><span> Facebook</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="slack">
        <i class="fab fa-slack"></i><span> Slack recive message</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="github">
        <i class="fab fa-github"></i><span> Github Star</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="telegram">
        <i class="fab fa-telegram"></i><span> Telegram send message</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="aws">
        <i class="fab fa-aws"></i><span> AWS</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="log">
        <i class="fas fa-file-signature"></i><span> File Log</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="google">
        <i class="fab fa-google-drive"></i><span> Google Drive save</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="email">
        <i class="fas fa-at"></i><span> Email send</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="template">
        <i class="fas fa-code"></i><span> Template</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="multiple">
        <i class="fas fa-code-branch"></i><span> Multiple inputs/outputs</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="personalized">
        <i class="fas fa-fill"></i><span> Personalized</span>
      </div>
      <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="dbclick">
        <i class="fas fa-mouse"></i><span> DBClick!</span>
      </div>


    </div>
    <div class="col-right">
        <div class="drawflow" id="{{$name}}" {!! $attributes !!} ondrop="drop(event)" ondragover="allowDrop(event)">
            <div class="btn-export" onclick="Swal.fire({ title: 'Export',html: '<pre><code>'+JSON.stringify(editor.export(), null,4)+'</code></pre>'})">Export</div>
            <div class="btn-clear" onclick="editor.clearModuleSelected()">Clear</div>
            <div class="btn-lock">
                <i id="lock" class="fas fa-lock" onclick="editor.editor_mode='fixed'; changeMode('lock');"></i>
                <i id="unlock" class="fas fa-lock-open" onclick="editor.editor_mode='edit'; changeMode('unlock');" style="display:none;"></i>
            </div>
            <div class="bar-zoom">
                <i class="fas fa-search-minus" onclick="editor.zoom_out()"></i>
                <i class="fas fa-search" onclick="editor.zoom_reset()"></i>
                <i class="fas fa-search-plus" onclick="editor.zoom_in()"></i>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="{{$id}}_input" name="{{$name}}" value="{{ old($column, $value) }}" />

<style>
    #{{$name}} {
        border: 3px solid #444;
    }
    :root {
        --border-color: #cacaca;
        --background-color: #ffffff;

        --background-box-title: #f7f7f7;
    }

    .them-edit-link {
        position: absolute;
        top: 10px;
        right: 100px;
        color: black;
        font-size: 40px;
    }
    .them-edit-link a {
        text-decoration: none;
    }

    .github-link{
        position: absolute;
        top: 10px;
        right: 20px;
        color: black;
    }

    .drawflow-wrapper {
        width: 100%;
        height: calc(100vh - 67px);
        display: flex;
    }

    .col {
        overflow: auto;
        width: 300px;
        height: 100%;
        border-right: 1px solid var(--border-color);
    }

    .drag-drawflow {
        line-height: 50px;
        border-bottom: 1px solid var(--border-color);
        padding-left: 20px;
        cursor: move;
        user-select: none;
    }

    .btn-export {
        float: right;
        position: absolute;
        top: 10px;
        right: 10px;
        color: white;
        font-weight: bold;
        border: 1px solid #0e5ba3;
        background: #4ea9ff;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        z-index: 5;
    }

    .btn-clear {
        float: right;
        position: absolute;
        top: 10px;
        right: 85px;
        color: white;
        font-weight: bold;
        border: 1px solid #96015b;
        background: #e3195a;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        z-index: 5;
    }
    .swal-wide{
        width:80% !important;
    }

    .btn-lock {
        float: right;
        position: absolute;
        bottom: 10px;
        right: 140px;
        display: flex;
        font-size: 24px;
        color: white;
        padding: 5px 10px;
        background: #555555;
        border-radius: 4px;
        border-right: 1px solid var(--border-color);
        z-index: 5;
        cursor: pointer;
    }

    .bar-zoom {
        float: right;
        position: absolute;
        bottom: 10px;
        right: 10px;
        display: flex;
        font-size: 24px;
        color: white;
        padding: 5px 10px;
        background: #555555;
        border-radius: 4px;
        border-right: 1px solid var(--border-color);
        z-index: 5;
    }
    .bar-zoom svg {
        cursor: pointer;
        padding-left: 10px;
    }
    .bar-zoom svg:nth-child(1) {
        padding-left: 0px;
    }

    .drawflow {
        position: relative;
        width: calc(100vw - 600px);
        height: calc(100% - 0px);
        top: 0px;
        background: var(--background-color);
        background-size: 25px 25px;
        background-image:
        linear-gradient(to right, #f1f1f1 1px, transparent 1px),
        linear-gradient(to bottom, #f1f1f1 1px, transparent 1px);
    }

    @media only screen and (max-width: 768px) {
        .col {
            width: 50px;
        }
        .col .drag-drawflow span {
            display:none;
        }
        #drawflow {
            width: calc(100vw - 51px);
        }
    }

    /* Editing Drawflow */

    .drawflow .drawflow-node {
        background: var(--background-color);
        border: 1px solid var(--border-color);
        -webkit-box-shadow: 0 2px 15px 2px var(--border-color);
        box-shadow: 0 2px 15px 2px var(--border-color);
        padding: 0px;
        width: 200px;
    }

    .drawflow .drawflow-node.selected  {
        background: white;
        border: 1px solid #4ea9ff;
        -webkit-box-shadow: 0 2px 20px 2px #4ea9ff;
        box-shadow: 0 2px 20px 2px #4ea9ff;
    }

    .drawflow .drawflow-node.selected .title-box {
        color: #22598c;
        /*border-bottom: 1px solid #4ea9ff;*/
    }

    .drawflow .connection .main-path {
        stroke: #4ea9ff;
        stroke-width: 3px;
    }

    .drawflow .drawflow-node .input, .drawflow .drawflow-node .output {
        height: 15px;
        width: 15px;
        border: 2px solid var(--border-color);
    }

    .drawflow .drawflow-node .input:hover, .drawflow .drawflow-node .output:hover {
        background: #4ea9ff;
    }

    .drawflow .drawflow-node .output {
        right: 10px;
    }

    .drawflow .drawflow-node .input {
        left: -10px;
        background: white;
    }

    .drawflow > .drawflow-delete {
        border: 2px solid #43b993;
        background: white;
        color: #43b993;
        -webkit-box-shadow: 0 2px 20px 2px #43b993;
        box-shadow: 0 2px 20px 2px #43b993;
    }

    .drawflow-delete {
        border: 2px solid #4ea9ff;
        background: white;
        color: #4ea9ff;
        -webkit-box-shadow: 0 2px 20px 2px #4ea9ff;
        box-shadow: 0 2px 20px 2px #4ea9ff;
    }

    .drawflow-node .title-box {
        height: 50px;
        line-height: 50px;
        background: var(--background-box-title);
        border-bottom: 1px solid #e9e9e9;
        border-radius: 4px 4px 0px 0px;
        padding-left: 10px;
    }
    .drawflow .title-box svg {
        position: initial;
    }
    .drawflow-node .box {
        padding: 10px 20px 20px 20px;
        font-size: 14px;
        color: #555555;
    }
    .drawflow-node .box p {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .drawflow-node.slack .title-box {
        border-radius: 4px;
    }

    .drawflow-node input, .drawflow-node select, .drawflow-node textarea {
        border-radius: 4px;
        border: 1px solid var(--border-color);
        height: 30px;
        line-height: 30px;
        font-size: 16px;
        width: 158px;
        color: #555555;
    }

    .drawflow-node textarea {
        height: 100px;
    }


    .drawflow-node.personalized {
        background: red;
        height: 200px;
        text-align: center;
        color: white;
    }
    .drawflow-node.personalized .input {
        background: yellow;
    }
    .drawflow-node.personalized .output {
        background: green;
    }

    .drawflow-node.personalized.selected {
        background: blue;
    }

    .drawflow .connection .point {
        stroke: var(--border-color);
        stroke-width: 2;
        fill: white;
        transform: translate(-9999px, -9999px);
    }

    .drawflow .connection .point.selected, .drawflow .connection .point:hover {
        fill: #4ea9ff;
    }


    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 7;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        overflow: auto;
        background-color: rgb(0,0,0);
        background-color: rgba(0,0,0,0.7);
    }

    .modal-content {
        position: relative;
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 400px; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .modal .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor:pointer;
    }

    @media only screen and (max-width: 768px) {
        .modal-content {
            width: 80%;
        }
    }
</style>
