
<div class="row">
    <div class="{{$viewClass['label']}}"><h4 class="pull-right">{{ $label }}</h4></div>
    <div class="{{$viewClass['field']}}"></div>
</div>

<hr style="margin-top: 0px;">

<div id="has-many-{{$column}}" class="has-many-{{$column}}">

    <div class="has-many-{{$column}}-forms panel-group" id="{{$column}}">

        @foreach($forms as $pk => $form)

            <div class="has-many-{{$column}}-form fields-group">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <a class="box-title" data-widget="collapse">###{{$pk}}###</a>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                            @if($options['allowDelete'])
                            <button type="button" class="btn btn-box-tool remove"><i class="fa fa-times"></i></button>
                            @endif
                        </div>
                    </div>
                    <div class="box-body">
                        @foreach($form->fields() as $field)
                            {!! $field->render() !!}
                        @endforeach
                    </div>
                </div>
            </div>

        @endforeach
    </div>


    <template class="{{$column}}-tpl">
        <div class="has-many-{{$column}}-form fields-group">
            <div class="box box-default">
                <div class="box-header with-border">
                    <a class="box-title" data-widget="collapse">__LA_KEY__</a>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    {!! $template !!}
                </div>
            </div>
        </div>
    </template>

    @if($options['allowCreate'])
    <div class="form-group">
        <label class="{{$viewClass['label']}} control-label"></label>
        <div class="{{$viewClass['field']}}">
            <div class="add btn btn-success btn-sm"><i class="fa fa-save"></i>&nbsp;{{ trans('admin.new') }}</div>
        </div>
    </div>
    @endif

</div>
