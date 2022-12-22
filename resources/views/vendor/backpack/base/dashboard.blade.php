<!-- s -->
@extends(backpack_view('blank'))

@php
    $widgets['before_content'] = [
        [
            'type' => 'jumbotron',
            'heading' => trans('backpack::base.welcome'),
            // 'content' => trans('backpack::base.use_sidebar'),
            // 'button_link' => backpack_url('logout'),
            // 'button_text' => trans('backpack::base.logout'),
        ],
        [
            'type' => 'card', // the kind of widget to show
            'class' => '', // optional
            'content' => [
                'body' => '<h3>إختصارات</h3>',
            ], // the content of that widget (some are string, some are array)
        ],
        [
            'type' => 'div',
            'class' => 'row',
            'content' => [
                // widgets start
                [
                    'type' => 'card',
                    'wrapper' => ['class' => 'col-sm-12 col-md-4'], // optional
                    'class' => 'card bg-cyan text-white', // optional
                    'content' => [
                        // 'header' => 'Some card title', // optional
                        'body' => "<a class='h1 d-block py-5 m-0 text-white' href='" . backpack_url('course') . "' ><i class='nav-icon la la-campground'></i>" . trans('course.courses') . '</a>',
                    ],
                ],
                [
                    'type' => 'card',
                    'wrapper' => ['class' => 'col-sm-12 col-md-4'], // optional
                    'class' => 'card bg-primary text-white', // optional
                    'content' => [
                        // 'header' => 'Some card title', // optional
                        'body' => "<a class='h1 d-block py-5 m-0 text-white' href='" . backpack_url('student') . "' ><i class='nav-icon la la-user-graduate'></i>" . trans('student.students') . '</a>',
                    ],
                ],
                [
                    'type' => 'card',
                    'wrapper' => ['class' => 'col-sm-12 col-md-4'], // optional
                    'class' => 'card bg-gray-400 text-white', // optional
                    'content' => [
                        // 'header' => 'Some card title', // optional
                        'body' => "<a class='h1 d-block py-5 m-0 text-dark' href='" . backpack_url('payment') . "' ><i class='nav-icon la la-file-invoice-dollar'></i>" . trans('account.payments') . '</a>',
                    ],
                ],
                [
                    'type' => 'card',
                    'wrapper' => ['class' => 'col-sm-12 col-md-4'], // optional
                    'class' => 'card bg-green text-white', // optional
                    'content' => [
                        // 'header' => 'Some card title', // optional
                        'body' => "<a class='h1 d-block py-5 m-0 text-white' href='" . backpack_url('add_attend_by_date') . "' ><i class='nav-icon la la-check-square'></i>" . trans('attend.add_attend_w_date') . '</a>',
                    ],
                ],
            ], // widgets end
        ],
    
        [
            'type' => 'card', // the kind of widget to show
            'class' => '', // optional
            'content' => [
                'body' => '<h3>إحصائيات</h3>',
            ], // the content of that widget (some are string, some are array)
        ],
    
        [
            'type' => 'div',
            'class' => 'row',
            'content' => [
                // widgets start
                [
                    'type' => 'progress_white',
                    'class' => 'card mb-2',
                    'value' => '11.456',
                    'description' => 'عدد الطلاب',
                    'progress' => 11, // integer
                    'progressClass' => 'progress-bar bg-primary',
                    'hint' => 'ارقام غير صحيحة مجرد مثال',
                ],
            ], // widgets end
        ],
    ];
@endphp

@section('content')
@endsection
