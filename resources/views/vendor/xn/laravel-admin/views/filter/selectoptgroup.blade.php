<select class="form-control {{ $class }}" name="{{$name}}" style="width: 100%;">
    @foreach($options as $select => $option)
        @if (isset($option[$relationName]))
            <optgroup label="{{$option['name']}}">
                @foreach ($option[$relationName] as $m)
                    <option value="{{$m[$selectKeyValue['value']]}}" {{ (string)$m[$selectKeyValue['value']] === (string)request($name, $value) ?'selected':'' }}>{{$m[$selectKeyValue['text']]}}</option>
                @endforeach
            </optgroup>
        @else
            <option value="{{$option[$selectKeyValue['value']]}}" {{ (string)$select === (string)request($name, $value) ?'selected':'' }}>{{$option[$selectKeyValue['text']]}}</option>
        @endif
    @endforeach
</select>
