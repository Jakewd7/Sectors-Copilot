<?php

namespace Modules\Agent\Tools\Contracts;

interface AgentToolInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function getSchema(): array;
    public function execute(array $parameters): array;
}