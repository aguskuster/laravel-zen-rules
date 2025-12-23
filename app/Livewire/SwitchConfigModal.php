<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\SwitchService;
use Livewire\Component;

/**
 * Switch Configuration Modal Component
 *
 * Handles the UI for configuring switch conditions.
 * This is a child component of GraphEditor.
 *
 * @package App\Livewire
 * @author  Your Team
 */
class SwitchConfigModal extends Component
{
    /**
     * Switch component ID being edited
     *
     * @var string
     */
    public string $switchId;

    /**
     * Switch conditions
     *
     * @var array<int, array<string, mixed>>
     */
    public array $conditions = [];

    /**
     * Switch mode (first-match or all-matches)
     *
     * @var string
     */
    public string $mode = 'first-match';

    /**
     * Available operators for expressions
     *
     * @var array<int, array<string, string>>
     */
    public array $operators = [];

    /**
     * Mount the component
     *
     * @param string $switchId
     * @param array<int, array<string, mixed>> $conditions
     * @param string $mode
     * @return void
     */
    public function mount(string $switchId, array $conditions, string $mode = 'first-match'): void
    {
        $this->switchId = $switchId;
        $this->conditions = $conditions;
        $this->mode = $mode;

        $switchService = app(SwitchService::class);
        $this->operators = $switchService->getAvailableOperators();
    }

    /**
     * Get syntax-highlighted expression
     *
     * @param string $expression
     * @return string
     */
    public function getHighlightedExpression(string $expression): string
    {
        $switchService = app(SwitchService::class);
        return $switchService->highlightExpression($expression);
    }

    /**
     * Validate an expression
     *
     * @param string $expression
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateExpression(string $expression): array
    {
        $switchService = app(SwitchService::class);
        return $switchService->validateExpression($expression);
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.switch-config-modal');
    }
}

