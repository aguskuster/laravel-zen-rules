<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

/**
 * Expression Configuration Component
 *
 * Handles the UI for configuring key-value expressions with syntax highlighting.
 * This component allows users to define expressions using a key-value format.
 *
 * @package App\Livewire
 */
class ExpressionConfig extends Component
{
    /**
     * Component ID being edited
     *
     * @var string
     */
    public string $componentId;

    /**
     * Expression rows (key-value pairs)
     *
     * @var array<int, array{key: string, expression: string}>
     */
    public array $rows = [];

    /**
     * Whether the modal is shown
     *
     * @var bool
     */
    public bool $show = false;

    /**
     * Available operators for expressions
     *
     * @var array<int, array{value: string, label: string}>
     */
    public array $operators = [
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

    /**
     * Mount the component
     *
     * @param string $componentId
     * @param array<int, array{key: string, expression: string}> $rows
     * @return void
     */
    public function mount(string $componentId, array $rows = []): void
    {
        $this->componentId = $componentId;
        $this->rows = empty($rows) ? [
            ['key' => 'status', 'expression' => 'len(user.servers) > 2 ? "very-active" : len(user.servers) > 0 ? "active" : "inactive"'],
            ['key' => 'admin', 'expression' => 'user.role == "super" ? "admin" : "not admin"'],
        ] : $rows;
    }

    /**
     * Add a new row
     *
     * @return void
     */
    public function addRow(): void
    {
        $this->rows[] = ['key' => '', 'expression' => ''];
    }

    /**
     * Remove a row
     *
     * @param int $index
     * @return void
     */
    public function removeRow(int $index): void
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }

    /**
     * Update a row's key
     *
     * @param int $index
     * @param string $key
     * @return void
     */
    public function updateKey(int $index, string $key): void
    {
        if (isset($this->rows[$index])) {
            $this->rows[$index]['key'] = $key;
        }
    }

    /**
     * Update a row's expression
     *
     * @param int $index
     * @param string $expression
     * @return void
     */
    public function updateExpression(int $index, string $expression): void
    {
        if (isset($this->rows[$index])) {
            $this->rows[$index]['expression'] = $expression;
        }
    }

    /**
     * Save the configuration
     *
     * @return void
     */
    public function save(): void
    {
        $this->dispatch('expression-config-saved', [
            'componentId' => $this->componentId,
            'rows' => $this->rows,
        ]);

        $this->show = false;
    }

    /**
     * Close the modal
     *
     * @return void
     */
    public function close(): void
    {
        $this->show = false;
        $this->dispatch('expression-config-closed');
    }

    /**
     * Highlight expression syntax
     *
     * @param string $expression
     * @return string
     */
    public function highlightExpression(string $expression): string
    {
        if (empty($expression)) {
            return '<span class="text-slate-400">Enter expression...</span>';
        }

        // Highlight strings
        $highlighted = preg_replace(
            '/"([^"]*)"/',
            '<span class="text-green-600 dark:text-green-400">"$1"</span>',
            $expression
        );

        // Highlight operators
        $operators = ['==', '!=', '>=', '<=', '>', '<', '&&', '||', '?', ':', 'IN', 'NOT IN'];
        foreach ($operators as $op) {
            $highlighted = str_replace(
                $op,
                '<span class="text-blue-600 dark:text-blue-400">' . $op . '</span>',
                $highlighted
            );
        }

        // Highlight numbers
        $highlighted = preg_replace(
            '/\b(\d+)\b/',
            '<span class="text-orange-600 dark:text-orange-400">$1</span>',
            $highlighted
        );

        // Highlight functions
        $highlighted = preg_replace(
            '/\b(len|sum|avg|min|max|count)\s*\(/',
            '<span class="text-purple-600 dark:text-purple-400">$1</span>(',
            $highlighted
        );

        return $highlighted;
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.expression-config');
    }
}
