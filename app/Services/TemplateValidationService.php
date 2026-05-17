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

    public function validate(string $platform, string $content): void
    {

        if (preg_match('/https?:\/\//i', $content)) {
            throw new InvalidArgumentException(__("Template content cannot contain direct links"));
        }

        $variables = $this->extractVariables($content);
        $allowedKeys = $this->registry->getAllowedKeys($platform);

        $invalidVariables = array_diff($variables, $allowedKeys);

        if (!empty($invalidVariables)) {
            throw new InvalidArgumentException(__("Invalid variables found in template: :variables", ['variables' => implode(', ', $invalidVariables)]));
        }
    }
}
