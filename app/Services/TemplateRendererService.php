<?php

namespace App\Services;

class TemplateRendererService
{
    /**
     * Render template by replacing placeholders with values.
     */
    public function render(string $template, array $data): string
    {
        $search = [];
        $replace = [];

        foreach ($data as $key => $value) {
            $search[] = '{{' . $key . '}}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $template);
    }
}
