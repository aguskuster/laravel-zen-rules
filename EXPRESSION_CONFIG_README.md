# 🎨 Expression Configuration Component

## Overview

The Expression Configuration component is a **Livewire-based** key-value expression editor with **real-time syntax highlighting**. It allows users to define expressions using a clean, intuitive interface.

## 🎯 Features

### ✅ Key Features
- **Key-Value Pairs** - Define multiple expressions with custom keys
- **Real-time Syntax Highlighting** - Instant visual feedback for:
  - 🟢 Strings (green)
  - 🔵 Operators (blue)
  - 🟠 Numbers (orange)
  - 🟣 Functions (purple)
- **Quick Operators** - One-click insertion of common operators
- **Add/Remove Rows** - Dynamic row management
- **Validation** - Expression syntax validation
- **Dark Mode** - Full dark mode support
- **Responsive** - Works on all screen sizes

## 📁 Files

```
app/
├── Livewire/
│   └── ExpressionConfig.php          # Livewire component
│
resources/
└── views/
    └── livewire/
        └── expression-config.blade.php  # Component view
```

## 🚀 Usage

### Basic Usage

```blade
@livewire('expression-config', [
    'componentId' => 'expression-1',
    'rows' => [
        ['key' => 'status', 'expression' => 'user.active ? "active" : "inactive"'],
        ['key' => 'role', 'expression' => 'user.role == "admin" ? "admin" : "user"'],
    ]
])
```

### Opening the Modal

```javascript
// Find the Livewire component and open it
const component = Livewire.find(wireId);
component.set('show', true);
```

### Listening for Save Events

```php
#[On('expression-config-saved')]
public function handleExpressionSaved(array $data): void
{
    $componentId = $data['componentId'];
    $rows = $data['rows'];
    
    // Save to database or update state
}
```

## 📝 Expression Syntax

### Supported Operators

| Operator | Description | Example |
|----------|-------------|---------|
| `==` | Equals | `user.role == "admin"` |
| `!=` | Not equals | `status != "inactive"` |
| `>` | Greater than | `age > 18` |
| `<` | Less than | `price < 100` |
| `>=` | Greater or equal | `score >= 80` |
| `<=` | Less or equal | `count <= 10` |
| `IN` | Contains | `country IN ["US", "CA"]` |
| `NOT IN` | Not contains | `status NOT IN ["banned"]` |
| `&&` | Logical AND | `age > 18 && verified` |
| `||` | Logical OR | `admin || moderator` |

### Ternary Operator

```javascript
condition ? value_if_true : value_if_false
```

**Examples:**
```javascript
// Simple ternary
user.active ? "active" : "inactive"

// Nested ternary
len(servers) > 2 ? "very-active" : len(servers) > 0 ? "active" : "inactive"
```

### Functions

| Function | Description | Example |
|----------|-------------|---------|
| `len(array)` | Get array length | `len(user.servers)` |
| `sum(array)` | Sum array values | `sum(order.items)` |
| `avg(array)` | Average of values | `avg(scores)` |
| `min(array)` | Minimum value | `min(prices)` |
| `max(array)` | Maximum value | `max(ratings)` |

## 🎨 Component API

### Properties

```php
public string $componentId;           // Component ID being edited
public array $rows = [];              // Expression rows
public bool $show = false;            // Modal visibility
public array $operators = [];         // Available operators
```

### Methods

```php
// Add a new row
public function addRow(): void

// Remove a row by index
public function removeRow(int $index): void

// Update a row's key
public function updateKey(int $index, string $key): void

// Update a row's expression
public function updateExpression(int $index, string $expression): void

// Save configuration
public function save(): void

// Close modal
public function close(): void

// Highlight expression syntax
public function highlightExpression(string $expression): string
```

### Events

**Dispatched Events:**
- `expression-config-saved` - When user saves changes
  ```php
  [
      'componentId' => 'expression-1',
      'rows' => [
          ['key' => 'status', 'expression' => '...'],
      ]
  ]
  ```
- `expression-config-closed` - When modal is closed

## 💡 Examples

### Example 1: User Status

```javascript
// Key: status
// Expression:
len(user.servers) > 2 ? "very-active" : len(user.servers) > 0 ? "active" : "inactive"
```

**Result:**
- If user has more than 2 servers: `"very-active"`
- If user has 1-2 servers: `"active"`
- If user has 0 servers: `"inactive"`

### Example 2: User Role

```javascript
// Key: admin
// Expression:
user.role == "super" ? "admin" : "not admin"
```

**Result:**
- If user role is "super": `"admin"`
- Otherwise: `"not admin"`

### Example 3: Fee Calculation

```javascript
// Key: fee
// Expression:
country == "US" && amount > 100 ? amount * 0.1 : amount * 0.05
```

**Result:**
- If country is US and amount > 100: 10% fee
- Otherwise: 5% fee

### Example 4: Premium Status

```javascript
// Key: premium
// Expression:
sum(order.items) > 1000 && user.verified ? "premium" : "standard"
```

**Result:**
- If order total > 1000 and user is verified: `"premium"`
- Otherwise: `"standard"`

## 🎯 Integration with GraphEditor

### In GraphEditor Component

```php
// Add properties
public bool $showExpressionModal = false;
public ?string $editingExpressionId = null;
public array $expressionRows = [];

// Open modal
public function openExpressionModal(string $componentId): void
{
    $this->editingExpressionId = $componentId;
    
    $component = collect($this->components)->firstWhere('id', $componentId);
    $this->expressionRows = $component['config']['rows'] ?? [];
    
    $this->showExpressionModal = true;
}

// Save configuration
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
```

### In Blade View

```blade
@if($showExpressionModal && $editingExpressionId)
    @livewire('expression-config', [
        'componentId' => $editingExpressionId,
        'rows' => $expressionRows
    ])
@endif
```

## 🧪 Testing

Visit `/expression-test` to see a live demo of the component.

```bash
php artisan serve
```

Then open: http://localhost:8000/expression-test

## 🎨 Customization

### Change Syntax Highlighting Colors

Edit `highlightExpression()` method in `ExpressionConfig.php`:

```php
// Strings - change from green to your color
'<span class="text-green-600 dark:text-green-400">"$1"</span>'

// Operators - change from blue to your color
'<span class="text-blue-600 dark:text-blue-400">' . $op . '</span>'

// Numbers - change from orange to your color
'<span class="text-orange-600 dark:text-orange-400">$1</span>'

// Functions - change from purple to your color
'<span class="text-purple-600 dark:text-purple-400">$1</span>'
```

### Add More Operators

Edit the `$operators` property:

```php
public array $operators = [
    // ... existing operators
    ['value' => ' % ', 'label' => '% (modulo)'],
    ['value' => ' ** ', 'label' => '** (power)'],
];
```

### Add More Functions

Update the `highlightExpression()` method:

```php
$highlighted = preg_replace(
    '/\b(len|sum|avg|min|max|count|round|floor|ceil)\s*\(/',
    '<span class="text-purple-600 dark:text-purple-400">$1</span>(',
    $highlighted
);
```

## 📚 Best Practices

1. **Keep expressions simple** - Complex logic should be in backend
2. **Use meaningful keys** - Keys should describe what the expression calculates
3. **Add comments** - Use the help section to document complex expressions
4. **Validate expressions** - Always validate before saving
5. **Test thoroughly** - Test with different input values

## 🐛 Troubleshooting

### Modal doesn't open
- Check that `show` property is set to `true`
- Verify Livewire is properly initialized
- Check browser console for errors

### Syntax highlighting not working
- Ensure expressions are properly formatted
- Check that `highlightExpression()` method is being called
- Verify Tailwind CSS classes are loaded

### Save event not firing
- Check that event listener is registered
- Verify event name matches: `expression-config-saved`
- Check Livewire console for errors

## 🎉 Conclusion

The Expression Configuration component provides a powerful, user-friendly way to define and manage expressions in your application. With real-time syntax highlighting and a clean interface, it makes complex expression editing simple and intuitive.

**Happy coding!** 🚀

