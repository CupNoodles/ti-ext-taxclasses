<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Http\Requests;

use CupNoodles\TaxClasses\Models\TaxClass;
use Igniter\System\Classes\FormRequest;
use Illuminate\Validation\Rule;

class TaxClassRequest extends FormRequest
{
    public function attributes(): array
    {
        return [
            'name' => lang('cupnoodles.taxclasses::default.name'),
            'label' => lang('cupnoodles.taxclasses::default.label'),
            'description' => lang('cupnoodles.taxclasses::default.description'),
            'rate' => lang('cupnoodles.taxclasses::default.rate'),
            'include_in_price' => lang('cupnoodles.taxclasses::default.include_in_price'),
            'limit_to_order_type' => lang('cupnoodles.taxclasses::default.limit_to_order_type'),
            'tax_delivery' => lang('cupnoodles.taxclasses::default.tax_delivery'),
            'tax_payment_fee' => lang('cupnoodles.taxclasses::default.tax_payment_fee'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'between:1,128'],
            'label' => ['nullable', 'string', 'max:128'],
            'description' => ['nullable', 'string', 'max:500'],
            // Up to 4 decimal places, eg. 8.875
            'rate' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d{1,3}(\.\d{1,4})?$/'],
            'include_in_price' => ['boolean'],
            'limit_to_order_type' => ['nullable', 'array'],
            'limit_to_order_type.*' => ['string', Rule::in(array_keys(TaxClass::getOrderTypeOptions()))],
            'tax_delivery' => ['boolean'],
            'tax_payment_fee' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'rate.regex' => lang('cupnoodles.taxclasses::default.error_rate_format'),
        ];
    }
}
