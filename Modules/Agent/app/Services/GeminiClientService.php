<?php

namespace Modules\Agent\Services;

use Gemini;
use Gemini\Client;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
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
            $rawSchema = $tool->getSchema();

            $declarations[] = new FunctionDeclaration(
                name: $tool->getName(),
                description: $tool->getDescription(),
                parameters: $this->buildSchema($rawSchema)
            );
        }

        return $declarations;
    }

    protected function buildSchema(array $schema): Schema
    {
        $type = match (strtolower($schema['type'] ?? 'string')) {
            'object' => DataType::OBJECT,
            'array' => DataType::ARRAY ,
            'integer' => DataType::INTEGER,
            'number' => DataType::NUMBER,
            'boolean' => DataType::BOOLEAN,
            default => DataType::STRING,
        };

        $properties = [];
        if (!empty($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $key => $prop) {
                $properties[$key] = $this->buildSchema($prop);
            }
        }

        $items = null;
        if (!empty($schema['items']) && is_array($schema['items'])) {
            $items = $this->buildSchema($schema['items']);
        }

        return new Schema(
            type: $type,
            description: $schema['description'] ?? null,
            properties: !empty($properties) ? $properties : null,
            required: $schema['required'] ?? null,
            items: $items
        );
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