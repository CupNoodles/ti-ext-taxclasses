<?php

namespace CupNoodles\TaxClasses\Requests;

use System\Classes\FormRequest;

class TaxClasses extends FormRequest
{
    public function rules()
    {
        return [
            ['name', 'cupnoodles.taxclasses::default.name', 'required|between:2,128'],
            ['rate', 'cupnoodles.taxclasses::default.rate', 'required|numeric'],

        ];
    }

}
