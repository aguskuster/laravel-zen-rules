# Graph Editor Architecture Documentation

## 📋 Table of Contents
1. [Overview](#overview)
2. [Architecture Principles](#architecture-principles)
3. [Directory Structure](#directory-structure)
4. [Core Concepts](#core-concepts)
5. [Data Flow](#data-flow)
6. [Adding New Features](#adding-new-features)

## 🎯 Overview

This is a **Livewire-first** graph editor with minimal JavaScript. The architecture follows **Clean Code** principles and uses **Data Transfer Objects (DTOs)** for type safety.

### Key Technologies
- **Laravel 12** - Backend framework
- **Livewire 3** - Server-side component framework
- **Alpine.js** - Minimal client-side interactivity (comes with Livewire)
- **Tailwind CSS** - Styling
- **@shopify/draggable** - Drag-and-drop library

## 🏗️ Architecture Principles

### 1. **Separation of Concerns**
- **DTOs** (`app/DataTransferObjects/`) - Immutable data containers
- **Services** (`app/Services/`) - Business logic
- **Livewire Components** (`app/Livewire/`) - UI state management
- **Views** (`resources/views/livewire/`) - Presentation

### 2. **Type Safety**
All DTOs use PHP 8.2+ features:
- `readonly` classes for immutability
- Typed properties
- Named arguments
- Return type declarations

### 3. **Immutability**
DTOs are immutable. To update, create a new instance:
```php
$updatedComponent = $component->withPosition(100, 200);
```

### 4. **Single Responsibility**
Each class has ONE job:
- `GraphEditorService` - Manages components and connections
- `SwitchService` - Manages switch-specific logic
- `GraphEditor` (Livewire) - Manages UI state

## 📁 Directory Structure

```
app/
├── DataTransferObjects/          # Immutable data containers
│   ├── GraphComponentData.php    # Represents a graph component
│   ├── SwitchConditionData.php   # Represents a switch condition
│   └── ConnectionData.php        # Represents a connection
│
├── Services/                     # Business logic
│   ├── GraphEditorService.php    # Component/connection management
│   └── SwitchService.php         # Switch-specific logic
│
└── Livewire/                     # UI components
    └── GraphEditor.php           # Main graph editor component

resources/
└── views/
    └── livewire/
        └── graph-editor.blade.php  # Main view
```

## 🔑 Core Concepts

### Data Transfer Objects (DTOs)

DTOs are **immutable value objects** that carry data between layers.

#### GraphComponentData
Represents a component on the canvas.

```php
$component = new GraphComponentData(
    id: 'switch-abc123',
    type: 'switch',
    x: 100,
    y: 200,
    config: ['mode' => 'first-match']
);

// Update position (creates new instance)
$moved = $component->withPosition(150, 250);
```

#### SwitchConditionData
Represents a condition in a switch component.

```php
$condition = new SwitchConditionData(
    id: 'cond-xyz789',
    type: 'if',
    expression: 'fee == 10',
    targetComponentId: 'response-1'
);

// Check if valid
if ($condition->hasValidExpression()) {
    // Process...
}
```

#### ConnectionData
Represents a connection between components.

```php
$connection = new ConnectionData(
    id: 'conn-123',
    fromComponentId: 'switch-1',
    toComponentId: 'response-1',
    fromPosition: 'right',
    toPosition: 'left',
    fromConditionId: 'cond-1'  // Optional: for switch conditions
);
```

### Services

Services contain **business logic** and are stateless.

#### GraphEditorService
```php
$service = app(GraphEditorService::class);

// Create a component
$component = $service->createComponent('switch', 100, 200);

// Create a connection
$connection = $service->createConnection(
    fromComponentId: 'switch-1',
    toComponentId: 'response-1'
);

// Validate connection
if ($service->isValidConnection($from, $to)) {
    // Create connection
}
```

#### SwitchService
```php
$service = app(SwitchService::class);

// Create a condition
$condition = $service->createCondition('if', 'fee == 10');

// Validate expression
$result = $service->validateExpression('fee == 10');
if ($result['valid']) {
    // Expression is valid
}

// Add condition to collection
$conditions = $service->addCondition($existingConditions, 'elseif');
```

## 🔄 Data Flow

### Adding a Component

```
User Action (Drag from sidebar)
    ↓
Alpine.js captures drop event
    ↓
Dispatches Livewire event: 'add-component'
    ↓
GraphEditor::addComponent() method
    ↓
GraphEditorService::createComponent()
    ↓
Returns GraphComponentData DTO
    ↓
Added to $components array
    ↓
Livewire re-renders view
    ↓
Component appears on canvas
```

### Updating a Switch Condition

```
User types in expression input
    ↓
wire:model.live="expression"
    ↓
GraphEditor::updateConditionExpression()
    ↓
SwitchService::updateConditionExpression()
    ↓
Returns updated Collection of conditions
    ↓
Updates $components array
    ↓
Livewire re-renders
    ↓
UI updates with new expression
```

## 🚀 Adding New Features

### Example: Add a new component type

1. **No DTO changes needed** (GraphComponentData is generic)

2. **Add to component types** in view:
```blade
<div wire:click="addComponent('my-new-type', 100, 100)">
    My New Component
</div>
```

3. **Add rendering logic** in view:
```blade
@if($component['type'] === 'my-new-type')
    <!-- Render your component -->
@endif
```

4. **Add specific logic** (if needed):
```php
// In GraphEditorService or create MyNewTypeService
public function createMyNewType(): GraphComponentData
{
    return $this->createComponent('my-new-type', 0, 0, [
        'customConfig' => 'value'
    ]);
}
```

### Example: Add validation to switch expressions

1. **Update SwitchService**:
```php
public function validateExpression(string $expression): array
{
    $errors = [];
    
    // Add your validation
    if (str_contains($expression, 'forbidden')) {
        $errors[] = 'Forbidden keyword';
    }
    
    return ['valid' => empty($errors), 'errors' => $errors];
}
```

2. **Use in Livewire component**:
```php
public function updateConditionExpression(string $id, string $expr): void
{
    $service = app(SwitchService::class);
    $result = $service->validateExpression($expr);
    
    if (!$result['valid']) {
        $this->addError('expression', implode(', ', $result['errors']));
        return;
    }
    
    // Continue with update...
}
```

## 📝 Code Style Guidelines

### 1. Always use type declarations
```php
public function myMethod(string $param): array
{
    // ...
}
```

### 2. Use named arguments for clarity
```php
$component = new GraphComponentData(
    id: 'switch-1',
    type: 'switch',
    x: 100,
    y: 200
);
```

### 3. Document everything
```php
/**
 * Create a new component
 * 
 * @param string $type Component type
 * @param int $x X coordinate
 * @param int $y Y coordinate
 * @return GraphComponentData
 */
public function createComponent(string $type, int $x, int $y): GraphComponentData
```

### 4. Use collections for data manipulation
```php
$filtered = collect($this->components)
    ->filter(fn($c) => $c['type'] === 'switch')
    ->map(fn($c) => GraphComponentData::fromArray($c))
    ->toArray();
```

## 🧪 Testing

### Unit Testing Services
```php
public function test_creates_component_with_correct_type(): void
{
    $service = new GraphEditorService();
    $component = $service->createComponent('switch', 100, 200);
    
    $this->assertEquals('switch', $component->type);
    $this->assertEquals(100, $component->x);
}
```

### Testing Livewire Components
```php
public function test_adds_component_to_canvas(): void
{
    Livewire::test(GraphEditor::class)
        ->call('addComponent', 'switch', 100, 200)
        ->assertCount('components', 1);
}
```

## 🤝 Contributing

When adding features:
1. **Create DTOs** for new data structures
2. **Add business logic** to services
3. **Keep Livewire components thin** - just UI state
4. **Document everything** with PHPDoc
5. **Write tests** for services
6. **Use type hints** everywhere

## 📚 Further Reading

- [Livewire Documentation](https://livewire.laravel.com)
- [Laravel Collections](https://laravel.com/docs/collections)
- [PHP 8.2 Features](https://www.php.net/releases/8.2/en.php)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)

