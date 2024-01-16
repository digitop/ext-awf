<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductFeaturesShowRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'productFeatureShowRequest';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'productCode' => ['required', 'string', 'max:32', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'productCode.required' => __('validation.required', ['attribute' =>'productCode']),
            'productCode.string' => __('validation.string', ['attribute' =>'productCode']),
            'productCode.max' => __('validation.max.string', ['attribute' =>'productCode']),
            'productCode.min' => __('validation.min.string', ['attribute' =>'productCode']),
        ];
    }
}