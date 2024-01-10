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
            'pillar' => ['nullable', 'string', 'max:1', 'min:1', 'in:R,L'],
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
            'pillar.required' => __('validation.required', ['attribute' =>'pillar']),
            'pillar.in' => __('validation.required', ['attribute' =>'pillar']),
            'pillar.string' => __('validation.string', ['attribute' =>'pillar']),
            'pillar.max' => __('validation.max.string', ['attribute' =>'pillar']),
            'pillar.min' => __('validation.min.string', ['attribute' =>'pillar']),
        ];
    }
}
