<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intérêt pour une information formateur</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px; text-align: center;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 32px 32px 16px; text-align: left;">
                            <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 600; color: #111827;">
                                Un formateur a manifesté son intérêt
                            </h1>
                            <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                Suite à une information publiée dans le tableau de bord formateur.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px 20px; margin-bottom: 16px;">
                                <p style="margin: 0 0 4px; font-size: 14px; color: #6b7280;">Formateur intéressé :</p>
                                <p style="margin: 0; font-size: 16px; font-weight: 600; color: #111827;">
                                    {{ trim(($formateur->first_name ?? '').' '.($formateur->name ?? '')) ?: $formateur->email }}
                                </p>
                                <p style="margin: 4px 0 0; font-size: 13px; color: #6b7280;">
                                    Email : {{ $formateur->email }}
                                </p>
                            </div>

                            <div style="border-radius: 8px; border: 1px solid #e5e7eb; padding: 16px 20px;">
                                <p style="margin: 0 0 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af;">
                                    Information consultée
                                </p>
                                <h2 style="margin: 0 0 8px; font-size: 18px; font-weight: 600; color: #111827;">
                                    {{ $notification->title }}
                                </h2>
                                @if($notification->deadline_at)
                                    <p style="margin: 0 0 8px; font-size: 13px; color: #6b7280;">
                                        Date limite : <strong>{{ $notification->deadline_at->format('d/m/Y') }}</strong>
                                    </p>
                                @endif
                                <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #374151;">
                                    {{ $notification->description }}
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px 28px;">
                            <p style="margin: 0; font-size: 13px; color: #6b7280;">
                                Vous pouvez désormais contacter directement ce formateur pour lui partager plus de détails ou organiser un échange.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 16px 32px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>


