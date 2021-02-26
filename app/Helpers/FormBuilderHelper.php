<?php

namespace App\Helpers;

use Form;

class FormBuilderHelper
{

    function __construct($form)
    {
        foreach ($form as $key => $value) {
            $this->{$key} = $value;
        }
    }


    public function getTitle()
    {
        return $this->title;
    }

    public function openForm()
    {
        return Form::open($this->open);
    }

    public function closeForm()
    {
        return Form::close();
    }


    public function getSubmitValue()
    {
        return $this->submitValue ?? 'print';
    }

    public function getFields()
    {
        $fieldsHtml = [];
        foreach ($this->fields as $field) {

            $inputAttr = array('class' => "form-control");


            $fieldsHtml[] = '<div class="form-group col">';
            $fieldsHtml[] = Form::label($field['name'], $field['name']);
            switch ($field['type']) {
                case 'list':
                    $fieldsHtml[] = Form::select($field['name'], $field['options'], '', $inputAttr);
                    break;

                default:
                    $fieldsHtml[] = Form::text($field['name'], '', $inputAttr);
                    break;
            }

            $fieldsHtml[] = "</div>";
        }

        return implode('', $fieldsHtml);
    }
}
