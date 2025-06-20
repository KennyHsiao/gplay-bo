<div class="form-horizontal card box" style="position: relative; padding-right: 50px; height: 200px;">
    <input type="hidden" name="id" data-field="id">
    <input type="hidden" name="status" data-field="status">
    <input type="hidden" name="coords" data-field="coords">
    <select name="img_shape" class="img_shape" style="display:none;">
        <option value="rect">rectangle</option>
    </select>
    <div style="position: absolute; top: 80px; left: 5px;">
        <input type="radio" name="img_active" class="img_active" id="img_active">
        <input type="text"  name="img_id" class="img_id" readonly="1" style="display:inline; width:30px;"/>
    </div>
    <div class="group custom-select">
        <select class="inputMaterial" name="action_type" data-field="action_type">
            <option value></option>
            @foreach($menu_options as $key => $value)
                <option value="{{$key}}">{{$key}}</option>
            @endforeach
        </select>
        <span class="highlight"></span>
        <span class="bar"></span>
        <label>選單類型</label>
    </div>
    <div class="group custom-select">
        <select class="inputMaterial" name="action_attr" data-field="action_attr">
        {{--  --}}
        </select>
        <span class="highlight"></span>
        <span class="bar"></span>
        <label>選擇功能</label>
    </div>
    <div class="group custom-select select">
        <select class="inputMaterial" name="action_uri" data-field="action_uri">
        {{--  --}}
        </select>
        <span class="highlight"></span>
        <span class="bar"></span>
        <label>選項</label>
    </div>
    <div class="group custom-select input" style="display:none; z-index:999;">
        <textarea class="inputMaterial" name="action_uri" data-field="action_uri" cols="30" rows="1"></textarea>
        <span class="highlight"></span>
        <span class="bar"></span>
        <label>文字</label>
    </div>
    <a href="javascript:;" onclick="setTimeout(function(){RemoveMenu(mapEditor.currentid)},200)" class="btn btn-danger pull-right" style="position: absolute; top: 80px; right: 5px;">
        <i class="fa fa-trash"></i>
    </a>
</div>
