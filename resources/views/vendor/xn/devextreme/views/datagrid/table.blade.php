<div class="box grid-box dx-viewport">
    {!! $grid->renderHeader() !!}

    <!-- /.box-header -->
    <div class="box-body table-responsive">
        <div id="{{$grid->tableID}}" style="height: 75vh"></div>
    </div>
    {!! $grid->renderFooter() !!}
    <!-- /.box-body -->
</div>
