@if ($crud->hasAccess('clone'))
    <a href="javascript:void(0)" onclick="cloneEntry(this)"
        data-route="{{ url($crud->route . '/' . $entry->getKey() . '/clone') }}" class="btn btn-sm btn-link"
        data-button-type="clone"><i class="la la-copy"></i> {{ trans('backpack::crud.clone') }}</a>
@endif

{{-- Button Javascript --}}
@push('after_scripts') @if (request()->ajax())
@endpush
@endif
<script>
    if (typeof cloneEntry != 'function') {
        $("[data-button-type=clone]").unbind('click');

        function cloneEntry(button) {
            var route = $(button).attr('data-route');

            swal({
                title: "{!! trans('backpack::base.warning') !!}",
                text: "{!! trans('localize.are_you_sure_you_want_to_clone_this_item') !!}",
                icon: "info",
                buttons: ["{!! trans('backpack::crud.cancel') !!}", "{{ trans('backpack::crud.clone') }}"],
            }).then((value) => {
                if (value) {
                    $.ajax({
                        url: route,
                        type: 'POST',
                        success: function(result) {
                            // Show a success notification bubble
                            new Noty({
                                type: "success",
                                text: "{!! trans('backpack::crud.clone_success') !!}"
                            }).show();

                            // Hide the modal, if any
                            $('.modal').modal('hide');

                            if (typeof crud !== 'undefined') {
                                crud.table.ajax.reload();
                            }
                        },
                        error: function(result) {
                            // Show an error notification bubble
                            new Noty({
                                type: "error",
                                text: "{!! trans('backpack::crud.clone_failure') !!}"
                            }).show();
                        },
                    });
                }
            });
        }
    }

    // Add the function to the DataTable draw event queue
    // crud.addFunctionToDataTablesDrawEventQueue('cloneEntry');
</script>
@if (!request()->ajax())
@endpush
@endif
