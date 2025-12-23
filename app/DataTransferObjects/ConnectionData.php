<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Connection Data Transfer Object
 * 
 * Represents a connection between two components in the graph.
 * Immutable value object for type safety.
 * 
 * @package App\DataTransferObjects
 * @author  Your Team
 */
final readonly class ConnectionData
{
    /**
     * Create a new Connection instance
     * 
     * @param string $id Unique identifier for the connection
     * @param string $fromComponentId Source component ID
     * @param string $toComponentId Target component ID
     * @param string $fromPosition Connection point on source (right, left, top, bottom)
     * @param string $toPosition Connection point on target (right, left, top, bottom)
     * @param string|null $fromConditionId Condition ID if source is a switch component
     */
    public function __construct(
        public string $id,
        public string $fromComponentId,
        public string $toComponentId,
        public string $fromPosition = 'right',
        public string $toPosition = 'left',
        public ?string $fromConditionId = null,
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
            fromComponentId: $data['fromComponentId'],
            toComponentId: $data['toComponentId'],
            fromPosition: $data['fromPosition'] ?? 'right',
            toPosition: $data['toPosition'] ?? 'left',
            fromConditionId: $data['fromConditionId'] ?? null,
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
            'fromComponentId' => $this->fromComponentId,
            'toComponentId' => $this->toComponentId,
            'fromPosition' => $this->fromPosition,
            'toPosition' => $this->toPosition,
            'fromConditionId' => $this->fromConditionId,
        ];
    }

    /**
     * Check if this connection is from a switch condition
     * 
     * @return bool
     */
    public function isFromSwitchCondition(): bool
    {
        return $this->fromConditionId !== null;
    }
}

