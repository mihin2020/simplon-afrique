<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Attestation de Labellisation - {{ $formateur->name }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: A4 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #dc2626;
            color: #1f2937;
            line-height: 1.4;
        }
        
        .certificate-wrapper {
            width: 100%;
            height: 100vh;
            padding: 12px;
            background: #dc2626;
        }
        
        .certificate-container {
            width: 100%;
            height: 100%;
            background: #f8f5f0;
            position: relative;
            overflow: hidden;
        }
        
        /* Bordure dorée intérieure */
        .golden-border {
            position: absolute;
            top: 18px;
            left: 18px;
            right: 18px;
            bottom: 18px;
            border: 2px solid #c9a227;
            pointer-events: none;
        }
        
        /* Coins décoratifs */
        .corner {
            position: absolute;
            width: 50px;
            height: 50px;
            border: 2px solid #c9a227;
        }
        
        .corner-tl {
            top: 26px;
            left: 26px;
            border-right: none;
            border-bottom: none;
        }
        
        .corner-tr {
            top: 26px;
            right: 26px;
            border-left: none;
            border-bottom: none;
        }
        
        .corner-bl {
            bottom: 26px;
            left: 26px;
            border-right: none;
            border-top: none;
        }
        
        .corner-br {
            bottom: 26px;
            right: 26px;
            border-left: none;
            border-top: none;
        }
        
        /* Petites décorations aux coins */
        .corner-dot {
            position: absolute;
            width: 6px;
            height: 6px;
            background: #c9a227;
            border-radius: 50%;
        }
        
        .dot-tl { top: 30px; left: 30px; }
        .dot-tr { top: 30px; right: 30px; }
        .dot-bl { bottom: 30px; left: 30px; }
        .dot-br { bottom: 30px; right: 30px; }
        
        /* Filigrane - Logo Simplon en arrière-plan */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            width: 500px;
            height: auto;
            pointer-events: none;
            z-index: 0;
        }
        
        /* Contenu principal */
        .content {
            position: absolute;
            top: 30px;
            left: 40px;
            right: 40px;
            bottom: 30px;
            z-index: 1;
            text-align: center;
        }
        
        /* En-tête - Titre principal */
        .main-title {
            font-size: 42px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 12px;
            margin-bottom: 3px;
            margin-top: 5px;
        }
        
        .sub-title {
            font-size: 22px;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 6px;
            margin-bottom: 15px;
        }
        
        /* Texte "Cette attestation est décernée à" */
        .awarded-to {
            font-size: 14px;
            color: #5a6a7a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        /* Nom du formateur */
        .recipient-name {
            font-size: 44px;
            font-family: 'DejaVu Serif', Georgia, serif;
            font-style: italic;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        /* Ligne sous le nom */
        .name-underline {
            width: 400px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #c9a227, #c9a227, transparent);
            margin: 0 auto 18px;
        }
        
        /* Texte de certification */
        .certification-text {
            font-size: 14px;
            color: #5a6a7a;
            line-height: 1.7;
            max-width: 600px;
            margin: 0 auto 18px;
            text-align: center;
        }
        
        /* Section Badge */
        .badge-section {
            margin: 15px 0;
        }
        
        .badge-container {
            display: inline-block;
            background: linear-gradient(135deg, #fef9e7 0%, #f9e79f 100%);
            border: 2px solid #c9a227;
            border-radius: 10px;
            padding: 12px 30px;
        }
        
        .badge-label {
            font-size: 11px;
            color: #7d6608;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }
        
        .badge-name {
            font-size: 20px;
            font-weight: bold;
            color: #7d6608;
            margin: 5px 0;
        }
        
        .badge-score {
            font-size: 12px;
            color: #9a7d0a;
            font-weight: 600;
        }
        
        /* Section pied de page - Signature à droite */
        .footer-section {
            position: absolute;
            bottom: 45px;
            right: 70px;
            text-align: center;
        }
        
        /* Date et lieu */
        .info-text {
            font-size: 13px;
            color: #5a6a7a;
            margin-bottom: 10px;
        }
        
        .info-value {
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 1px dotted #999;
            padding: 0 15px;
        }
        
        /* Signature */
        .signature-block {
            text-align: center;
        }
        
        .signature-label {
            font-size: 11px;
            color: #5a6a7a;
            font-style: italic;
            margin-bottom: 3px;
        }
        
        .signature-line {
            width: 180px;
            border-top: 1px solid #2c3e50;
            margin: 0 auto 5px;
        }
        
        .signature-name {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }
        
        .signature-title {
            font-size: 11px;
            color: #5a6a7a;
        }
        
        .signature-image {
            height: 40px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-container">
            <!-- Bordure dorée -->
            <div class="golden-border"></div>
            
            <!-- Coins décoratifs -->
            <div class="corner corner-tl"></div>
            <div class="corner corner-tr"></div>
            <div class="corner corner-bl"></div>
            <div class="corner corner-br"></div>
            
            <!-- Points décoratifs aux coins -->
            <div class="corner-dot dot-tl"></div>
            <div class="corner-dot dot-tr"></div>
            <div class="corner-dot dot-bl"></div>
            <div class="corner-dot dot-br"></div>
            
            <!-- Filigrane - Logo Simplon -->
            @php
                $logoPath = null;
                if ($settings->logo_path) {
                    $fullPath = public_path('storage/' . $settings->logo_path);
                    if (file_exists($fullPath)) {
                        $logoPath = $fullPath;
                    }
                }
            @endphp
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="" class="watermark">
            @endif
            
            <!-- Contenu -->
            <div class="content">
                <!-- Titre -->
                <h1 class="main-title">ATTESTATION</h1>
                <h2 class="sub-title">DE LABELLISATION</h2>
                
                <!-- Texte d'attribution -->
                <p class="awarded-to">Cette attestation est décernée à :</p>
                
                <!-- Nom du formateur -->
                <div class="recipient-name">
                    @php
                        // Construire le nom complet : prénom + nom
                        $firstName = trim($formateur->first_name ?? '');
                        $lastName = trim($formateur->name ?? '');
                        
                        if (!empty($firstName) && !empty($lastName)) {
                            $fullName = $firstName . ' ' . $lastName;
                        } elseif (!empty($firstName)) {
                            $fullName = $firstName;
                        } elseif (!empty($lastName)) {
                            $fullName = $lastName;
                        } else {
                            $fullName = 'Formateur';
                        }
                    @endphp
                    {{ $fullName }}
                </div>
                <div class="name-underline"></div>
                
                <!-- Texte de certification -->
                <p class="certification-text">
                    {{ $settings->attestation_text ?? 'Nous certifions que le/la formateur(trice) mentionné(e) ci-dessus a satisfait aux exigences du processus de labellisation et s\'est vu attribuer le badge correspondant à son niveau de compétences.' }}
                </p>
                
                <!-- Badge -->
                <div class="badge-section">
                    <div class="badge-container">
                        <div class="badge-label">Badge obtenu</div>
                        <div class="badge-name">{{ $badge->getEmoji() }} {{ $badge->label }}</div>
                        <div class="badge-score">Score final : {{ number_format($score, 2) }}/20</div>
                    </div>
                </div>
            </div>
            
            <!-- Pied de page - Date et Signature à droite -->
            <div class="footer-section">
                <!-- Date et lieu au-dessus de la signature -->
                <p class="info-text">
                    Fait à <span class="info-value">Dakar</span>, le <span class="info-value">{{ $date->format('d/m/Y') }}</span>
                </p>
                
                <!-- Signature -->
                <div class="signature-block">
                    <div class="signature-label">Signature</div>
                    @php
                        $signaturePath = null;
                        if ($settings->signature_path) {
                            $fullPath = public_path('storage/' . $settings->signature_path);
                            if (file_exists($fullPath)) {
                                $signaturePath = $fullPath;
                            }
                        }
                    @endphp
                    @if($signaturePath)
                        <img src="{{ $signaturePath }}" alt="Signature" class="signature-image">
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $settings->director_name ?? 'MIHIN Hugues Aimé' }}</div>
                    <div class="signature-title">{{ $settings->director_title ?? 'Directeur Simplon Afrique' }}</div>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>
