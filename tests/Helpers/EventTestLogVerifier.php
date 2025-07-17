<?php


use Illuminate\Support\Facades\Log;
//use Mockery;

class EventTestLogVerifier
{
    /**
     * Vérifie les logs selon le type de test
     */
    public static function verifierLogs($logSpy, string $casTest, array $data, $record, string $prefix, string $marinUuid): void
    {
        // Logs communs à tous les tests
        self::verifierLogsCommuns($logSpy, $data, $record, $prefix);
        
        // Logs spécifiques selon le type
        self::verifierLogsSpecifiques($logSpy, $casTest);
        
        // Logs de suite pour les cas normaux
        if ($casTest !== 'ERREUR') {
            self::verifierLogsSuite($logSpy, $casTest, $prefix, $marinUuid);
        }
    }

    private static function verifierLogsCommuns($logSpy, array $data, $record, string $prefix): void
    {
        $logSpy->shouldHaveReceived('info')
            ->with('Prefixe:', ['prefix' => $prefix])
            ->once();

        $logSpy->shouldHaveReceived('info')
            ->with('EventTrigger appele', ['data' => $data, 'record' => $record])
            ->once();

        $logSpy->shouldHaveReceived('info')
            ->with('buildEventData appele', ['data' => $data, 'record' => $record])
            ->once();
    }

    private static function verifierLogsSpecifiques($logSpy, string $casTest): void
    {
        $logsSpecifiques = [
            'MENTOR' => 'EventBuilder Data handleMentor genere',
            'COHORTE' => 'EventBuilder Data handleCohorte genere',
            'CREATION_PARCOURS_SERIALISE' => 'EventBuilder Data handleCreationParcoursSerialise genere',
            'ATTRIBUER_PARCOURS_SERIALISE_MARIN' => 'EventBuilder Data handleParcoursSerialiseMarin genere',
        ];

        if ($casTest === 'MENTOR_COHORTE') {
            $logSpy->shouldHaveReceived('info')
                ->with('EventBuilder Data handleMentor genere')
                ->once();
            $logSpy->shouldHaveReceived('info')
                ->with('EventBuilder Data handleCohorte genere')
                ->once();
        } elseif (isset($logsSpecifiques[$casTest])) {
            $logSpy->shouldHaveReceived('info')
                ->with($logsSpecifiques[$casTest])
                ->once();
        }
    }

    private static function verifierLogsSuite($logSpy, string $casTest, string $prefix, string $marinUuid): void
    {
        // Vérification "Donnees generes"
        if ($casTest !== 'CREATION_PARCOURS_SERIALISE') {
            $logSpy->shouldHaveReceived('info')
                ->with('Donnees generes', Mockery::on(function ($context) use ($marinUuid) {
                    return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
                }))
                ->once();   
        } else {
            $logSpy->shouldHaveReceived('info')
                ->with('Donnees generes', Mockery::type('array'))
                ->once();   
        }

        $logSpy->shouldHaveReceived('info')
            ->with('Event cree', Mockery::type('array'))
            ->once();

        $logSpy->shouldHaveReceived('info')
            ->with('CreateFromUserGeneratedEvent Array cree')
            ->once();

        // Logs des listeners spécifiques
        self::verifierLogsListeners($logSpy, $casTest, $marinUuid);

        // Log fcm_marin
        self::verifierLogsFcmMarin($logSpy, $casTest, $prefix, $marinUuid);
    }

    private static function verifierLogsListeners($logSpy, string $casTest, string $marinUuid): void
    {
        $listeners = [
            'MENTOR' => ['MentorMarinListener appele'],
            'COHORTE' => ['CohorteMarinListener appele'],
            'MENTOR_COHORTE' => ['MentorMarinListener appele', 'CohorteMarinListener appele']
        ];

        if (isset($listeners[$casTest])) {
            foreach ($listeners[$casTest] as $listener) {
                $logSpy->shouldHaveReceived('info')
                    ->with($listener, Mockery::on(function ($context) use ($marinUuid) {
                        return isset($context['marinUuid']) && $context['marinUuid'] === $marinUuid;
                    }))
                    ->once();
            }
        }
    }

    private static function verifierLogsFcmMarin($logSpy, string $casTest, string $prefix, string $marinUuid): void
    {
        if ($casTest !== 'CREATION_PARCOURS_SERIALISE') {
            $expectedCalls = ($casTest === 'MENTOR_COHORTE') ? 2 : 1;
            
            $logSpy->shouldHaveReceived('info')
                ->with($prefix.'fcm_marin  :', Mockery::on(function ($context) use ($marinUuid) {
                    return isset($context['uuid']) && $context['uuid'] === $marinUuid;
                }))
                ->times($expectedCalls);
        }
    }
}


