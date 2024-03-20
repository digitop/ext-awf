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
            'dashboard' => ['required', 'string', 'min:1', 'max:32', 'exists:DASHBOARD,DHIDEN'],
            'color' => ['required', 'string', 'max:6', 'min:6'],
            'material' => ['required', 'string', 'max:32', 'min:0'],
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
            'dashboard.required' => __('validation.required', ['attribute' => 'dashboard']),
            'dashboard.string' => __('validation.integer', ['attribute' => 'dashboard']),
            'dashboard.max' => __('validation.max.string', ['attribute' => 'dashboard', 'max' => 32]),
            'dashboard.min' => __('validation.min.numeric', ['attribute' => 'dashboard', 'min' => 1]),
            'dashboard.exists' => __('validation.exists', ['attribute' => 'dashboard']),
            'color.required' => __('validation.required', ['attribute' => 'color']),
            'color.string' => __('validation.string', ['attribute' => 'color']),
            'color.max' => __('validation.max.string', ['attribute' => 'color', 'max' => 6]),
            'color.min' => __('validation.min.string', ['attribute' => 'color', 'min' => 6]),
            'material.required' => __('validation.required', ['attribute' => 'material']),
            'material.string' => __('validation.string', ['attribute' => 'material']),
            'material.max' => __('validation.max.string', ['attribute' => 'material', 'max' => 32]),
            'material.min' => __('validation.min.string', ['attribute' => 'material', 'min' => 0]),
        ];
    }
}
