
<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$name}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div id="{{$name}}" {!! $attributes !!}></div>

        <input type="hidden" id="{{$id}}_input" name="{{$name}}" value="{{ old($column, $value) }}" />
        @include('admin::form.help-block')

    </div>
</div>
