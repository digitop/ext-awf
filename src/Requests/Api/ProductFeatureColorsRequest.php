<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductFeatureColorsRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'productColorRequest';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'dashboard' => ['required', 'string', 'min:1', 'max:32', 'exists:DASHBOARD,DHIDEN'],
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
        ];
    }
}
