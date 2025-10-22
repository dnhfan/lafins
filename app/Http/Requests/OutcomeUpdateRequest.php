<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutcomeUpdateRequest extends FormRequest
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
            'date' => ['date', 'before_or_equal:today'],
            'category' => ['string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['numeric', 'min:0'],
            'jar_id' => ['required', 'exists:jars,id'],
        ];
    }
}
