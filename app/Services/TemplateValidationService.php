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

        if ($content !== strip_tags($content)) {
            throw new InvalidArgumentException(__("Template content cannot contain HTML tags or scripts."));
        }

        if (preg_match('/(https?:\/\/|www\.)/i', $content)) {
            throw new InvalidArgumentException(__("Template content cannot contain direct links."));
        }

        $variables = $this->extractVariables($content);
        $allowedKeys = $this->registry->getAllowedKeys($platform);
        $requiredKeys = $this->registry->getRequiredKeys($platform);

        $invalidVariables = array_diff($variables, $allowedKeys);

        if (!empty($invalidVariables)) {
            throw new InvalidArgumentException(__("Invalid variables found in template: :variables", ['variables' => implode(', ', $invalidVariables)]));
        }

        $missingRequired = array_diff($requiredKeys, $variables);
        
        if (!empty($missingRequired)) {
            $missingLabels = $this->registry->getLabelsForKeys($platform, $missingRequired);
            throw new InvalidArgumentException(__("Missing required variables in template: :variables", ['variables' => implode(', ', $missingLabels)]));
        }
    }
}
