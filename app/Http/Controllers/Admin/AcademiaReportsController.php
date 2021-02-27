<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FormBuilderHelper;
use Illuminate\Http\Request;
use PDF;

class AcademiaReportsController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  *
    //  */
    // public function index()
    // {
    //     $data = [
    //         [
    //             'id' => 'transcript',
    //             'title' => 'our title',
    //             'open' => [
    //                 'route' => 'reports/transcript',
    //                 'method' => 'get',
    //             ],
    //             'fields' => [
    //                 [
    //                     'name' => 'course',
    //                     'type' => 'list',
    //                     'options' =>  [1 => 'a', 2 => 'b'],
    //                 ],
    //                 [
    //                     'name' => 'note',
    //                     'type' => 'text',
    //                 ],
    //             ],
    //         ],
    //     ];
    //     $formList = [];
    //     foreach ($data as $form) {
    //         $formList[] = new FormBuilderHelper($form);
    //     }

    //     return view('reports.index', [
    //         'formList' => $formList
    //     ]);
    // }


    public function transcript()
    {

        $data = [
            'foo' => 'bar'
        ];

        return view('reports.transcript', $data);

        $pdf = PDF::loadView('reports.transcript', $data);
        return $pdf->stream();
    }
}
