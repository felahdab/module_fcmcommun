<!-- filepath: Modules/FcmCentral/resources/views/pdf/livret-fcm.blade.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Livret FCM - {{ $nom_complet }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 15px;
        }
        
        .header h1 {
            color: #1e40af;
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #3b82f6;
            margin: 0;
            font-size: 14px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-section {
            background-color: #f9fafb;
            padding: 12px;
            border-left: 4px solid #3b82f6;
        }
        
        .info-section h3 {
            margin: 0 0 8px 0;
            color: #1e40af;
            font-size: 12px;
            font-weight: bold;
        }
        
        .info-item {
            margin-bottom: 4px;
        }
        
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            border: 2px solid #e5e7eb;
        }
        
        .stat-box.excellent { border-color: #10b981; background-color: #ecfdf5; }
        .stat-box.bon { border-color: #f59e0b; background-color: #fffbeb; }
        .stat-box.insuffisant { border-color: #ef4444; background-color: #fef2f2; }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .stat-value.excellent { color: #059669; }
        .stat-value.bon { color: #d97706; }
        .stat-value.insuffisant { color: #dc2626; }
        
        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 20px;
            break-inside: avoid;
        }
        
        .section-title {
            background-color: #1e40af;
            color: white;
            padding: 8px 12px;
            margin: 0 0 10px 0;
            font-weight: bold;
            font-size: 12px;
        }
        
        .activites-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .activites-table th {
            background-color: #3b82f6;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2563eb;
        }
        
        .activites-table td {
            padding: 5px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }
        
        .activites-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        
        .status-validee {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .status-non-validee {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .competence-group {
            margin-bottom: 12px;
            padding: 8px;
            background-color: #f3f4f6;
            border-left: 3px solid #6b7280;
        }
        
        .competence-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 6px;
            font-size: 10px;
        }
        
        .footer {
            position: fixed;
            bottom: 15px;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @page {
            margin: 1.5cm;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>LIVRET DE FORMATION CONTINUE DES MARINS</h1>
        <h2>{{ $nom_complet }} - {{ $grade }}</h2>
    </div>
 
    <!-- Informations générales -->
    <div class="info-grid">
        <div class="info-section">
            <h3>INFORMATIONS PERSONNELLES</h3>
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
            <div class="info-item">
                <span class="info-label">Unité :</span> {{ $unite }}
            </div>
        </div>
        
        <div class="info-section">
            <h3>FORMATION FCM</h3>
            <div class="info-item">
                <span class="info-label">Cohorte :</span> {{ $cohorte }}
            </div>
            <div class="info-item">
                <span class="info-label">Mentor :</span> {{ $mentor }}
            </div>
            <div class="info-item">
                <span class="info-label">Parcours :</span> {{ $parcours }}
            </div>
            <div class="info-item">
                <span class="info-label">Date génération :</span> {{ $date_generation }}
            </div>
        </div>
    </div>
 
    <!-- Statistiques -->
    <div class="stats-container">
        <div class="stat-box {{ $taux_global >= 80 ? 'excellent' : ($taux_global >= 50 ? 'bon' : 'insuffisant') }}">
            <div class="stat-value {{ $taux_global >= 80 ? 'excellent' : ($taux_global >= 50 ? 'bon' : 'insuffisant') }}">
                {{ number_format($taux_global, 1) }}%
            </div>
            <div class="stat-label">Taux Global</div>
        </div>
        
        <div class="stat-box {{ $taux_stage >= 80 ? 'excellent' : ($taux_stage >= 50 ? 'bon' : 'insuffisant') }}">
            <div class="stat-value {{ $taux_stage >= 80 ? 'excellent' : ($taux_stage >= 50 ? 'bon' : 'insuffisant') }}">
                {{ number_format($taux_stage, 1) }}%
            </div>
            <div class="stat-label">Taux Stage</div>
        </div>
        
        <div class="stat-box {{ $taux_activite >= 80 ? 'excellent' : ($taux_activite >= 50 ? 'bon' : 'insuffisant') }}">
            <div class="stat-value {{ $taux_activite >= 80 ? 'excellent' : ($taux_activite >= 50 ? 'bon' : 'insuffisant') }}">
                {{ number_format($taux_activite, 1) }}%
            </div>
            <div class="stat-label">Taux Activité</div>
        </div>
    </div>
 
    <!-- Détail du parcours -->
    @if(!empty($parcours_data))
        <div class="section">
            <h2 class="section-title">DÉTAIL DU PARCOURS DE FORMATION</h2>
            
            @php 
                $activites = app(\Modules\FcmCommun\Services\LivretPdfService::class)->getActivitesValidees($parcours_data);
                $activitesParCompetence = collect($activites)->groupBy('competence');
            @endphp
            
            @foreach($activitesParCompetence as $competence => $activitesCompetence)
                <div class="competence-group">
                    <div class="competence-title">{{ $competence }}</div>
                    
                    <table class="activites-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Savoir-faire</th>
                                <th style="width: 30%;">Activité</th>
                                <th style="width: 10%;">Statut</th>
                                <th style="width: 15%;">Date</th>
                                <th style="width: 20%;">Commentaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach($activitesCompetence as $activite)
                                <tr>
                                    <td>{{ $activite['savoirfaire'] }}</td>
                                    <td>{{ $activite['activite'] }}</td>
                                    <td>
                                        <span class="status-badge {{ $activite['validee'] ? 'status-validee' : 'status-non-validee' }}">
                                            {{ $activite['validee'] ? 'Validée' : 'Non validée' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($activite['date_validation'])
                                            {{ \Carbon\Carbon::parse($activite['date_validation'])->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($activite['commentaire'], 50) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif
 
    <!-- Pied de page -->
    <div class="footer">
        <p>Document généré le {{ $date_generation }} - Livret FCM {{ $nom_complet }} - Page <script type="text/php">echo $PAGE_NUM;</script></p>
    </div>
</body>
</html>
