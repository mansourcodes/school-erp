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
foreach ($formList as $singleForm) :
?>

    <div class="card">
        <div class="card-header">
            <?php echo $singleForm->getTitle(); ?>
            <div class="card-header-actions">
                <a class="card-header-action btn-minimize" href="#" data-toggle="collapse" data-target="#collapse_<?php echo $singleForm->id; ?>" aria-expanded="true">
                    <i class="la la-close"></i>
                </a>
            </div>
        </div>
        <div class="collapse show" id="collapse_<?php echo $singleForm->id; ?>" style="">
            <?php echo $singleForm->openForm(); ?>
            <div class="card-body">
                <div class="row">
                    <?php echo $singleForm->getFields(); ?>
                </div>

                <div class="col-xs-4">
                    <h3>With<br>Ajax-Bootstrap-Select</h3>
                    <select id="ajax-select" class="selectpicker with-ajax" data-live-search="true"></select>
                </div>



                <hr>
                <div class="btn-group pull-end">
                    <button class="btn btn-success" type="button"><i class="la la-print "></i></button>
                    <button class="btn btn-success dropdown-toggle dropdown-toggle-split" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>
                    <div class="dropdown-menu"><a class="dropdown-item" href="#">Action</a><a class="dropdown-item" href="#">Another action</a><a class="dropdown-item" href="#">Something else here</a>
                        <div class="dropdown-divider"></div><a class="dropdown-item" href="#">Separated link</a>
                    </div>
                </div>

            </div>
            <?php echo $singleForm->closeForm(); ?>

        </div>
    </div>


<?php

endforeach;
?>
@endsection


@section('after_scripts')
<style>
    .bootstrap-select {
        width: 100% !important;
    }
</style>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ajax-bootstrap-select/1.3.8/css/ajax-bootstrap-select.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ajax-bootstrap-select/1.3.8/js/ajax-bootstrap-select.min.js"></script>
<script>
    jQuery(document).ready(function() {



        var options = {
            values: "a, b, c",
            ajax: {
                url: "http://localhost/api/student",
                type: "GET",
                dataType: "json",
                // Use "{@{{q}}}" as a placeholder and Ajax Bootstrap Select will
                // automatically replace it with the value of the search query.
                data: {
                    q: "{@{{q}}}"
                }
            },
            locale: {
                emptyTitle: "Select and Begin Typing"
            },
            log: 3,
            preprocessData: function(data) {
                var i,
                    l = data.length,
                    array = [];
                if (l) {
                    for (i = 0; i < l; i++) {
                        array.push(
                            jQuery.extend(true, data[i], {
                                text: data[i].Name,
                                value: data[i].Email,
                                data: {
                                    subtext: data[i].Email
                                }
                            })
                        );
                    }
                }
                // You must always return a valid array when processing data. The
                // data argument passed is a clone and cannot be modified directly.
                return array;
            }
        };

        jQuery(".selectpicker")
            .selectpicker()
            .filter(".with-ajax")
            .ajaxSelectPicker(options);
        jQuery("select").trigger("change");

        function chooseSelectpicker(index, selectpicker) {
            jQuery(selectpicker).val(index);
            jQuery(selectpicker).selectpicker('refresh');
        }

        console.log("ready!");
    });
</script>

@endsection
