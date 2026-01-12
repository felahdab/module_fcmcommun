{{-- filepath: Modules/FcmCentral/resources/views/pdf/livret-fcm.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Livret FCM - {{ $nom_complet }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
            padding: 20px;
        }

        .page-header {
            background: #1e40af;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .page-header h2 {
            font-size: 14px;
            opacity: 0.95;
        }

        /* INFORMATIONS - TABLE AU LIEU DE GRID */
        .info-table {
            width: 100%;
            
            
        }

        .info-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 5px;
        }

        .info-card {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            border-radius: 4px;
           
        }

        .info-card h3 {
            font-size: 11px;
            color: #1e40af;
            margin-bottom: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-item {
            margin-bottom: 4px;
            font-size: 9px;
        }

        .info-label {
            font-weight: bold;
            color: #4b5563;
            display: inline-block;
            width: 80px;
        }

        /* JALONS */
        .jalons-section {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .jalons-title {
            font-size: 12px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            text-align: center;
        }

        .jalons-table {
            width: 100%;
            border-collapse: collapse;
        }

        .jalons-table td {
            width: 33.33%;
            padding: 5px;
        }

        .jalon-box {
            background: white;
            padding: 8px;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #fbbf24;
        }

        .jalon-label {
            font-size: 8px;
            color: #92400e;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .jalon-date {
            font-size: 11px;
            color: #1f2937;
            font-weight: bold;
        }

        /* STATISTIQUES */
        .stats-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .stats-table td {
            width: 20%;
            padding: 5px;
        }

        .stat-card {
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            border: 2px solid #e5e7eb;
        }

        .stat-card.primary { border-color: #3b82f6; background: #eff6ff; }
        .stat-card.success { border-color: #10b981; background: #ecfdf5; }
        .stat-card.warning { border-color: #f59e0b; background: #fffbeb; }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .stat-value.primary { color: #1e40af; }
        .stat-value.success { color: #059669; }
        .stat-value.warning { color: #d97706; }

        .stat-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* SECTIONS */
        .section-title {
            background: #1e40af;
            color: white;
            padding: 8px 12px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 11px;
            border-radius: 4px;
        }

        .competence-block {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            break-inside: avoid;
        }

        .competence-header {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 10px;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }

        .affectation-tag {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            margin-right: 8px;
        }

        /* SAVOIR-FAIRE */
        .savoirfaire-block {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            break-inside: avoid;
        }

        .savoirfaire-block:last-child {
            border-bottom: none;
        }

        .sf-header {
            margin-bottom: 10px;
            padding: 8px;
            background: #f9fafb;
            border-left: 4px solid #6b7280;
            border-radius: 4px;
        }

        .sf-title-line {
            margin-bottom: 6px;
        }

        .sf-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            margin-right: 8px;
        }

        .sf-type.stage {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
        }

        .sf-type.compagnonnage {
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #6366f1;
        }

        .sf-type.ead {
            background: #ddd6fe;
            color: #5b21b6;
            border: 1px solid #8b5cf6;
        }

        .sf-type.activite {
            background: #dbeafe;
            color: #1e3a8a;
            border: 1px solid #3b82f6;
        }

        .sf-title {
            font-weight: bold;
            font-size: 10px;
            color: #111827;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .status-valide {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .status-non-valide {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        /* OBJECTIFS */
        .objectifs-section {
            margin-top: 8px;
            margin-left: 20px;
        }

        .objectifs-title {
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .objectif-item {
            padding: 6px 10px;
            background: #fafafa;
            border-left: 3px solid #9ca3af;
            margin-bottom: 4px;
            font-size: 9px;
            border-radius: 2px;
        }

        .objectif-item.valide {
            border-left-color: #10b981;
            background: #f0fdf4;
        }

        .objectif-icon {
            margin-right: 6px;
            font-weight: bold;
            font-size: 10px;
        }

        .objectif-icon.valide { color: #10b981; }
        .objectif-icon.non-valide { color: #ef4444; }

        .objectif-text {
            color: #374151;
        }

        .objectif-meta {
            color: #9ca3af;
            font-size: 8px;
            margin-top: 3px;
        }

        .footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        @page {
            margin: 1.5cm;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    {{-- EN-TÊTE --}}
    <div class="page-header">
        <h1>LIVRET DE FORMATION CONTINUE DES MARINS</h1>
        <h2>{{ $nom_complet }} - {{ $grade }}</h2>
    </div>

    {{-- INFORMATIONS GÉNÉRALES --}}
    <table class="info-table">
        <tr>
            <td>
                <div class="info-card">
                    <h3>👤 Identité</h3>
                    <div class="info-item">
                        <span class="info-label">Grade :</span> {{ $grade }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nom :</span> {{ $marin->nom }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Prénom :</span> {{ $marin->prenom }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Spécialité :</span> {{ $specialite }}
                    </div>
                </div>
            </td>
            <td>
                <div class="info-card">
                    <h3>📍 Affectation</h3>
                    <div class="info-item">
                        <span class="info-label">Unité :</span> {{ $unite }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cohorte :</span> {{ $cohorte }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Parcours :</span> {{ $parcours }}
                    </div>
                </div>
            </td>
            <td>
                <div class="info-card">
                    <h3>👨🏫 Encadrement</h3>
                    <div class="info-item">
                        <span class="info-label">Mentor :</span> {{ $mentor }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Généré le :</span> {{ $date_generation }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- JALONS FCM --}}
    <div class="jalons-section">
        <div class="jalons-title"> JALONS DE FORMATION</div>
        <table class="jalons-table">
            <tr>
                <td>
                    <div class="jalon-box">
                        <div class="jalon-label"> Sédimentation</div>
                        <div class="jalon-date">{{ $jalons['sedimentation'] }}</div>
                    </div>
                </td>
                <td>
                    <div class="jalon-box">
                        <div class="jalon-label"> Vert FCM</div>
                        <div class="jalon-date">{{ $jalons['fcm'] }}</div>
                    </div>
                </td>
                <td>
                    <div class="jalon-box">
                        <div class="jalon-label">Vert Compétence</div>
                        <div class="jalon-date">{{ $jalons['competence'] }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- STATISTIQUES --}}
    <table class="stats-table">
        <tr>
            <td>
                <div class="stat-card primary">
                    <div class="stat-value primary">{{ number_format($taux_global, 0) }}%</div>
                    <div class="stat-label">Progression</div>
                </div>
            </td>
            <td>
                <div class="stat-card success">
                    <div class="stat-value success">{{ $stats['sf_valides'] }}/{{ $stats['total_sf'] }}</div>
                    <div class="stat-label">Savoir-faire</div>
                </div>
            </td>
            <td>
                <div class="stat-card success">
                    <div class="stat-value success">{{ $stats['obj_valides'] }}/{{ $stats['total_obj'] }}</div>
                    <div class="stat-label">Objectifs</div>
                </div>
            </td>
            <td>
                <div class="stat-card warning">
                    <div class="stat-value warning">{{ $stats['stages_valides'] }}/{{ $stats['total_stages'] }}</div>
                    <div class="stat-label">Stages</div>
                </div>
            </td>
            {{-- <td>
                <div class="stat-card primary">
                    <div class="stat-value primary">{{ number_format($taux_activite, 0) }}%</div>
                    <div class="stat-label">Activités</div>
                </div>
            </td> --}}
        </tr>
    </table>

    {{-- DÉTAIL DU PARCOURS --}}
    <h2 class="section-title"> DÉTAIL DU PARCOURS - SAVOIR-FAIRE ET OBJECTIFS</h2>

    @php
        $detailles = app(\Modules\FcmCommun\Services\LivretPdfService::class)->getParcoursDetaille($parcours_data);
        $parCompetence = collect($detailles)->groupBy('competence');
    @endphp

    @foreach($parCompetence as $competence => $items)
        <div class="competence-block">
            <div class="competence-header">
                <span class="affectation-tag">{{ $items->first()['affectation'] }}</span>
                {{ $competence }}
            </div>

            @foreach($items as $item)
                <div class="savoirfaire-block">
                    {{-- SAVOIR-FAIRE --}}
                    <div class="sf-header">
                        <div class="sf-title-line">
                            @php
                                $typeLabel = match($item['type_sf']) {
                                    'S' => 'STAGE',
                                    'C' => 'COMPAGNONNAGE',
                                    'EAD' => 'E-LEARNING',
                                    default => 'ACTIVITÉ'
                                };
                                $typeClass = match($item['type_sf']) {
                                    'S' => 'stage',
                                    'C' => 'compagnonnage',
                                    'EAD' => 'ead',
                                    default => 'activite'
                                };
                            @endphp
                            <span class="sf-type {{ $typeClass }}">{{ $typeLabel }}</span>
                            <span class="status-badge {{ $item['sf_valide'] ? 'status-valide' : 'status-non-valide' }}">
                                {{ $item['sf_valide'] ? '✓ Validé' : '✗ Non validé' }}
                            </span>
                            <br/>
                            <span class="sf-title">{{ $item['savoirfaire'] }}</span>
                            <span class="sf-title">{{ $item['savoirfaireType'] }}</span>
                            
                            
                        </div>
                    </div>

                    {{-- OBJECTIFS DU SAVOIR-FAIRE --}}
                    @if(count($item['objectifs']) > 0)
                        <div class="objectifs-section">
                            <div class="objectifs-title">→ Objectifs associés :</div>
                            @foreach($item['objectifs'] as $obj)
                                <div class="objectif-item {{ $obj['valide'] ? 'valide' : '' }}">
                                    <div>
                                        <span class="objectif-icon {{ $obj['valide'] ? 'valide' : 'non-valide' }}">
                                            {{ $obj['valide'] ? '✓' : '○' }}
                                        </span>
                                        <span class="objectif-text">{{ $obj['libelle'] }}</span>
                                    </div>
                                    @if($obj['date_validation'] || $obj['commentaire'])
                                        <div class="objectif-meta">
                                            @if($obj['date_validation'])
                                                📅 {{ \Carbon\Carbon::parse($obj['date_validation'])->format('d/m/Y') }}
                                            @endif
                                            @if($obj['commentaire'])
                                                | 💬 {{ Str::limit($obj['commentaire'], 80) }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- PIED DE PAGE --}}
    <div class="footer">
        Document généré le {{ $date_generation }} - Livret FCM {{ $nom_complet }}
    </div>
</body>
</html>
