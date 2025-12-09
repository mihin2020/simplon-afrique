<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'country' => ['required', 'string', 'max:255'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['uuid', 'exists:organizations,id'],
            'number_of_learners' => ['required', 'integer', 'min:1'],
            'admin_id' => ['required', 'uuid', 'exists:users,id'],
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
            'name.required' => 'Le nom de la promotion est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'start_date.required' => 'La date de début est obligatoire.',
            'start_date.date' => 'La date de début doit être une date valide.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'end_date.date' => 'La date de fin doit être une date valide.',
            'end_date.after' => 'La date de fin doit être postérieure à la date de début.',
            'country.required' => 'Le pays est obligatoire.',
            'organization_ids.array' => 'Les organisations doivent être un tableau.',
            'organization_ids.*.exists' => 'Une ou plusieurs organisations sélectionnées n\'existent pas.',
            'number_of_learners.required' => 'Le nombre d\'apprenants est obligatoire.',
            'number_of_learners.integer' => 'Le nombre d\'apprenants doit être un nombre entier.',
            'number_of_learners.min' => 'Le nombre d\'apprenants doit être au moins 1.',
            'admin_id.required' => 'L\'administrateur associé est obligatoire.',
            'admin_id.exists' => 'L\'administrateur sélectionné n\'existe pas.',
        ];
    }
}
