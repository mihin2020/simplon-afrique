<x-mail::message>
# Félicitations {{ $formateur->name }} ! 🎉

Nous avons le plaisir de vous informer que vous avez obtenu votre badge de labellisation formateur.

<x-mail::panel>
## {{ $badge->getEmoji() }} {{ $badge->label }}

**Score final obtenu :** {{ number_format($score, 2) }}/20

Votre candidature a été évaluée avec succès par notre jury et vous avez satisfait à toutes les exigences du processus de labellisation.
</x-mail::panel>

## Votre attestation

Votre attestation officielle est jointe à cet email au format PDF. Vous pouvez également la télécharger à tout moment depuis votre espace personnel sur notre plateforme.

<x-mail::button :url="route('formateur.dashboard')">
Accéder à mon espace
</x-mail::button>

## Prochaines étapes

En tant que formateur labellisé **{{ $badge->label }}**, vous pouvez désormais :

@if($badge->name === 'senior')
- Animer des formations avancées
- Mentorer d'autres formateurs
- Participer aux jurys d'évaluation
@elseif($badge->name === 'intermediaire')
- Animer des formations intermédiaires
- Accompagner des formateurs juniors
- Proposer des améliorations pédagogiques
@else
- Animer des formations de base
- Participer aux sessions de formation continue
- Développer vos compétences pour évoluer
@endif

---

Nous vous remercions pour votre engagement et vous souhaitons une excellente continuation dans votre parcours de formateur.

Cordialement,<br>
L'équipe {{ $organizationName }}
</x-mail::message>

## Votre attestation

Votre attestation officielle est jointe à cet email au format PDF. Vous pouvez également la télécharger à tout moment depuis votre espace personnel sur notre plateforme.

<x-mail::button :url="route('formateur.dashboard')">
Accéder à mon espace
</x-mail::button>

## Prochaines étapes

En tant que formateur labellisé **{{ $badge->label }}**, vous pouvez désormais :

@if($badge->name === 'senior')
- Animer des formations avancées
- Mentorer d'autres formateurs
- Participer aux jurys d'évaluation
@elseif($badge->name === 'intermediaire')
- Animer des formations intermédiaires
- Accompagner des formateurs juniors
- Proposer des améliorations pédagogiques
@else
- Animer des formations de base
- Participer aux sessions de formation continue
- Développer vos compétences pour évoluer
@endif

---

Nous vous remercions pour votre engagement et vous souhaitons une excellente continuation dans votre parcours de formateur.

Cordialement,<br>
L'équipe {{ $organizationName }}
</x-mail::message>
