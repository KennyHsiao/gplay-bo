@extends('admin::index')

@section('content')
    <section class="content-header">
        <h1>
            {{ $header ?? trans('admin.title') }}
            <small>{{ $description ?? trans('admin.description') }}</small>
        </h1>
    </section>

    <section class="content">
        @include('admin::partials.alerts')
        @include('admin::partials.exception')
        @include('admin::partials.toastr')

        {{--  content  --}}
        <form id="main_form" method="POST" action="{{$action}}" enctype="multipart/form-data">
            {{csrf_field()}}
            {!!$action_method ?? ''!!}
            <input type="hidden" name="image_width" id="image_width">
            <input type="hidden" name="image_height" id="image_height">
            <input type="file" name="image" id="image" style="display:none;" onchange="onFileSelected(event);">
            <div class="box box-default">
                <div class="box-header with-border">
                    <a class="btn btn-default btn-loading-image">
                        <i class="fa fa-image"></i> 載入圖片
                    </a>
                    <div class="form-group">
                        <label for="dd_zoom">縮放:</label>
                        <select onchange="gui_zoom(this)" id="dd_zoom" class="form-control" style="display:inline; width:100px;">
                            <option value='0.25'>25%</option>
                            <option value='0.5'>50%</option>
                            <option value='1' selected="1">100%</option>
                            <option value='2'>200%</option>
                            <option value='3'>300%</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="title">名稱</label>
                        <input type="text" name="title" id="title" class="form-control" style="display:inline; width:300px;">
                    </div>
                    <label style="color:red;">圖片寬度：1280px 以上</label>
                    <div class="btn-group pull-right">
                        <div class="btn-group pull-right" style="margin-right: 10px">
                            <a href="{{route('line-imagemap.index')}}" class="btn btn-sm btn-default"><i class="fa fa-list"></i>&nbsp;{{__('admin.list')}}</a>
                        </div>
                    </div>
                </div>
                <div class="box-body inner-content" style="height:75vh;overflow-x:hidden;overflow-y:scroll;display:flex;">
                    <div style="display:flex;" class="col-sm-7">
                        <div id="pic_container"></div>
                    </div>
                    <div id="form_container" class="col-sm-5" style="clear: both;">
                        <!-- form elements come here -->
                    </div>
                </div>
                <div class="box-footer">
                    <div class="btn-group pull-right">
                        <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> {{__('admin.submit')}}">{{__('admin.submit')}}</button>
                    </div>
                </div>
            </div>
        </form>
        {{--  end of content  --}}
    </section>
    <template id="card-block">
        {{-- @include('admin.line-imagemap.cards.block') --}}
        @include('admin.line-imagemap.cards.multipurpose-block')
    </template>
    <link rel="stylesheet" href="{{ admin_asset("/vendor/libs/ImageMapEditor/line-rich-menu.css") }}" type="text/css">
    <!--[if gte IE 6]>
        <script language="javascript" type="text/javascript" src="{{ admin_asset("/vendor/libs/ImageMapEditor/ext/excanvas.js") }}"></script>
    <![endif]-->
    <script>
        //
        $(function(){
            // binding mulitpurpose
            var menuOptions = JSON.parse('{!! json_encode($menu_options) !!}');
            // MENU TYPE
            $('body').on('change', '[data-field="action_type"]', function(e){
                var menuAttrSelectField = $(this).parents('.card.box').find('select[data-field="action_attr"]')[0];
                menuAttrSelectField.innerHTML = "";
                var menuOpt = menuOptions[e.target.value];
                if (typeof(menuOpt) == 'object') {
                    for( item in menuOpt) {
                        var option  = document.createElement('option');
                        option.textContent = item;
                        menuAttrSelectField.appendChild(option);
                    }
                } else {
                    var option  = document.createElement('option');
                    option.textContent = menuOpt;
                    menuAttrSelectField.appendChild(option);
                }
                // trigger menu attr
                $(menuAttrSelectField).trigger('change');
                // 切換 選項 select / input
                if ('純文字,關鍵字'.indexOf($(this).val()) !== -1) {
                    var f_name = $(this).parents('.card.box').find('.group.select').find('select').attr('name');
                    $(this).parents('.card.box').find('.group.select').find('select').removeAttr('name');
                    $(this).parents('.card.box').find('.group.input').find('textarea').attr('name', f_name);
                    //
                    $(this).parents('.card.box').find('.group.select').fadeOut();
                    $(this).parents('.card.box').find('.group.input').fadeIn();
                } else {
                    var f_name = $(this).parents('.card.box').find('.group.input').find('textarea').attr('name');
                    $(this).parents('.card.box').find('.group.input').find('textarea').removeAttr('name');
                    $(this).parents('.card.box').find('.group.select').find('select').attr('name', f_name);
                    //
                    $(this).parents('.card.box').find('.group.select').fadeIn();
                    $(this).parents('.card.box').find('.group.input').fadeOut();
                }
            });
            // MENU ATTRIBUTE
            $('body').on('change', '[data-field="action_attr"]', function(e){
                var menuType = $(this).parents('.card.box').find('[data-field="action_type"]').val();
                var menuUriSelectField = $(this).parents('.card.box').find('select[data-field="action_uri"]')[0];
                menuUriSelectField.innerHTML = "";
                var menuOpt = menuOptions[menuType][e.target.value] === undefined ? menuType : menuOptions[menuType][e.target.value];
                if (typeof(menuOpt) == 'object') {
                    for( item in menuOpt) {
                        var option  = document.createElement('option');
                        option.textContent = menuOpt[item];
                        menuUriSelectField.appendChild(option);
                    }
                } else {
                    var option  = document.createElement('option');
                    option.textContent = menuOpt;
                    menuUriSelectField.appendChild(option);
                }
            });
        });
        //
        function onFileSelected(event) {
            var selectedFile = event.target.files[0];
            var reader = new FileReader();
            reader.onload = function(event) {
                validImage(event.target.result, function(img, width, height){
                    if (width >= 1280 ) {
                        $('#image_width').val(width);
                        $('#image_height').val(height);
                        gui_loadImage(img, width, height);
                        setTimeout(function(){
                            document.getElementById('dd_zoom').value = (width>1600?0.25:0.5);
                            document.getElementById('dd_zoom').onchange();
                        }, 200);
                    } else {
                        swalDialog("圖片寬度：至少1280px. ", true);
                    }
                });
            };
            reader.readAsDataURL(selectedFile);
        }

        function validImage(img, callback) {
            var downloadingImage = new Image();
            downloadingImage.onload = function() {
                if(callback) {
                    callback(img, this.width, this.height)
                }
            }
            downloadingImage.src = img;
        }
        //
        function RemoveMenu(id) {
            xconfirm(function(){
                mapEditor.removeArea(id);
            });
        }
        /**
        *	Called from imgmap when an area was removed.
        */
        function removeArea(id) {
            if (props[id]) {
                //shall we leave the last one?
                $(props[id]).fadeOut('fast', function(){
                    var status = $(this).find('[name*="status"]'),
                        uid = $(this).find('[name*="id"]');
                    if(status.val()==='new') {
                        var pprops = props[id].parentNode;
                        pprops.removeChild(props[id]);
                        var lastid = pprops.lastChild.aid;
                        props[id] = null;
                        try {
                            gui_row_select(lastid, true);
                            mapEditor.currentid = lastid;
                        } catch (err) {
                            // alert('noparent');
                        }
                    } else {
                        Array.from($(this).find('[name*="form"]')).forEach(function(input){
                            $oldName = $(input).attr('name');
                            $(input).attr('name', $oldName.replace(id, '_' + uid.val()))
                        });
                        status.val('del');
                        var pprops = props[id].parentNode;
                        if (pprops) {
                            var lastid = pprops.lastChild.aid;
                            try {
                                gui_row_select(lastid, true);
                                mapEditor.currentid = lastid;
                            } catch (err) {
                                // alert('noparent');
                            }
                        }
                    }
                });
            }
        }
        // create new component by card type
        function newComponent(id, data) {
            var uid = id;
            var row = document.getElementById("card-block").content.cloneNode(true);
            props[uid] = document.createElement('DIV');
            document.getElementById('form_container').appendChild(props[id]);
            props[uid].appendChild(row);
            $(props[uid]).find('textarea, input, select').each(function(idx, item){
                var f_name = $(item).attr('name');
                if ('img_active'.indexOf(f_name) !== -1) {
                    $(item).attr('id', 'img_active_' + uid);
                }
                if ('img_id'.indexOf(f_name) !== -1) {
                    $(item).attr('value', uid);
                }
                $(item).attr('name', 'form['+uid+']['+f_name+']');
                if (f_name === 'status') {
                    $(item).val('new');
                }
                if (f_name === 'menu_type') {
                    $('[name="form['+uid+']['+f_name+']"]').val('商品列表');
                    $('[name="form['+uid+']['+f_name+']"]').trigger('change');
                }
            });
            props[uid].id = 'img_area_' + uid;
            props[uid].aid = uid;
            props[uid].className = 'img_area';
            //hook ROW event handlers
            mapEditor.addEvent(props[uid], 'mouseover', gui_row_mouseover);
            mapEditor.addEvent(props[uid], 'mouseout', gui_row_mouseout);
            mapEditor.addEvent(props[uid], 'click', gui_row_click);
            //set shape as nextshape if set
            if (mapEditor.nextShape) { props[uid].getElementsByTagName('select')[0].value = mapEditor.nextShape; }
            //alert(this.props[id].parentNode.innerHTML);
            gui_row_select(uid, true);
        }
        async('{{ admin_asset("/vendor/libs/ImageMapEditor/imgmap.js") }}', function(){
            async('{{ admin_asset("/vendor/libs/ImageMapEditor/line-rich-menu.js") }}', function() {
                //
                $(function(){
                    // override default function
                    mapEditor.config.mode = 'editor2';
                    mapEditor.config.maxArea = 20; // 官方支援 50
                    mapEditor.config.custom_callbacks.onAddArea = newComponent;
                    mapEditor.config.custom_callbacks.onRemoveArea = removeArea;
                    //
                    $('body').off('click', '.btn-loading-image');
                    $('body').on('click', '.btn-loading-image', function(){
                        $('#image').click();
                    });
                    // init from db
                    if("{{$map['image']}}") {
                        $("#title").val("{{$map['title']}}");
                        $('#image_width').val("{{$map['image_width']}}");
                        $('#image_height').val("{{$map['image_height']}}");
                        var img = "{{$image_prf . $map['image'].'/1040'}}";
                        gui_loadImage(img, "{{$map['image_width']}}", "{{$map['image_height']}}");
                        var areas = JSON.parse('{!!Utils::jsonStringify($map->actions()->get())!!}');
                        $.each(areas, function(pidx, data){
                            mapEditor.addNewArea();
                            mapEditor.initArea(pidx, mapEditor.nextShape);
                            mapEditor._recalculate(pidx, data['coords']);
                            $('#img_area_'+pidx).find('input, select').each(function(idx, item){
                                var f_name = $(item).data('field');
                                var item = $('[name="form['+pidx+']['+f_name+']"]');
                                if ($(item).hasClass('base64')) {
                                    $(item).val(data[f_name]?Base64.decode(data[f_name]):'');
                                } else {
                                    $(item).val(data[f_name]?data[f_name]:'');
                                    $(item).trigger('change');
                                }
                                if (f_name === 'status') {
                                    $(item).val(data[f_name]?data[f_name]:'old');
                                }
                            });
                        });
                        setTimeout(function(){
                            document.getElementById('dd_zoom').value = 0.5;
                            document.getElementById('dd_zoom').onchange();
                        }, 200);
                    }
                    // submit form
                    $('#main_form').submit(function(e){
                        $('button[type="submit"]').attr("disabled", true);
                        swalDialog("資料處理中");
                        $('.form-group').removeClass('has-error');
                        $('.control-label[for="inputError"]').remove();
                        e.preventDefault();
                        //
                        $.ajax({
                            url: $(this).attr('action'),
                            method: 'POST',
                            cache: false,
                            data: new FormData(this),
                            processData: false,
                            contentType: false,
                            success: function(resp) {
                                if (resp.status) {
                                    $.pjax({url: resp.return, container: '#pjax-container'});
                                    swal.close();
                                }
                            },
                            error: function(resp){
                                $('button[type="submit"]').attr("disabled", false);
                                // swal.close();
                                if(resp.responseJSON) {
                                    /*
                                    $.each(resp.responseJSON, function(idx, item) {
                                        var _idx = idx.split('.'),
                                            _tail = idx.split('.').slice(1).map(function(item){return '['+item+']' }).join('');
                                        idx = _idx[0] + _tail;
                                        item = item[0].split(' ');
                                        delete item[1];
                                        item = item.join('');
                                        var input = $("[name='"+idx+"']"),
                                            error_msg = "<label class='control-label' for='inputError'>&nbsp;&nbsp;<i class='fa fa-times-circle-o'></i>"+item+"</label>";
                                        input.closest('.form-group').toggleClass('has-error');
                                        input.closest('.form-group').find('label').after(error_msg);
                                    });
                                    */
                                    var errors = [];
                                    $.each(resp.responseJSON, function(k, v){
                                        var r = errors.filter(function($item){
                                            return $item == v[0];
                                        })
                                        if(!r[0]) {
                                            errors.push(v[0]);
                                        }
                                    });
                                    swalDialog(errors.join("<br>"), true);
                                }
                            }
                        });
                    });
                });
            });
        });
    </script>
@endsection
