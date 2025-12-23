<?php

declare(strict_types=1);

namespace App\Livewire;

use App\DataTransferObjects\GraphComponentData;
use App\DataTransferObjects\ConnectionData;
use App\DataTransferObjects\SwitchConditionData;
use App\Services\GraphEditorService;
use App\Services\SwitchService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Collection;

/**
 * Graph Editor Livewire Component
 *
 * Main component for the visual graph editor.
 * Manages components, connections, and user interactions.
 *
 * Architecture:
 * - Uses DTOs for type-safe data transfer
 * - Delegates business logic to service classes
 * - Minimal JavaScript (only for drag-and-drop and canvas interactions)
 * - Livewire handles all state management
 *
 * @package App\Livewire
 * @author  Your Team
 */
class GraphEditor extends Component
{
    /**
     * Collection of graph components
     *
     * @var array<int, array<string, mixed>>
     */
    public array $components = [];

    /**
     * Collection of connections between components
     *
     * @var array<int, array<string, mixed>>
     */
    public array $connections = [];

    /**
     * Currently selected component ID
     *
     * @var string|null
     */
    public ?string $selectedComponentId = null;

    /**
     * Switch configuration modal state
     *
     * @var bool
     */
    public bool $showSwitchModal = false;

    /**
     * Current switch component being edited
     *
     * @var string|null
     */
    public ?string $editingSwitchId = null;

    /**
     * Expression configuration modal state
     *
     * @var bool
     */
    public bool $showExpressionModal = false;

    /**
     * Current expression component being edited
     *
     * @var string|null
     */
    public ?string $editingExpressionId = null;

    /**
     * Expression rows for the current component
     *
     * @var array<int, array{key: string, expression: string}>
     */
    public array $expressionRows = [];

    /**
     * Initialize the component
     *
     * @return void
     */
    public function mount(): void
    {
        // Initialize with empty state
        // In a real app, you might load from database here
    }

    /**
     * Add a new component to the canvas
     *
     * @param string $type Component type
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @return void
     */
    #[On('add-component')]
    public function addComponent(string $type, int $x, int $y): void
    {
        $service = app(GraphEditorService::class);

        $component = $service->createComponent($type, $x, $y);

        // Add default conditions for switch components
        if ($type === 'switch') {
            $switchService = app(SwitchService::class);
            $condition = $switchService->createCondition('if');

            $componentData = $component->toArray();
            $componentData['config']['conditions'] = [$condition->toArray()];
            $componentData['config']['mode'] = 'first-match';

            $this->components[] = $componentData;
        } else {
            $this->components[] = $component->toArray();
        }
    }

    /**
     * Update component position (called from JavaScript drag-and-drop)
     *
     * @param string $componentId
     * @param int $x
     * @param int $y
     * @return void
     */
    #[On('update-component-position')]
    public function updateComponentPosition(string $componentId, int $x, int $y): void
    {
        $this->components = collect($this->components)
            ->map(function ($componentData) use ($componentId, $x, $y) {
                if ($componentData['id'] === $componentId) {
                    $component = GraphComponentData::fromArray($componentData);
                    return $component->withPosition($x, $y)->toArray();
                }
                return $componentData;
            })
            ->toArray();
    }

    /**
     * Delete a component
     *
     * @param string $componentId
     * @return void
     */
    public function deleteComponent(string $componentId): void
    {
        // Remove component
        $this->components = collect($this->components)
            ->reject(fn($c) => $c['id'] === $componentId)
            ->values()
            ->toArray();

        // Remove related connections
        $this->connections = collect($this->connections)
            ->reject(fn($c) =>
                $c['fromComponentId'] === $componentId ||
                $c['toComponentId'] === $componentId
            )
            ->values()
            ->toArray();
    }

    /**
     * Open switch configuration modal
     *
     * @param string $componentId
     * @return void
     */
    public function openSwitchModal(string $componentId): void
    {
        $this->editingSwitchId = $componentId;
        $this->showSwitchModal = true;
    }

    /**
     * Close switch configuration modal
     *
     * @return void
     */
    public function closeSwitchModal(): void
    {
        $this->showSwitchModal = false;
        $this->editingSwitchId = null;
    }

    /**
     * Open expression configuration modal
     *
     * @param string $componentId
     * @return void
     */
    public function openExpressionModal(string $componentId): void
    {
        $this->editingExpressionId = $componentId;

        // Load existing expression rows from component config
        $component = collect($this->components)->firstWhere('id', $componentId);
        $this->expressionRows = $component['config']['rows'] ?? [
            ['key' => 'status', 'expression' => 'len(user.servers) > 2 ? "very-active" : len(user.servers) > 0 ? "active" : "inactive"'],
            ['key' => 'admin', 'expression' => 'user.role == "super" ? "admin" : "not admin"'],
        ];

        $this->showExpressionModal = true;
    }

    /**
     * Close expression configuration modal
     *
     * @return void
     */
    public function closeExpressionModal(): void
    {
        $this->showExpressionModal = false;
        $this->editingExpressionId = null;
        $this->expressionRows = [];
    }

    /**
     * Save expression configuration
     *
     * @param array<int, array{key: string, expression: string}> $rows
     * @return void
     */
    #[On('expression-config-saved')]
    public function saveExpressionConfig(array $data): void
    {
        $componentId = $data['componentId'];
        $rows = $data['rows'];

        $this->components = collect($this->components)
            ->map(function ($componentData) use ($componentId, $rows) {
                if ($componentData['id'] === $componentId) {
                    $componentData['config']['rows'] = $rows;
                }
                return $componentData;
            })
            ->toArray();

        $this->closeExpressionModal();
    }

    /**
     * Add a condition to a switch component
     *
     * @param string $type Condition type ('if', 'elseif', 'else')
     * @return void
     */
    public function addSwitchCondition(string $type): void
    {
        if (!$this->editingSwitchId) {
            return;
        }

        $switchService = app(SwitchService::class);

        $this->components = collect($this->components)
            ->map(function ($componentData) use ($type, $switchService) {
                if ($componentData['id'] === $this->editingSwitchId) {
                    $conditions = collect($componentData['config']['conditions'] ?? [])
                        ->map(fn($c) => SwitchConditionData::fromArray($c));

                    $conditions = $switchService->addCondition($conditions, $type);

                    $componentData['config']['conditions'] = $conditions
                        ->map(fn($c) => $c->toArray())
                        ->toArray();
                }
                return $componentData;
            })
            ->toArray();
    }

    /**
     * Remove a condition from a switch component
     *
     * @param string $conditionId
     * @return void
     */
    public function removeSwitchCondition(string $conditionId): void
    {
        if (!$this->editingSwitchId) {
            return;
        }

        $switchService = app(SwitchService::class);

        $this->components = collect($this->components)
            ->map(function ($componentData) use ($conditionId, $switchService) {
                if ($componentData['id'] === $this->editingSwitchId) {
                    $conditions = collect($componentData['config']['conditions'] ?? [])
                        ->map(fn($c) => SwitchConditionData::fromArray($c));

                    $conditions = $switchService->removeCondition($conditions, $conditionId);

                    $componentData['config']['conditions'] = $conditions
                        ->map(fn($c) => $c->toArray())
                        ->toArray();
                }
                return $componentData;
            })
            ->toArray();
    }

    /**
     * Update a condition's expression
     *
     * @param string $conditionId
     * @param string $expression
     * @return void
     */
    public function updateConditionExpression(string $conditionId, string $expression): void
    {
        if (!$this->editingSwitchId) {
            return;
        }

        $switchService = app(SwitchService::class);

        $this->components = collect($this->components)
            ->map(function ($componentData) use ($conditionId, $expression, $switchService) {
                if ($componentData['id'] === $this->editingSwitchId) {
                    $conditions = collect($componentData['config']['conditions'] ?? [])
                        ->map(fn($c) => SwitchConditionData::fromArray($c));

                    $conditions = $switchService->updateConditionExpression($conditions, $conditionId, $expression);

                    $componentData['config']['conditions'] = $conditions
                        ->map(fn($c) => $c->toArray())
                        ->toArray();
                }
                return $componentData;
            })
            ->toArray();
    }

    /**
     * Update switch mode (first-match or all-matches)
     *
     * @param string $mode
     * @return void
     */
    public function updateSwitchMode(string $mode): void
    {
        if (!$this->editingSwitchId) {
            return;
        }

        $this->components = collect($this->components)
            ->map(function ($componentData) use ($mode) {
                if ($componentData['id'] === $this->editingSwitchId) {
                    $componentData['config']['mode'] = $mode;
                }
                return $componentData;
            })
            ->toArray();
    }

    /**
     * Create a connection between components
     *
     * @param string $fromComponentId
     * @param string $toComponentId
     * @param string $fromPosition
     * @param string $toPosition
     * @param string|null $fromConditionId
     * @return void
     */
    #[On('create-connection')]
    public function createConnection(
        string $fromComponentId,
        string $toComponentId,
        string $fromPosition = 'right',
        string $toPosition = 'left',
        ?string $fromConditionId = null
    ): void {
        $service = app(GraphEditorService::class);

        if (!$service->isValidConnection($fromComponentId, $toComponentId)) {
            return;
        }

        $connection = $service->createConnection(
            $fromComponentId,
            $toComponentId,
            $fromPosition,
            $toPosition,
            $fromConditionId
        );

        $this->connections[] = $connection->toArray();
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.graph-editor');
    }
}
