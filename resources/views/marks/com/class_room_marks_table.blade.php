@php
    $key = $curriculum->id . '_' . rand();
@endphp


<h4>{{ $classRoom->long_name[$curriculum->id] }}</h4>
<div id="example_{{ $key }}"></div>

@section('after_scripts')
    @parent

    <script>
        const container_{{ $key }} = document.querySelector('#example_{{ $key }}');
        const hot_{{ $key }} = new Handsontable(container_{{ $key }}, {
            licenseKey: 'non-commercial-and-evaluation', // for non-commercial use only
            // cell data
            data: [@php
                foreach ($table as $key => $row) {
                    echo json_encode($row, JSON_FORCE_OBJECT) . ',';
                }
            @endphp],
            // header labels
            colHeaders: ['@php
                echo implode("','", $colHeaders);
            @endphp'],
            // columns settings
            columns: [@php
                foreach ($columns as $key => $row) {
                    echo json_encode($row, JSON_FORCE_OBJECT) . ',';
                }
            @endphp],
            // ...
            // render Handsontable from the right to the left
            layoutDirection: 'rtl',
            // load an RTL language (e.g., Arabic)
            language: 'ar-AR',
            // enable a few options that exemplify the layout direction
            dropdownMenu: false,
            filters: false,
            contextMenu: false,
            rowHeaders: false,
            height: 'auto',
        });
    </script>
@endsection
