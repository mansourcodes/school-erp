@php
    $key = $curriculum->id . '_' . rand();
@endphp


<h4>{{ $classRoom->long_name[$curriculum->id] }}</h4>
<div id="example_{{ $key }}"></div>
<div id="save_{{ $key }}" class="btn btn-primary w-25 mt-2">
    {{ __('crud.save') }}
</div>

@section('after_scripts')
    @parent

    <script>
        var csrfToken = document.head.querySelector("[name~=csrf-token][content]").content;

        const save_{{ $key }} = document.querySelector('#save_{{ $key }}');

        const container_{{ $key }} = document.querySelector('#example_{{ $key }}');
        const hot_{{ $key }} = new Handsontable(container_{{ $key }}, {
            licenseKey: 'non-commercial-and-evaluation', // for non-commercial use only
            // cell data
            data: [@php
                foreach ($table as $row) {
                    echo json_encode($row, JSON_FORCE_OBJECT) . ',';
                }
            @endphp],
            // header labels
            colHeaders: ['@php
                echo implode("','", $colHeaders);
            @endphp'],
            // columns settings
            columns: [@php
                foreach ($columns as $row) {
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


        save_{{ $key }}.addEventListener('click', () => {
            // save all cell's data
            fetch("{{ backpack_url('saveMarksByClassForm') }}", {
                    method: 'POST',
                    // mode: 'no-cors',
                    // credentials: "same-origin",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-Token": csrfToken
                    },


                    body: JSON.stringify({
                        course_id: {{ $classRoom->course_id }},
                        curriculum_id: {{ $curriculum->id }},
                        data: hot_{{ $key }}.getSourceData()
                    })
                })
                .then(function(response) {
                    console.log(response);
                    return response.json();
                }).then(function(json) {
                    console.log(json);
                    // change course

                })
                .catch(function(error) {

                    console.error(error);

                });
        });
    </script>
@endsection
