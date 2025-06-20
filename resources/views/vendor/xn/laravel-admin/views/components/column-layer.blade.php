<span class="grid-layer" data-key="{{ $key }}" data-title="{{ $value }}">
   <a href="javascript:void(0)"><i class="fa fa-clone"></i>&nbsp;&nbsp;{!! $value !!}</a>
</span>

<script>
    $( ".grid-layer" ).bind( "click", function() {
        var opt = {!! json_encode($layerConfig) !!};
        var config = Object.assign({
            type: 1,
            id: $(this).attr('data-key'),
            title: $(this).attr('data-title') || ' ',
            shade: 0,
            maxmin: true,
            zIndex: layer.zIndex,
            success: function(layero, index){
                layer.setTop(layero);
                layer.escIndex = layer.escIndex || [];
                layer.escIndex.unshift(index);
                layero.on('mousedown', function(){
                    var _index = layer.escIndex.indexOf(index);
                    if(_index !== -1){
                        layer.escIndex.splice(_index, 1);
                    }
                    layer.escIndex.unshift(index);
                });
            },
            end: function(){
                if(typeof layer.escIndex === 'object'){
                    layer.escIndex.splice(0, 1);
                }
            }
        }, opt)

        layer.open(config);
    });
</script>
