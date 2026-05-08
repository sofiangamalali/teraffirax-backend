<?php

namespace App\Services;

use App\Support\TemplateVariableRegistry;
use InvalidArgumentException;

class TemplateValidationService
{
    public function __construct(protected TemplateVariableRegistry $registry)
    {
    }

    /**
     * Extract variables from template content.
     */
    public function extractVariables(string $content): array
    {
        preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $content, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Validate template variables against the registry.
     */
    public function validate(string $platform, string $content): void
    {
        $variables = $this->extractVariables($content);
        $allowedKeys = $this->registry->getAllowedKeys($platform);

        $invalidVariables = array_diff($variables, $allowedKeys);

        if (!empty($invalidVariables)) {
            throw new InvalidArgumentException("Invalid variables found in template: " . implode(', ', $invalidVariables));
        }
    }
}
