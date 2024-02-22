<?php

namespace AWF\Extension\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PreparationStationPanelDefaultRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'preparationStationPanelDefault';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'fabric_shelf' => ['nullable', 'int', 'max:1', 'min:0'],
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
            'fabric_shelf.required' => __('validation.required', ['attribute' => 'productCode']),
            'fabric_shelf.integer' => __('validation.string', ['attribute' => 'productCode']),
            'fabric_shelf.max' => __('validation.max.numeric', ['attribute' => 'productCode', 'max' => 1]),
            'fabric_shelf.min' => __('validation.min.numeric', ['attribute' => 'productCode', 'min' => 0]),
        ];
    }
}
