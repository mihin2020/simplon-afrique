<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'difficulties' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'other' => ['nullable', 'string'],
            'promotion_id' => ['nullable', 'uuid', 'exists:promotions,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la note est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'promotion_id.exists' => 'La promotion sélectionnée n\'existe pas.',
        ];
    }
}
