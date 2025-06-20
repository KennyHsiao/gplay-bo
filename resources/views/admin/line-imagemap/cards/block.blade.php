<div class="form-horizontal card box" style="position: relative; padding-right: 50px; height: 150px;">
    <input type="hidden" name="id" data-field="id">
    <input type="hidden" name="status" data-field="status">
    <input type="hidden" name="coords" data-field="coords">
    <select name="img_shape" class="img_shape" style="display:none;">
        <option value="rect">rectangle</option>
    </select>
    <div style="position: absolute; top: 60px; left: 5px;">
        <input type="radio" name="img_active" class="img_active" id="img_active">
        <input type="text"  name="img_id" class="img_id" readonly="1" style="display:inline; width:30px;"/>
    </div>
    <div class="group custom-select">
        <select class="inputMaterial" name="type" data-field="type">
            <option value></option>
            <option value="message">訊息</option>
            <option value="postback">回傳</option>
            <option value="uri">網址</option>
        </select>
        <span class="highlight"></span>
        <span class="bar"></span>
        <label>選擇類型</label>
    </div>
    <div class="group">      
      <input class="inputMaterial" type="text" name="message" data-field="message" maxlength="255">
      <span class="highlight"></span>
      <span class="bar"></span>
      <label>訊息</label>
    </div>
    <a href="javascript:;" onclick="setTimeout(function(){mapEditor.removeArea(mapEditor.currentid)},200)" class="btn btn-danger pull-right" style="position: absolute; top: 60px; right: 5px;">
        <i class="fa fa-trash"></i>
    </a>
</div>