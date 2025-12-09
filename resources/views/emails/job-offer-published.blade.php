<x-mail::message>
# Nouvelle offre d'emploi disponible

Bonjour,

Une nouvelle offre d'emploi vient d'être publiée sur la plateforme Simplon Africa.

## {{ $jobOffer->title }}

**Type de contrat :** {{ $jobOffer->contract_type_label }}

**Localisation :** {{ $jobOffer->location }} ({{ $jobOffer->remote_policy_label }})

**Niveau d'expérience :** {{ $jobOffer->experience_years }}

**Formation requise :** {{ $jobOffer->minimum_education }}

### Description du poste

{{ Str::limit($jobOffer->description, 300) }}

### Compétences requises

@foreach($jobOffer->required_skills as $skill)
- {{ $skill }}
@endforeach

**Date limite de candidature :** {{ $jobOffer->application_deadline->format('d/m/Y') }}

<x-mail::button :url="$applyUrl" color="primary">
Postuler maintenant
</x-mail::button>

Cordialement,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
