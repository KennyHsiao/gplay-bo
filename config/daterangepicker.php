<?php

return [

    'default' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 7
        ],
        'ranges' => [
            'filterdaterangepicker.today' => [
                'method' => 'day',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.yesterday' => [
                'method' => 'day',
                'start' => -1,
                'end' => -1
            ],
            'filterdaterangepicker.3days' => [
                'method' => 'day',
                'start' => -2,
                'end' => 0
            ],
            'filterdaterangepicker.7days' =>[
                'method' => 'day',
                'start' => -6,
                'end' => 0
            ]
        ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1
        ]
    ],
    'default30' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 32
        ],
        'ranges' => [
            'filterdaterangepicker.today' => [
                'method' => 'day',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.yesterday' => [
                'method' => 'day',
                'start' => -1,
                'end' => -1
            ],
            'filterdaterangepicker.3days' => [
                'method' => 'day',
                'start' => -2,
                'end' => 0
            ],
            'filterdaterangepicker.7days' =>[
                'method' => 'day',
                'start' => -6,
                'end' => 0
            ],
            'filterdaterangepicker.week' => [
                'method' => 'week',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.lastweek' => [
                'method' => 'week',
                'start' => -1,
                'end' => -1
            ],
            'filterdaterangepicker.month' => [
                'method' => 'month',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.lastmonth' => [
                'method' => 'month',
                'start' => -1,
                'end' => -1
            ],
        ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1
        ]
    ],
    'report' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 31
        ],
        'timePickerMinutes' => false,
        'timePickerSeconds' => false,
        'ranges' => [
            'filterdaterangepicker.today' => [
                'method' => 'day',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.yesterday' => [
                'method' => 'day',
                'start' => -1,
                'end' => -1
            ],
            'filterdaterangepicker.week' => [
                'method' => 'week',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.lastweek' => [
                'method' => 'week',
                'start' => -1,
                'end' => -1
            ],
            'filterdaterangepicker.month' => [
                'method' => 'month',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.lastmonth' => [
                'method' => 'month',
                'start' => -1,
                'end' => -1
            ],
        ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1,
            'format' => 'YYYY-MM-DD HH'
        ]
    ],
    'rtp' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 91
        ],
        'timePickerMinutes' => false,
        'timePickerSeconds' => false,
        'ranges' => [
            'filterdaterangepicker.3month' => [
                'method' => 'day',
                'start' => -89,
                'end' => 0
            ],
            'filterdaterangepicker.today' => [
                'method' => 'day',
                'start' => 0,
                'end' => 0
            ],
        ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1,
            'format' => 'YYYY-MM-DD HH'
        ]
    ],
    'opposing' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 1,
        ],
        'timePickerMinutes' => false,
        'timePickerSeconds' => false,
        // 'ranges' => [
        //     'filterdaterangepicker.today' => [
        //         'method' => 'day',
        //         'start' => 0,
        //         'end' => 0
        //     ],
        // ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1,
            'format' => 'YYYY-MM-DD HH'
        ]
    ],
    'member_login' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 7
        ],
        'timePickerHours' => false,
        'timePickerMinutes' => false,
        'timePickerSeconds' => false,
        'ranges' => [
            'filterdaterangepicker.week' => [
                'method' => 'week',
                'start' => 0,
                'end' => 0
            ],
            'filterdaterangepicker.lastweek' => [
                'method' => 'week',
                'start' => -1,
                'end' => -1
            ],
        ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1,
            'format' => 'YYYY-MM-DD'
        ]
    ],
    'retention' => [
        'startDate' => [
            'method' => 'day',
            'start' => 0
        ],
        'endDate' => [
            'method' => 'day',
            'end' => 0
        ],
        'maxDate' => [
            'method' => 'day',
            'end' => 0
        ],
        "maxSpan" => [
            "days" => 31
        ],
        'timePickerHours' => false,
        'timePickerMinutes' => false,
        'timePickerSeconds' => false,
        'ranges' => [
            '1日' => [
                'method' => 'day',
                'start' => -2,
                'end' => -1
            ],
            '3日' => [
                'method' => 'day',
                'start' => -4,
                'end' => -1
            ],
            '7日' => [
                'method' => 'day',
                'start' => -8,
                'end' => -1
            ],
            '15日' => [
                'method' => 'day',
                'start' => -16,
                'end' => -1
            ],
            '30日' => [
                'method' => 'day',
                'start' => -31,
                'end' => -1
            ],
        ],
        'locale' => [
            'applyLabel' => 'filterdaterangepicker.apply',
            'cancelLabel' => 'filterdaterangepicker.cancel',
            'fromLabel' => 'filterdaterangepicker.from',
            'toLabel' => 'filterdaterangepicker.to',
            'customRangeLabel' => 'filterdaterangepicker.customRange',
            'daysOfWeek' => 'filterdaterangepicker.daysOfWeek',
            'monthNames' => 'filterdaterangepicker.monthNames',
            'firstDay' => 1,
            'format' => 'YYYY-MM-DD'
        ]
    ],
];
