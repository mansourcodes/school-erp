  {!! Setting::get('payment_print.pre') !!}



  @if (Setting::get('print_header'))
      <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
  @endif

  <table class="table table-no-border ">
      <tbody>
          <tr>
              <td></td>
              <td>
                  <h2>رصيد نقدي</h2>
              </td>
              <td></td>
          </tr>
          <tr>
              <td>{{ $payment->id }}</td>
              <td colspan="2">تاريخ:
                  {{ $payment->created_at->format('d/m/Y') }}
              </td>
          </tr>
          <tr>
              <td>استلمنا من:</td>
              <td colspan="2">{{ $payment->student->name }}</td>
          </tr>
          <tr>
              <td>مبلغ:</td>
              <td colspan="2">{{ $payment->amount }} دب</td>
          </tr>
          <tr>
              <td>التفاصيل:</td>
              <td colspan="2"> {{ $payment->course->long_name }} </td>
          </tr>
          <tr>
              <td>رقم الطالب:</td>
              <td colspan="2">{{ $payment->student->student_id }}</td>
          </tr>
      </tbody>
  </table>



  {!! Setting::get('payment_print.pro') !!}
