@php
    $key = $curriculum->id . '_' . rand();
@endphp


<h4>{{ $classRoom->long_name[$curriculum->id] }}</h4>
<div id="example_{{ $key }}"></div>


@section('after_styles')
@endsection


@section('after_scripts')
    @parent

    <script>
        const container_{{ $key }} = document.querySelector('#example_{{ $key }}');
        const hot_{{ $key }} = new Handsontable(container_{{ $key }}, {
            data: @php
                echo json_encode($table);
            @endphp,
            rowHeaders: true,
            colHeaders: true,
            height: 'auto',
            licenseKey: 'non-commercial-and-evaluation' // for non-commercial use only
        });
    </script>
@endsection
