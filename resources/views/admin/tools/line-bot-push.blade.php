@foreach ($options as $option)
    <a class="btn btn-sm btn-success push-action" id="create-{{$option['type']}}" data-type="{{$option['type']}}" href="{{$option['url']}}">
        {{$option['label']}}
    </a>
@endforeach
