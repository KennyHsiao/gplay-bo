<div class="box grid-box">

    {!! $grid->renderHeader() !!}

    <!-- /.box-header -->
    <div class="box-body table-responsive">
        <div id="{{$grid->tableID}}" style="margin:10px 5px; height: 75vh"></div>
    </div>
    {!! $grid->renderFooter() !!}
    <!-- /.box-body -->
</div>


<script>
    $(function () {
        $("#{{$grid->tableID}}").dxDataGrid({
            showBorders: false,
            dataSource: {
                store: {
                    type: 'odata',
                    url: '/odata/Transactions',
                    beforeSend: function (e) {
                        e.headers = {
                            'OData-Version': '4.0'
                        };
                        e.params["m_code"] = "QN6"
                    },
                    key: 'id',
                    version: 4
                },
                select: [
                    'id',
                    'trans_type',
                    'trace_id',
                    'balance'
                ]
            },
            // editing: {
            //     mode: 'form',
            //     allowUpdating: true,
            //     allowAdding: true,
            //     allowDeleting: true,
            // },
            headerFilter: {
                visible: true,
                allowSearch: true,
            },
            groupPanel: {
                visible: true,
            },
            columns: [
                {
                    dataField: 'id',
                    allowEditing: false,
                },
                'trans_type',
                'trace_id',
                'balance'
            ],
            summary: {
                groupItems: [{
                    column: 'trace_id',
                    summaryType: 'count',
                    alignByColumn: true,
                }, {}, {
                    column: 'balance',
                    summaryType: 'sum',
                    valueFormat: 'currency',
                    alignByColumn: true,
                }],
                totalItems: [{
                    column: 'trace_id',
                    summaryType: 'count',
                }, {
                    column: 'balance',
                    summaryType: 'sum',
                    valueFormat: 'currency',
                }],
            },
        });
    });
</script>
