<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Graph Component Data Transfer Object
 * 
 * Represents a single component in the graph editor.
 * This is an immutable value object that ensures data consistency.
 * 
 * @package App\DataTransferObjects
 * @author  Your Team
 */
final readonly class GraphComponentData
{
    /**
     * Create a new Graph Component instance
     * 
     * @param string $id Unique identifier for the component
     * @param string $type Component type (request, response, switch, etc.)
     * @param int $x X coordinate on the canvas
     * @param int $y Y coordinate on the canvas
     * @param array<string, mixed> $config Component-specific configuration
     */
    public function __construct(
        public string $id,
        public string $type,
        public int $x,
        public int $y,
        public array $config = [],
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
            x: (int) $data['x'],
            y: (int) $data['y'],
            config: $data['config'] ?? [],
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
            'x' => $this->x,
            'y' => $this->y,
            'config' => $this->config,
        ];
    }

    /**
     * Create a copy with updated position
     * 
     * @param int $x New X coordinate
     * @param int $y New Y coordinate
     * @return self
     */
    public function withPosition(int $x, int $y): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            x: $x,
            y: $y,
            config: $this->config,
        );
    }

    /**
     * Create a copy with updated configuration
     * 
     * @param array<string, mixed> $config New configuration
     * @return self
     */
    public function withConfig(array $config): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            x: $this->x,
            y: $this->y,
            config: $config,
        );
    }
}

