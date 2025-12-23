<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Switch Condition Data Transfer Object
 * 
 * Represents a single condition within a switch component.
 * Immutable value object for type safety and consistency.
 * 
 * @package App\DataTransferObjects
 * @author  Your Team
 */
final readonly class SwitchConditionData
{
    /**
     * Create a new Switch Condition instance
     * 
     * @param string $id Unique identifier for this condition
     * @param string $type Condition type: 'if', 'elseif', or 'else'
     * @param string|null $expression The condition expression (null for 'else')
     * @param string|null $targetComponentId Component to route to if condition is true
     */
    public function __construct(
        public string $id,
        public string $type,
        public ?string $expression = null,
        public ?string $targetComponentId = null,
    ) {
    }

    /**
     * Create from array
     * 
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: $data['type'],
            expression: $data['expression'] ?? null,
            targetComponentId: $data['targetComponentId'] ?? null,
        );
    }

    /**
     * Convert to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'expression' => $this->expression,
            'targetComponentId' => $this->targetComponentId,
        ];
    }

    /**
     * Check if this is an 'else' condition
     * 
     * @return bool
     */
    public function isElse(): bool
    {
        return $this->type === 'else';
    }

    /**
     * Check if this condition has a valid expression
     * 
     * @return bool
     */
    public function hasValidExpression(): bool
    {
        if ($this->isElse()) {
            return true; // Else doesn't need an expression
        }

        return !empty($this->expression);
    }

    /**
     * Get human-readable label
     * 
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this->type) {
            'if' => 'If',
            'elseif' => 'Else If',
            'else' => 'Else',
            default => ucfirst($this->type),
        };
    }
}

