<x-mail::message>
# Nouvelle candidature reçue

Bonjour,

Une nouvelle candidature a été soumise pour l'offre d'emploi **{{ $application->jobOffer->title }}**.

## Informations sur le candidat

**Nom :** {{ $application->profile_snapshot['name'] ?? 'N/A' }}

**Prénom :** {{ $application->profile_snapshot['first_name'] ?? 'N/A' }}

**Email :** {{ $application->profile_snapshot['email'] ?? 'N/A' }}

**Statut :** {{ $application->applicant_type_label }}

@if($application->isFormateur())
### Profil Formateur

@if(isset($application->profile_snapshot['technical_profile']))
**Profil technique :** {{ $application->profile_snapshot['technical_profile'] }}
@endif

@if(isset($application->profile_snapshot['years_of_experience']))
**Expérience :** {{ $application->profile_snapshot['years_of_experience'] }}
@endif

@if(isset($application->profile_snapshot['certifications']) && count($application->profile_snapshot['certifications']) > 0)
**Certifications :**
@foreach($application->profile_snapshot['certifications'] as $certification)
- {{ $certification }}
@endforeach
@endif

@if($application->cv_path)
**CV :** Disponible dans la plateforme
@endif
@endif

<x-mail::button :url="$viewUrl" color="primary">
Voir la candidature
</x-mail::button>

Cordialement,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
