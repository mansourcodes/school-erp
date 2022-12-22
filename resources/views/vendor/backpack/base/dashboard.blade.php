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
            'type' => 'div',
            'class' => 'row',
            'content' => [
                // widgets start
                [
                    'type' => 'card',
                    'wrapper' => ['class' => 'col-sm-12 col-md-4'], // optional
                    'class' => 'card bg-primary text-white', // optional
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
                    'class' => 'card bg-primary text-white', // optional
                    'content' => [
                        // 'header' => 'Some card title', // optional
                        'body' => "<a class='h1 d-block py-5 m-0 text-white' href='" . backpack_url('payment') . "' ><i class='nav-icon la la-file-invoice-dollar'></i>" . trans('account.payments') . '</a>',
                    ],
                ],
            ], // widgets end
        ],
    ];
@endphp

@section('content')
@endsection
