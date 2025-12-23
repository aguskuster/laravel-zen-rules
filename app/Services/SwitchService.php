<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\SwitchConditionData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Switch Service
 * 
 * Handles business logic for switch components and their conditions.
 * Manages condition creation, validation, and expression parsing.
 * 
 * @package App\Services
 * @author  Your Team
 */
final class SwitchService
{
    /**
     * Create a new condition
     * 
     * @param string $type Condition type ('if', 'elseif', 'else')
     * @param string|null $expression Optional expression
     * @return SwitchConditionData
     */
    public function createCondition(
        string $type,
        ?string $expression = null
    ): SwitchConditionData {
        $id = $this->generateConditionId();

        return new SwitchConditionData(
            id: $id,
            type: $type,
            expression: $expression,
        );
    }

    /**
     * Validate condition expression
     * 
     * @param string $expression
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateExpression(string $expression): array
    {
        $errors = [];

        // Check for unmatched quotes
        if (substr_count($expression, "'") % 2 !== 0) {
            $errors[] = 'Unmatched single quote';
        }

        if (substr_count($expression, '"') % 2 !== 0) {
            $errors[] = 'Unmatched double quote';
        }

        // Check for unmatched parentheses
        if (substr_count($expression, '(') !== substr_count($expression, ')')) {
            $errors[] = 'Unmatched parentheses';
        }

        // Check for invalid operators
        if (preg_match('/={3,}|!={2,}/', $expression)) {
            $errors[] = 'Invalid operator (use == or !=)';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Highlight expression syntax for display
     * 
     * @param string $expression
     * @return string HTML with syntax highlighting
     */
    public function highlightExpression(string $expression): string
    {
        if (empty($expression)) {
            return '<span style="color: #94a3b8;">Enter expression...</span>';
        }

        // Escape HTML
        $highlighted = htmlspecialchars($expression, ENT_QUOTES, 'UTF-8');

        // Highlight strings (single and double quotes) - green
        $highlighted = preg_replace(
            "/('([^']*)'|\"([^\"]*)\")/",
            '<span style="color: #10b981;">$1</span>',
            $highlighted
        );

        // Highlight operators - purple/bold
        $highlighted = preg_replace(
            '/(\s+)(==|!=|>=|<=|>|<|IN|NOT IN|&&|\|\|)(\s+)/',
            '$1<span style="color: #a855f7; font-weight: bold;">$2</span>$3',
            $highlighted
        );

        // Highlight numbers - blue
        $highlighted = preg_replace(
            '/\b(\d+\.?\d*)\b/',
            '<span style="color: #3b82f6;">$1</span>',
            $highlighted
        );

        // Highlight property access (dot notation) - orange
        $highlighted = preg_replace(
            '/\b([a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_.]*)\b/',
            '<span style="color: #f97316;">$1</span>',
            $highlighted
        );

        return $highlighted;
    }

    /**
     * Get available operators
     * 
     * @return array<array{value: string, label: string}>
     */
    public function getAvailableOperators(): array
    {
        return [
            ['value' => ' == ', 'label' => '== (equals)'],
            ['value' => ' != ', 'label' => '!= (not equals)'],
            ['value' => ' > ', 'label' => '> (greater than)'],
            ['value' => ' < ', 'label' => '< (less than)'],
            ['value' => ' >= ', 'label' => '>= (greater or equal)'],
            ['value' => ' <= ', 'label' => '<= (less or equal)'],
            ['value' => ' IN ', 'label' => 'IN (contains)'],
            ['value' => ' NOT IN ', 'label' => 'NOT IN (not contains)'],
            ['value' => ' && ', 'label' => '&& (and)'],
            ['value' => ' || ', 'label' => '|| (or)'],
        ];
    }

    /**
     * Generate a unique condition ID
     * 
     * @return string
     */
    private function generateConditionId(): string
    {
        return 'cond-' . Str::random(8);
    }
}

