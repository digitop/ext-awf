<?php

namespace AWF\Extension\Requests\Web\Reports;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class ProductionDetailsShowRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'serial' => 'nullable|string|max:64|min:1',
            'porscheOrderNumber' => 'nullable|string|max:10|min:1',
            'porscheSequenceNumber' => 'nullable|string|max:10|min:1',
            'productCode' => 'required_with:porscheOrderNumber,porscheSequenceNumber|string|max:32|min:1',
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
            'porscheOrderNumber.string' => __('validation.integer', ['attribute' => 'porscheOrderNumber']),
            'porscheOrderNumber.max' => __('validation.max.string', ['attribute' => 'porscheOrderNumber', 'max' => 10]),
            'porscheOrderNumber.min' => __('validation.min.string', ['attribute' => 'porscheOrderNumber', 'min' => 1]),
            'porscheSequenceNumber.string' => __('validation.integer', ['attribute' => 'porscheSequenceNumber']),
            'porscheSequenceNumber.max' => __('validation.max.string',
                ['attribute' => 'porscheSequenceNumber', 'max' => 10]
            ),
            'porscheSequenceNumber.min' => __('validation.min.string',
                ['attribute' => 'porscheSequenceNumber', 'min' => 1]
            ),
            'productCode.required_with' => __('validation.required_with',
                ['attribute' => 'productCode', 'values' => 'porscheSequenceNumber/porscheOrderNumber']
            ),
            'productCode.string' => __('validation.integer', ['attribute' => 'productCode']),
            'productCode.max' => __('validation.max.string', ['attribute' => 'productCode', 'max' => 32]),
            'productCode.min' => __('validation.min.string', ['attribute' => 'productCode', 'min' => 1]),
        ];
    }
}
