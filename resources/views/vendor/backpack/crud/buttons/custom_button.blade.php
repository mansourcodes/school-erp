@if ($crud->hasAccess('create'))
    <a href="{{ $url }}" class="btn btn-primary" data-style="zoom-in"><span class="ladda-label"><i
                class="nav-icon la la-file-invoice-dollar"></i>{{ $label }}</span></a>
@endif
