<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Jar;

class OutcomeStoreRequest extends FormRequest
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
            'date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'jar_id' => ['required', 'exists:jars,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();
            if (isset($data['jar_id']) && isset($data['amount'])) {
                $jar = Jar::where('id', $data['jar_id'])->first();
                if (! $jar) {
                    $validator->errors()->add('jar_id', 'Selected jar not found.');
                    return;
                }
                // check ownership
                if ($jar->user_id !== $this->user()->id) {
                    $validator->errors()->add('jar_id', 'Selected jar does not belong to you.');
                    return;
                }
                // check balance
                if ((float) $jar->balance < (float) $data['amount']) {
                    $validator->errors()->add('amount', 'Insufficient balance in selected jar.');
                }
            }
        });
    }
}
