<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderCheckSerialRequest extends FormRequest
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
            'serial' => ['nullable', 'string', 'max:64', 'min:1'],
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
            'serial.string' => __('validation.integer', ['attribute' => 'serial']),
            'serial.max' => __('validation.max.string', ['attribute' => 'serial', 'max' => 64]),
            'serial.min' => __('validation.min.string', ['attribute' => 'serial', 'min' => 1]),
        ];
    }
}
