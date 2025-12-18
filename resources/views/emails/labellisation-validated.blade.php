<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labellisation validée - Simplon Africa</title>
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
                                Félicitations {{ $formateurName }} !
                            </h1>
                            
                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">
                                Nous avons le plaisir de vous informer que votre candidature de labellisation a été <strong style="color: #059669;">validée</strong> avec succès !
                            </p>
                            
                            @if($badge)
                                <div style="margin: 24px 0; padding: 20px; background-color: #f0fdf4; border-left: 4px solid #059669; border-radius: 4px;">
                                    <p style="margin: 0 0 8px; font-size: 18px; font-weight: 600; color: #059669;">
                                        Badge attribué : {{ $badge->getEmoji() }} {{ $badge->label ?? $badge->name }}
                                    </p>
                                    <p style="margin: 0; font-size: 14px; color: #047857;">
                                        Vous avez obtenu le badge <strong>{{ $badge->label ?? $badge->name }}</strong> suite à votre évaluation.
                                    </p>
                                </div>
                            @endif
                            
                            <p style="margin: 24px 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">
                                Vous pouvez maintenant vous connecter à la plateforme pour télécharger votre attestation de labellisation.
                            </p>
                            
                            <!-- Bouton rouge -->
                            <table role="presentation" style="width: 100%; margin: 32px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{{ $loginUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);">
                                            Se connecter à la plateforme
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 24px 0 0; font-size: 14px; line-height: 1.6; color: #6b7280;">
                                Une fois connecté, vous pourrez accéder à votre espace formateur et télécharger votre attestation de labellisation.
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


