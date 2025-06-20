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
            <input type="file" name="menu_image" id="menu_image" style="display:none;" onchange="onFileSelected(event);">
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
                        <label for="menu_title">選單名稱</label>
                        <input type="text" name="menu_title" id="menu_title" class="form-control" style="display:inline; width:200px;">
                    </div>
                    <div class="form-group">
                        <label for="merchant_code">LINEBot</label>
                        <select id="merchant_code" class="form-control" name="merchant_code" style="display:inline; width:200px;">
                            @forelse ($line_bot as $k => $v)
                                <option value="{{$k}}" {{$k === $menu['merchant_code']??"" ?'selected=1':''}}>{{$v}}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <label style="color:red;">圖片尺寸：2500x1686 或 2500x843</label>
                    <div class="btn-group pull-right">
                        <div class="btn-group pull-right" style="margin-right: 10px">
                            <a href="{{route('line-menu.index')}}" class="btn btn-sm btn-default"><i class="fa fa-list"></i>&nbsp;{{__('admin.list')}}</a>
                        </div>
                    </div>
                </div>
                <div class="box-body inner-content" style="height:75vh;overflow-x:hidden;overflow-y:scroll;display:flex;">
                    <div style="display:flex;" class="col-sm-8">
                        <div id="pic_container"></div>
                    </div>
                    <div id="form_container" class="col-sm-4" style="clear: both;">
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
        {{-- @include('admin.line-menu.cards.block') --}}
        @include('admin.line-menu.cards.multipurpose-block')
    </template>
    <link rel="stylesheet" href="{{ admin_asset("/vendor/libs/ImageMapEditor/line-rich-menu.css") }}" type="text/css">
    <!--[if gte IE 6]>
        <script language="javascript" type="text/javascript" src="{{ admin_asset("/vendor/libs/ImageMapEditor/ext/excanvas.js") }}"></script>
    <![endif]-->
    <script>
        //
        function RemoveMenu(id) {
            xconfirm(function(){
                mapEditor.removeArea(id);
            });
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
        function onFileSelected(event) {
            var selectedFile = event.target.files[0];
            var reader = new FileReader();
            reader.onload = function(event) {
                validImage(event.target.result, function(img, width, height){
                    // if (width === 2500 && (height === 1686 || height === 843)) {
                    if (((width >= 800 && width <= 2500) && height >= 250)&&((width/parseFloat(height)) > 1.45)) {
                        $('#image_width').val(width);
                        $('#image_height').val(height);
                        gui_loadImage(img, width, height);
                        setTimeout(function(){
                            document.getElementById('dd_zoom').value = 0.25;
                            document.getElementById('dd_zoom').onchange();
                        }, 200);
                    } else {
                        swalDialog("圖片尺寸：寬 800～2500 或 高 250～. ", true);
                    }
                });
            };
            if (selectedFile) {
                reader.readAsDataURL(selectedFile);
            }
        }
        async('{{ admin_asset("/vendor/libs/ImageMapEditor/imgmap.js") }}', function(){
            async('{{ admin_asset("/vendor/libs/ImageMapEditor/line-rich-menu.js") }}', function() {
     //
                var menuOptions = [];
                // binding mulitpurpose
                // var menuOptions = JSON.parse('{!! json_encode($menu_options) !!}');
                $('body').off('change', '#merchant_code');
                $('body').on('change', '#merchant_code', function(){
                    fetch("{{route('line-menu.options')}}?code="+$(this).val())
                    .then(function(response) {
                        return response.json();
                    }).then(function(myJson) {
                        menuOptions = myJson;
                        init();
                    });
                });
                // MENU TYPE
                $('body').off('change', '[data-field="menu_type"]');
                $('body').on('change', '[data-field="menu_type"]', function(e){
                    var menuAttrSelectField = $(this).parents('.card.box').find('select[data-field="menu_attr"]')[0];
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
                        $(this).parents('.card.box').find('.group.input').find('textarea').attr('name', f_name);
                        $(this).parents('.card.box').find('.group.select').find('select').removeAttr('name')
                        $(this).parents('.card.box').find('.group.input').fadeIn();
                        $(this).parents('.card.box').find('.group.select').fadeOut();
                    } else {
                        var f_name = $(this).parents('.card.box').find('.group.input').find('textarea').attr('name');
                        $(this).parents('.card.box').find('.group.input').find('textarea').removeAttr('name');
                        $(this).parents('.card.box').find('.group.select').find('select').attr('name', f_name)
                        $(this).parents('.card.box').find('.group.input').fadeOut();
                        $(this).parents('.card.box').find('.group.select').fadeIn();
                    }

                });
                // MENU ATTRIBUTE
                $('body').off('change', '[data-field="menu_attr"]');
                $('body').on('change', '[data-field="menu_attr"]', function(e){
                    var menuType = $(this).parents('.card.box').find('[data-field="menu_type"]').val();
                    var menuUriSelectField = $(this).parents('.card.box').find('select[data-field="menu_uri"]')[0];
                    menuUriSelectField.innerHTML = "";
                    var menuOpt = menuOptions[menuType] === undefined ? menuType : menuOptions[menuType][e.target.value];
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
                    $(props[uid]).find('input, select').each(function(idx, item){
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
                //
                function init() {
                    // init from db
                    if("{{$menu['menu_image']}}") {
                        $("#menu_title").val("{{$menu['menu_title']}}");
                        $('#image_width').val("{{$menu['image_width']}}");
                        $('#image_height').val("{{$menu['image_height']}}");
                        var img = "{{$image_prf . $menu['menu_image']}}";
                        gui_loadImage(img, "{{$menu['image_width']}}", "{{$menu['image_height']}}");
                        var areas = JSON.parse('{!!Utils::jsonStringify($menu->actions()->get())!!}');
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
                            document.getElementById('dd_zoom').value = 0.25;
                            document.getElementById('dd_zoom').onchange();
                        }, 200);
                    }
                }
                $(function(){
                    $('#merchant_code').trigger('change');
                    // override default function
                    mapEditor.config.mode = 'editor2';
                    mapEditor.config.maxArea = 20;
                    mapEditor.config.custom_callbacks.onAddArea = newComponent;
                    mapEditor.config.custom_callbacks.onRemoveArea = removeArea;
                    //
                    $('body').off('click', '.btn-loading-image');
                    $('body').on('click', '.btn-loading-image', function(){
                        $('#menu_image').click();
                    });
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
