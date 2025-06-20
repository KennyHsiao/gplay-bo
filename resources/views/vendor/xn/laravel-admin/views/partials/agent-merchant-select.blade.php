<li>
    <select name="merchant" id="merchant" class="header-merchant">
        @if (isset(Admin::user()->merchants()[0]['merchants']))
            @php
                $merchants = collect(Admin::user()->merchants())->filter(function ($item) {
                    return count($item['merchants']) > 0;
                });
            @endphp
            @forelse ($merchants as $item)
                <optgroup label="{{$item['name']}}">
                @foreach ($item['merchants'] as $m)
                    @if($m['code'] == session('merchant_code'))
                        <option value="{{$m['code']}}" selected>{{$m['name']}}</option>
                    @else
                        <option value="{{$m['code']}}">{{$m['name']}}</option>
                    @endif
                @endforeach
                </optgroup>
            @empty
                <option value="-">- Empty -</option>
            @endforelse()
        @else
            @forelse (Admin::user()->merchants() as $item)
                @if($item['code'] == session('merchant_code'))
                    <option value="{{$item['code']}}" selected>{{$item['name']}}</option>
                @else
                    <option value="{{$item['code']}}">{{$item['name']}}</option>
                @endif
            @empty
                <option value="-">- Empty -</option>
            @endforelse()
        @endif
    </select>
</li>
