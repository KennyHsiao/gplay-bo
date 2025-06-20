<div class="form-group {!! !$errors->has($label) ?: 'has-error' !!}">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <select class="form-control {{$class}}" style="width: 100%; display:none;" name="{{$name}}[]" multiple="multiple" data-placeholder="{{ $placeholder }}" {!! $attributes !!} id="{{$id}}">
            @foreach($options as $select => $option)
                <option value="{{$select}}" {{  in_array($select, (array)old($column, $value)) ?'selected':'' }}>{{$option}}</option>
            @endforeach
        </select>
        <div id="{{$id}}_tree"></div>
        <input type="hidden" name="{{$id}}[]">
        @include('admin::form.help-block')
    </div>
</div>
