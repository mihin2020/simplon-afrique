<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle candidature de formateur - Simplon Africa</title>
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
                                Bonjour {{ $admin->name }},
                            </h1>
                            
                            <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">
                                Une nouvelle candidature vient d'être soumise par un formateur.
                            </p>
                            
                            <div style="background-color: #f9fafb; border-left: 4px solid #dc2626; padding: 16px; margin: 24px 0; border-radius: 4px;">
                                <p style="margin: 0 0 8px; font-size: 16px; font-weight: 600; color: #111827;">
                                    Formateur : {{ $formateurName }}
                                </p>
                                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                    Email : {{ $formateur->email }}
                                </p>
                            </div>
                            
                            <p style="margin: 24px 0 16px; font-size: 16px; line-height: 1.6; color: #374151;">
                                Veuillez consulter le dossier de candidature pour procéder à la validation ou au refus.
                            </p>
                            
                            <!-- Bouton rouge -->
                            <table role="presentation" style="width: 100%; margin: 32px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{{ $candidatureUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);">
                                            Consulter le dossier
                                        </a>
                                    </td>
                                </tr>
                            </table>
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

