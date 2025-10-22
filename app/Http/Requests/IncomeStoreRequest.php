<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'date' => ['required','date','before_or_equal:today'],
            'source' => ['required','string','max:255'],
            'description' => ['nullable','string','max:2000'],
            // Accept numeric; frontend may send string "1.000.000" -> parse in controller if needed
            'amount' => ['required','numeric','min:0'],
        ];
    }
}
