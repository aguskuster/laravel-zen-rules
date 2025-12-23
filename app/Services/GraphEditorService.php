<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\GraphComponentData;
use App\DataTransferObjects\ConnectionData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Graph Editor Service
 * 
 * Handles all business logic for the graph editor.
 * This service is responsible for managing components and connections.
 * 
 * @package App\Services
 * @author  Your Team
 */
final class GraphEditorService
{
    /**
     * Create a new component
     * 
     * @param string $type Component type
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @param array<string, mixed> $config Optional configuration
     * @return GraphComponentData
     */
    public function createComponent(
        string $type,
        int $x,
        int $y,
        array $config = []
    ): GraphComponentData {
        $id = $this->generateComponentId($type);

        return new GraphComponentData(
            id: $id,
            type: $type,
            x: $x,
            y: $y,
            config: $config,
        );
    }

    /**
     * Update component position
     * 
     * @param GraphComponentData $component
     * @param int $x New X coordinate
     * @param int $y New Y coordinate
     * @return GraphComponentData
     */
    public function updateComponentPosition(
        GraphComponentData $component,
        int $x,
        int $y
    ): GraphComponentData {
        return $component->withPosition($x, $y);
    }

    /**
     * Update component configuration
     * 
     * @param GraphComponentData $component
     * @param array<string, mixed> $config New configuration
     * @return GraphComponentData
     */
    public function updateComponentConfig(
        GraphComponentData $component,
        array $config
    ): GraphComponentData {
        return $component->withConfig($config);
    }

    /**
     * Create a connection between components
     * 
     * @param string $fromComponentId Source component ID
     * @param string $toComponentId Target component ID
     * @param string $fromPosition Source position
     * @param string $toPosition Target position
     * @param string|null $fromConditionId Optional condition ID for switch components
     * @return ConnectionData
     */
    public function createConnection(
        string $fromComponentId,
        string $toComponentId,
        string $fromPosition = 'right',
        string $toPosition = 'left',
        ?string $fromConditionId = null
    ): ConnectionData {
        $id = $this->generateConnectionId();

        return new ConnectionData(
            id: $id,
            fromComponentId: $fromComponentId,
            toComponentId: $toComponentId,
            fromPosition: $fromPosition,
            toPosition: $toPosition,
            fromConditionId: $fromConditionId,
        );
    }

    /**
     * Validate if a connection is valid
     * 
     * @param string $fromComponentId
     * @param string $toComponentId
     * @return bool
     */
    public function isValidConnection(
        string $fromComponentId,
        string $toComponentId
    ): bool {
        // Can't connect to itself
        if ($fromComponentId === $toComponentId) {
            return false;
        }

        return true;
    }

    /**
     * Generate a unique component ID
     * 
     * @param string $type
     * @return string
     */
    private function generateComponentId(string $type): string
    {
        return $type . '-' . Str::random(8);
    }

    /**
     * Generate a unique connection ID
     * 
     * @return string
     */
    private function generateConnectionId(): string
    {
        return 'conn-' . Str::random(8);
    }
}

