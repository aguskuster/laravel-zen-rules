<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expression Configuration Test - GoLevel Zen Laravel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-900 text-slate-100">
    <div class="min-h-screen p-8">
        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Expression Configuration</h1>
                    <p class="text-slate-400">Test the expression configuration component</p>
                </div>
                <a href="/graph" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Back to Graph
                </a>
            </div>
        </div>

        <!-- Test Component -->
        <div class="max-w-7xl mx-auto">
            <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
                <h2 class="text-xl font-semibold mb-4">Expression Component Test</h2>
                <p class="text-slate-400 mb-6">Click the button below to open the expression configuration modal.</p>

                <button
                    onclick="openExpressionModal()"
                    class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors font-medium"
                >
                    Open Expression Configuration
                </button>

                <!-- Livewire Component -->
                <div class="mt-8" id="expression-component">
                    @livewire('expression-config', [
                        'componentId' => 'expression-1',
                        'rows' => [
                            ['key' => 'status', 'expression' => 'len(user.servers) > 2 ? "very-active" : len(user.servers) > 0 ? "active" : "inactive"'],
                            ['key' => 'admin', 'expression' => 'user.role == "super" ? "admin" : "not admin"'],
                        ]
                    ])
                </div>
            </div>

            <!-- Documentation -->
            <div class="mt-8 bg-slate-800 rounded-lg p-6 border border-slate-700">
                <h3 class="text-lg font-semibold mb-4">How to Use</h3>
                <div class="space-y-4 text-slate-300">
                    <div>
                        <h4 class="font-medium text-white mb-2">1. Key-Value Expressions</h4>
                        <p class="text-sm">Each row defines a key and its corresponding expression. The expression is evaluated and the result is assigned to the key.</p>
                    </div>

                    <div>
                        <h4 class="font-medium text-white mb-2">2. Syntax Highlighting</h4>
                        <p class="text-sm">The expression preview shows syntax highlighting for:</p>
                        <ul class="list-disc list-inside ml-4 text-sm mt-2 space-y-1">
                            <li><span class="text-green-400">Strings</span> - in green</li>
                            <li><span class="text-blue-400">Operators</span> - in blue (==, !=, >, <, etc.)</li>
                            <li><span class="text-orange-400">Numbers</span> - in orange</li>
                            <li><span class="text-purple-400">Functions</span> - in purple (len, sum, avg, etc.)</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-medium text-white mb-2">3. Quick Operators</h4>
                        <p class="text-sm">Click the operator buttons below each expression to quickly insert common operators.</p>
                    </div>

                    <div>
                        <h4 class="font-medium text-white mb-2">4. Add/Remove Rows</h4>
                        <p class="text-sm">Use the "Add row" button to add new expressions, or hover over a row and click the trash icon to remove it.</p>
                    </div>
                </div>
            </div>

            <!-- Example Expressions -->
            <div class="mt-8 bg-slate-800 rounded-lg p-6 border border-slate-700">
                <h3 class="text-lg font-semibold mb-4">Example Expressions</h3>
                <div class="space-y-3">
                    <div class="bg-slate-900 rounded p-3">
                        <div class="text-xs text-slate-400 mb-1">Simple comparison:</div>
                        <code class="text-sm font-mono text-slate-200">user.role == "admin"</code>
                    </div>

                    <div class="bg-slate-900 rounded p-3">
                        <div class="text-xs text-slate-400 mb-1">Ternary operator:</div>
                        <code class="text-sm font-mono text-slate-200">len(user.servers) > 2 ? "active" : "inactive"</code>
                    </div>

                    <div class="bg-slate-900 rounded p-3">
                        <div class="text-xs text-slate-400 mb-1">Nested ternary:</div>
                        <code class="text-sm font-mono text-slate-200">len(user.servers) > 2 ? "very-active" : len(user.servers) > 0 ? "active" : "inactive"</code>
                    </div>

                    <div class="bg-slate-900 rounded p-3">
                        <div class="text-xs text-slate-400 mb-1">Multiple conditions:</div>
                        <code class="text-sm font-mono text-slate-200">fee >= 100 && country IN ["US", "CA"]</code>
                    </div>

                    <div class="bg-slate-900 rounded p-3">
                        <div class="text-xs text-slate-400 mb-1">Using functions:</div>
                        <code class="text-sm font-mono text-slate-200">sum(order.items) > 1000 ? "premium" : "standard"</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts

    <script>
        function openExpressionModal() {
            // Find the Livewire component
            const componentEl = document.querySelector('#expression-component [wire\\:id]');
            if (componentEl) {
                const wireId = componentEl.getAttribute('wire:id');
                const component = Livewire.find(wireId);
                if (component) {
                    component.set('show', true);
                }
            }
        }
    </script>
</body>
</html>

