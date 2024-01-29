<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckProductCheckRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'productCheck';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'serial' => ['required', 'int', 'max:64', 'min:1'],
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
            'serial.required' => __('validation.required', ['attribute' =>'serial']),
            'serial.string' => __('validation.integer', ['attribute' =>'serial']),
            'serial.max' => __('validation.max.string', ['attribute' =>'serial']),
            'serial.min' => __('validation.min.numeric', ['attribute' =>'serial']),
            'dashboard.required' => __('validation.required', ['attribute' =>'dashboard']),
            'dashboard.string' => __('validation.integer', ['attribute' =>'dashboard']),
            'dashboard.max' => __('validation.max.string', ['attribute' =>'dashboard']),
            'dashboard.min' => __('validation.min.numeric', ['attribute' =>'dashboard']),
            'dashboard.exists' => __('validation.exists', ['attribute' =>'dashboard']),
        ];
    }
}
