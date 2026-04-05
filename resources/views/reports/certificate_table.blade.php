<div style="width: {{ $table_width }}%">
    <table class="table table-no-border" style="width: 100%">

        <tr>
            <th> الدرجات:</th>
            <td> </td>
        </tr>
    </table>


    <table class="table " style="width: 100%">
        <tr>
            <th class="text-center">
                الوصف
            </th>
            <th class="text-center">
                الدرجة العظمى
            </th>
            <th class="text-center">
                الدرجة المستحقة
            </th>
        </tr>
        @foreach ($studentmark->brief_marks as $mark)
            {{-- @dd($mark) --}}
            <tr>
                <td class="text-center">
                    @if (isArabic($mark->label))
                        {{ $mark->label }}
                    @else
                        {{ __('studentmark.' . $mark->label) }}
                    @endif
                </td>
                <td class="text-center">
                    {{ $mark->max }}
                </td>
                <td class="text-center">
                    {{ $mark->mark }}
                </td>
            </tr>
        @endforeach
        <tr>
            <th class="text-center">
                المجموع
            </th>
            <th class="text-center">
                100
            </th>
            <th class="text-center">
                {{ $studentmark->total_mark }}
            </th>
        </tr>

    </table>


    <table class="table table-no-border" style="width: 100%">
        <tr>
            <th> التقدير العام:</th>
            <td> {{ $studentmark->final_grade }}</td>
        </tr>

    </table>
    <hr>

</div>
