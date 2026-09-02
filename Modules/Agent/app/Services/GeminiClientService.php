<?php

namespace Modules\Agent\Services;

use Gemini;
use Gemini\Client;
use Modules\Agent\Tools\Contracts\AgentToolInterface;

class GeminiClientService
{
    protected Client $client;
    protected string $model;
    protected array $registeredTools = [];

    public function __construct()
    {
        $this->client = Gemini::client(config('agent.api_key'));
        $this->model = config('agent.model', 'gemini-2.5-flash');
    }

    public function registerTool(AgentToolInterface $tool): void
    {
        $this->registeredTools[$tool->getName()] = $tool;
    }

    public function getRegisteredTools(): array
    {
        return $this->registeredTools;
    }

    public function getToolDeclarations(): array
    {
        $declarations = [];
        foreach ($this->registeredTools as $tool) {
            $declarations[] = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'parameters' => $tool->getSchema(),
            ];
        }
        return $declarations;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getModel(): string
    {
        return $this->model;
    }
}