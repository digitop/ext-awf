<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SequenceShowRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'sequenceShowRequest';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'side' => ['nullable', 'string', 'max:1', 'min:1', 'in:R,L'],
            'limit' => ['nullable', 'int', 'min:1'],
            'no_change' => ['nullable', 'string', 'in:true,false'],
            'to_preparation_panel' => ['nullable', 'string', 'in:true,false'],
            'porscheProductNumber' => ['nullable', 'string'],
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
            'side.in' => __('validation.in', ['attribute' => 'side']),
            'side.string' => __('validation.string', ['attribute' => 'side']),
            'side.max' => __('validation.max.string', ['attribute' => 'side', 'max' => 1]),
            'side.min' => __('validation.min.string', ['attribute' => 'side', 'min' => 1]),
            'limit.int' => __('validation.integer', ['attribute' => 'limit']),
            'limit.min' => __('validation.min.numeric', ['attribute' => 'limit', 'min' => 1]),
            'no_change.in' => __('validation.in', ['attribute' => 'no_change']),
            'no_change.string' => __('validation.string', ['attribute' => 'no_change']),
            'to_preparation_panel.in' => __('validation.in', ['attribute' => 'to_preparation_panel']),
            'to_preparation_panel.string' => __('validation.string', ['attribute' => 'to_preparation_panel']),
            'porscheProductNumber.string' => __('validation.string', ['attribute' => 'porscheProductNumber']),
        ];
    }
}
