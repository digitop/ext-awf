<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SequenceCreateRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'sequenceCreateRequest';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
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
            'WCSHNA.required' => __('validation.required', ['attribute' =>'WCSHNA']),
            'WCSHNA.string' => __('validation.string', ['attribute' =>'WCSHNA']),
            'WCSHNA.max' => __('validation.max.string', ['attribute' =>'WCSHNA']),
            'WCSHNA.min' => __('validation.min.string', ['attribute' =>'WCSHNA']),
        ];
    }
}
