<?php

namespace Tests\Support;

/**
 * Gives the default and spoofed fetches different user agents, so a
 * test can tell which one was sent. The local .env may set both to
 * the same string.
 */
trait DistinctUserAgentsTrait
{
    private const DEFAULT_AGENT = 'Aggro test agent';
    private const SPOOF_AGENT   = 'Aggro spoofed test agent';

    /**
     * @var array<string, string|null>
     */
    private array $savedAgents = [];

    private function useDistinctUserAgents(): void
    {
        foreach (['UA_BMXFEED' => self::DEFAULT_AGENT, 'UA_SPOOF' => self::SPOOF_AGENT] as $key => $agent) {
            $this->savedAgents[$key] = $_ENV[$key] ?? null;
            $_ENV[$key]              = $agent;
        }
    }

    private function restoreUserAgents(): void
    {
        foreach ($this->savedAgents as $key => $agent) {
            if ($agent === null) {
                unset($_ENV[$key]);

                continue;
            }

            $_ENV[$key] = $agent;
        }

        $this->savedAgents = [];
    }
}
