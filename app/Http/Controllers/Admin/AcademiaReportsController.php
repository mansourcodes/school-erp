<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\FormBuilderHelper;
use Illuminate\Http\Request;

class AcademiaReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $data = [
            [
                'title' => 'our title',
                'action' => 'trap',
                'fields' => [
                    [
                        'name' => 'course',
                        'type' => 'list',
                        'options' =>  [1 => 'a', 2 => 'b'],
                    ]
                ],
            ],
        ];
        $formList = [];
        foreach ($data as $form) {
            $formList[] = new FormBuilderHelper($form);
        }

        return view('reports.index', [
            'formList' => $formList
        ]);
    }
}
