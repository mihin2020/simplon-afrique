<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobOfferRequest extends FormRequest
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
            'contract_type' => ['required', Rule::in(['cdi', 'cdd', 'stage', 'alternance', 'freelance'])],
            'location' => ['required', 'string', 'max:255'],
            'remote_policy' => ['required', Rule::in(['sur_site', 'hybride', 'full_remote'])],
            'description' => ['required', 'string', 'min:50'],
            'experience_years' => ['required', 'string', 'max:100'],
            'minimum_education' => ['required', 'string', 'max:255'],
            'required_skills' => ['required', 'array', 'min:1'],
            'required_skills.*' => ['required', 'string', 'max:100'],
            'application_deadline' => ['required', 'date', 'after:today'],
            'additional_info' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
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
            'title.required' => 'Le titre du poste est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'contract_type.required' => 'Le type de contrat est obligatoire.',
            'contract_type.in' => 'Le type de contrat sélectionné n\'est pas valide.',
            'location.required' => 'La localisation est obligatoire.',
            'location.max' => 'La localisation ne peut pas dépasser 255 caractères.',
            'remote_policy.required' => 'La politique de télétravail est obligatoire.',
            'remote_policy.in' => 'La politique de télétravail sélectionnée n\'est pas valide.',
            'description.required' => 'La description du poste est obligatoire.',
            'description.min' => 'La description doit contenir au moins 50 caractères.',
            'experience_years.required' => 'Le niveau d\'expérience requis est obligatoire.',
            'minimum_education.required' => 'Le diplôme/formation minimale est obligatoire.',
            'required_skills.required' => 'Au moins une compétence est requise.',
            'required_skills.min' => 'Au moins une compétence est requise.',
            'required_skills.*.required' => 'La compétence ne peut pas être vide.',
            'required_skills.*.max' => 'Chaque compétence ne peut pas dépasser 100 caractères.',
            'application_deadline.required' => 'La date limite de candidature est obligatoire.',
            'application_deadline.date' => 'La date limite de candidature doit être une date valide.',
            'application_deadline.after' => 'La date limite de candidature doit être postérieure à aujourd\'hui.',
            'additional_info.max' => 'Les informations complémentaires ne peuvent pas dépasser 5000 caractères.',
            'attachment.file' => 'Le fichier joint doit être un fichier valide.',
            'attachment.mimes' => 'Le fichier joint doit être au format PDF, JPG, JPEG ou PNG.',
            'attachment.max' => 'Le fichier joint ne peut pas dépasser 10 Mo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'titre du poste',
            'contract_type' => 'type de contrat',
            'location' => 'localisation',
            'remote_policy' => 'politique de télétravail',
            'description' => 'description',
            'experience_years' => 'niveau d\'expérience',
            'minimum_education' => 'formation minimale',
            'required_skills' => 'compétences requises',
            'application_deadline' => 'date limite de candidature',
            'additional_info' => 'informations complémentaires',
            'attachment' => 'pièce jointe',
        ];
    }
}
