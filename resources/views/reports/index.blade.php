<!-- s -->
@extends(backpack_view('blank'))

@php
$widgets['after_content'][] = [
'type' => 'jumbotron',
'heading' => trans('backpack::base.welcome'),
'content' => trans('backpack::base.use_sidebar'),
'button_link' => backpack_url('logout'),
'button_text' => trans('backpack::base.logout'),
];
@endphp

@section('content')


<?php
// TODO: build forms
foreach ($formList as $singleForm) :
?>

    <div class="card">
        <div class="card-header">
            Card actions
            <div class="card-header-actions">
                <a class="card-header-action btn-minimize" href="#" data-toggle="collapse" data-target="#collapse_" aria-expanded="true">
                    <i class="la la-close"></i>
                </a>
            </div>
        </div>
        <div class="collapse show" id="collapse_" style="">
            <form action="" method="GET">
                <div class="card-body">

                    <hr>
                    <div class="btn-group">
                        <button class="btn btn-success" type="button">Print</button>
                        <button class="btn btn-success dropdown-toggle dropdown-toggle-split" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>
                        <div class="dropdown-menu" style=""><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a>
                            <div class="dropdown-divider"></div><a class="dropdown-item" href="#">Separated link</a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>


<?php

endforeach;
?>
@endsection