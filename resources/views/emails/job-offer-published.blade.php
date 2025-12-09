<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width:device-width, initial-scale=1.0">
    <title>Nouvelle offre d'emploi - Simplon Africa</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px; text-align: center;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header avec logo -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center;">
                            <img src="{{ $logoUrl }}" alt="Simplon Africa" style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
                        </td>
                    </tr>
                    
                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 0 40px 40px;">
                            <h1 style="margin: 0 0 20px; font-size: 24px; font-weight: 600; color: #111827;">
                                Nouvelle offre d'emploi disponible
                            </h1>
                            
                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">
                                Bonjour,
                            </p>
                            
                            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.6; color: #374151;">
                                Une nouvelle offre d'emploi vient d'être publiée sur la plateforme Simplon Africa.
                            </p>
                            
                            <!-- Détails de l'offre -->
                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; margin: 24px 0;">
                                <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 600; color: #111827;">
                                    {{ $jobOffer->title }}
                                </h2>
                                
                                <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 16px 0;">
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; color: #6b7280; width: 40%;">
                                            <strong style="color: #374151;">Type de contrat :</strong>
                                        </td>
                                        <td style="padding: 8px 0; font-size: 14px; color: #111827;">
                                            {{ $jobOffer->contract_type_label }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; color: #6b7280;">
                                            <strong style="color: #374151;">Localisation :</strong>
                                        </td>
                                        <td style="padding: 8px 0; font-size: 14px; color: #111827;">
                                            {{ $jobOffer->location }} ({{ $jobOffer->remote_policy_label }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; color: #6b7280;">
                                            <strong style="color: #374151;">Niveau d'expérience :</strong>
                                        </td>
                                        <td style="padding: 8px 0; font-size: 14px; color: #111827;">
                                            {{ $jobOffer->experience_years }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; color: #6b7280;">
                                            <strong style="color: #374151;">Formation requise :</strong>
                                        </td>
                                        <td style="padding: 8px 0; font-size: 14px; color: #111827;">
                                            {{ $jobOffer->minimum_education }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; font-size: 14px; color: #6b7280;">
                                            <strong style="color: #374151;">Date limite :</strong>
                                        </td>
                                        <td style="padding: 8px 0; font-size: 14px; color: #111827;">
                                            {{ $jobOffer->application_deadline->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                </table>
                                
                                @if($jobOffer->description)
                                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                                        <h3 style="margin: 0 0 12px; font-size: 16px; font-weight: 600; color: #111827;">
                                            Description du poste
                                        </h3>
                                        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #374151;">
                                            {{ Str::limit($jobOffer->description, 300) }}
                                        </p>
                                    </div>
                                @endif
                                
                                @if($jobOffer->required_skills && count($jobOffer->required_skills) > 0)
                                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                                        <h3 style="margin: 0 0 12px; font-size: 16px; font-weight: 600; color: #111827;">
                                            Compétences requises
                                        </h3>
                                        <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8; color: #374151;">
                                            @foreach($jobOffer->required_skills as $skill)
                                                <li>{{ $skill }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Bouton rouge -->
                            <table role="presentation" style="width: 100%; margin: 32px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{{ $applyUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);">
                                            Postuler maintenant
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 24px 0 0; font-size: 14px; line-height: 1.6; color: #6b7280; text-align: center;">
                                Cordialement,<br>
                                <strong>L'équipe {{ config('app.name') }}</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; border-radius: 0 0 8px 8px;">
                            <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                Simplon Afrique
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
