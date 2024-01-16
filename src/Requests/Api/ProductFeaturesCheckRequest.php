<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductFeaturesCheckRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'productFeaturesCheckRequest';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'productCode' => ['required', 'string', 'min:1', 'max:32', 'exists:PRODUCT,PRCODE'],
            'color' => ['required', 'string', 'max:6', 'min:6'],
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
            'productCode.string' => __('validation.integer', ['attribute' =>'productCode']),
            'productCode.max' => __('validation.max.string', ['attribute' =>'productCode']),
            'productCode.min' => __('validation.min.numeric', ['attribute' =>'productCode']),
            'productCode.exists' => __('validation.exists', ['attribute' =>'productCode']),
            'color.required' => __('validation.required', ['attribute' =>'color']),
            'color.string' => __('validation.string', ['attribute' =>'color']),
            'color.max' => __('validation.max.string', ['attribute' =>'color']),
            'color.min' => __('validation.min.string', ['attribute' =>'color']),
        ];
    }
}
