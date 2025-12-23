# 🚀 Quick Start Guide - Graph Editor

## For Your Team Members

This guide will help you understand and work with the graph editor codebase in **5 minutes**.

## 📚 What You Need to Know

### 1. **We Use Livewire, Not JavaScript**

**Old Way (JavaScript-heavy):**
```javascript
// ❌ Don't do this anymore
function addComponent(type, x, y) {
    const component = { id: generateId(), type, x, y };
    components.push(component);
    renderComponent(component);
}
```

**New Way (Livewire):**
```php
// ✅ Do this instead
public function addComponent(string $type, int $x, int $y): void
{
    $service = app(GraphEditorService::class);
    $component = $service->createComponent($type, $x, $y);
    $this->components[] = $component->toArray();
    // Livewire automatically re-renders!
}
```

### 2. **Everything is Typed and Documented**

Every method has:
- **Type hints** for parameters
- **Return types**
- **PHPDoc comments** explaining what it does

```php
/**
 * Create a new component
 * 
 * @param string $type Component type (request, response, switch, etc.)
 * @param int $x X coordinate on canvas
 * @param int $y Y coordinate on canvas
 * @return GraphComponentData The created component
 */
public function createComponent(string $type, int $x, int $y): GraphComponentData
{
    // Implementation
}
```

### 3. **Data is Immutable**

We use **DTOs (Data Transfer Objects)** that can't be changed after creation.

```php
// Create a component
$component = new GraphComponentData(
    id: 'switch-1',
    type: 'switch',
    x: 100,
    y: 200
);

// ❌ Can't do this (readonly properties)
$component->x = 150;

// ✅ Do this instead (creates new instance)
$updated = $component->withPosition(150, 250);
```

## 🗂️ File Organization

```
app/
├── DataTransferObjects/     ← Data containers (like models, but simpler)
│   ├── GraphComponentData.php
│   ├── SwitchConditionData.php
│   └── ConnectionData.php
│
├── Services/                ← Business logic (the "brains")
│   ├── GraphEditorService.php
│   └── SwitchService.php
│
└── Livewire/                ← UI components (what users see)
    ├── GraphEditor.php
    └── SwitchConfigModal.php
```

## 🎯 Common Tasks

### Task 1: Add a New Component Type

**Example: Add a "Database" component**

1. **No code changes needed!** Just add to the view:

```blade
<!-- In resources/views/livewire/graph-editor.blade.php -->
<div wire:click="addComponent('database', 100, 100)">
    <svg><!-- Database icon --></svg>
    Database
</div>
```

2. **Add rendering** (in the same file):

```blade
@if($component['type'] === 'database')
    <div class="bg-blue-500 text-white p-4 rounded">
        <svg><!-- Database icon --></svg>
        Database
    </div>
@endif
```

That's it! The system handles everything else.

### Task 2: Add Validation to Switch Expressions

1. **Open** `app/Services/SwitchService.php`

2. **Find** the `validateExpression` method

3. **Add your rule**:

```php
public function validateExpression(string $expression): array
{
    $errors = [];

    // Existing validations...

    // Add your new validation
    if (strlen($expression) > 100) {
        $errors[] = 'Expression too long (max 100 characters)';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
    ];
}
```

### Task 3: Add a New Switch Condition Type

**Example: Add a "switch-case" type**

1. **Update** `SwitchService::createCondition()`:

```php
public function createCondition(string $type, ?string $expression = null): SwitchConditionData
{
    // Validate type
    $validTypes = ['if', 'elseif', 'else', 'case']; // ← Add 'case'
    if (!in_array($type, $validTypes)) {
        throw new \InvalidArgumentException("Invalid type: $type");
    }

    $id = $this->generateConditionId();
    return new SwitchConditionData($id, $type, $expression);
}
```

2. **Update the view** to show the new type:

```blade
@if($condition['type'] === 'case')
    <div class="bg-purple-500 text-white p-2 rounded">
        Case: {{ $condition['expression'] }}
    </div>
@endif
```

## 🔍 Understanding the Flow

### Example: User Adds a Switch Component

```
1. User drags "Switch" from sidebar
   ↓
2. Alpine.js (minimal JS) detects drop
   ↓
3. Calls: wire:click="addComponent('switch', 100, 200)"
   ↓
4. GraphEditor::addComponent() runs on server
   ↓
5. GraphEditorService::createComponent() creates DTO
   ↓
6. Component added to $this->components array
   ↓
7. Livewire automatically re-renders the view
   ↓
8. User sees new component on canvas
```

**No manual DOM manipulation! No state management! Livewire handles it all.**

## 🛠️ Debugging Tips

### 1. Use `dd()` to inspect data

```php
public function addComponent(string $type, int $x, int $y): void
{
    $component = $service->createComponent($type, $x, $y);
    
    dd($component); // ← Dumps and dies, shows you the data
    
    $this->components[] = $component->toArray();
}
```

### 2. Use Livewire DevTools

Install the browser extension:
- [Chrome](https://chrome.google.com/webstore/detail/livewire-devtools)
- [Firefox](https://addons.mozilla.org/en-US/firefox/addon/livewire-devtools/)

### 3. Check Livewire Logs

```bash
php artisan pail
```

Shows all Livewire requests in real-time.

## 📖 Key Concepts

### DTOs (Data Transfer Objects)

Think of them as **typed arrays** that can't be changed.

```php
// Instead of this:
$component = [
    'id' => 'switch-1',
    'type' => 'switch',
    'x' => 100,
    'y' => 200,
];

// We use this:
$component = new GraphComponentData(
    id: 'switch-1',
    type: 'switch',
    x: 100,
    y: 200,
);

// Benefits:
// ✅ Type safety (can't pass wrong types)
// ✅ IDE autocomplete
// ✅ Can't be accidentally modified
// ✅ Self-documenting
```

### Services

Services contain **business logic**. They're like helpers, but organized.

```php
// ❌ Don't put logic in Livewire components
public function addComponent(string $type, int $x, int $y): void
{
    $id = $type . '-' . Str::random(8);
    $component = ['id' => $id, 'type' => $type, 'x' => $x, 'y' => $y];
    $this->components[] = $component;
}

// ✅ Do put logic in services
public function addComponent(string $type, int $x, int $y): void
{
    $service = app(GraphEditorService::class);
    $component = $service->createComponent($type, $x, $y);
    $this->components[] = $component->toArray();
}
```

**Why?**
- Services can be **tested** independently
- Services can be **reused** in other components
- Livewire components stay **thin and focused** on UI

## 🎓 Learning Resources

1. **Read ARCHITECTURE.md** - Detailed architecture documentation
2. **Livewire Docs** - https://livewire.laravel.com
3. **PHP 8.2 Features** - https://www.php.net/releases/8.2/en.php

## 💡 Pro Tips

1. **Always use type hints** - Your IDE will thank you
2. **Read the PHPDoc** - Every method is documented
3. **Use named arguments** - Makes code self-documenting
4. **Keep Livewire components thin** - Move logic to services
5. **Test services, not Livewire** - Services are easier to test

## ❓ Common Questions

**Q: Where do I add new features?**
A: Start in the service class, then expose it in the Livewire component.

**Q: Can I still use JavaScript?**
A: Yes, but only for things Livewire can't do (drag-and-drop, canvas drawing).

**Q: How do I pass data to the frontend?**
A: Just add it to a public property in the Livewire component. It's automatically available in the view.

**Q: How do I update the UI?**
A: Just update the public properties. Livewire handles the rest.

## 🚨 Common Mistakes

### ❌ Modifying DTOs directly
```php
$component->x = 150; // Won't work! DTOs are readonly
```

### ✅ Create new instances
```php
$updated = $component->withPosition(150, 200);
```

---

### ❌ Putting logic in Livewire components
```php
public function addComponent(string $type, int $x, int $y): void
{
    // Lots of business logic here...
}
```

### ✅ Use services
```php
public function addComponent(string $type, int $x, int $y): void
{
    $service = app(GraphEditorService::class);
    $component = $service->createComponent($type, $x, $y);
    $this->components[] = $component->toArray();
}
```

---

**Need help? Check ARCHITECTURE.md for detailed explanations!**

