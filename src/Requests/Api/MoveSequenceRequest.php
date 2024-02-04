<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MoveSequenceRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'moveSequenceRequest';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'SEQUID' => ['required', 'int', 'min:1'],
            'WCSHNA' => ['required', 'string', 'max:32', 'min:1'],
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
            'SEQUID.required' => __('validation.required', ['attribute' => 'SEQUID']),
            'SEQUID.string' => __('validation.integer', ['attribute' => 'SEQUID']),
            'SEQUID.min' => __('validation.min.numeric', ['attribute' => 'SEQUID', 'min' => 1]),
            'WCSHNA.required' => __('validation.required', ['attribute' => 'WCSHNA']),
            'WCSHNA.string' => __('validation.string', ['attribute' => 'WCSHNA']),
            'WCSHNA.max' => __('validation.max.string', ['attribute' => 'WCSHNA', 'max' => 32]),
            'WCSHNA.min' => __('validation.min.string', ['attribute' => 'WCSHNA', 'min' => 1]),
        ];
    }
}