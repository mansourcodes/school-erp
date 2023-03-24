<!-- s -->
@extends(backpack_view('blank'))




@section('content')
    ---

    اضافة الجدول اكسل
    <hr>
    @foreach ($classMarks as $classMark)
        @include('marks.com.class_room_marks_table', [
            'curriculum' => $classMark['curriculum'],
            'table' => $classMark['table'],
            'colHeaders' => $classMark['colHeaders'],
            'columns' => $classMark['columns'],
            'classRoom' => $classRoom,
        ])

        <hr>
    @endforeach
@endsection


@section('before_styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" />
@endsection


@section('before_scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/languages/ar-AR.js"></script>
@endsection
