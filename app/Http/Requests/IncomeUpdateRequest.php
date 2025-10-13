<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'date' => ['date','before_or_equal:today'],
            'source' => ['string','max:255'],
            'description' => ['nullable','string','max:2000'],
            // Accept numeric; frontend may send string "1.000.000" -> parse in controller if needed
            'amount' => ['numeric','min:0'],
        ];
    }
}
