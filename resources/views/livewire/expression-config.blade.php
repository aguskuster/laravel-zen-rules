<div>
    @if($show)
    <!-- Expression Configuration Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" wire:click.self="close">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                        {{ $componentId }} - Expression Configuration
                    </h2>
                </div>
                <button wire:click="close" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Tabs -->
                <div class="flex gap-2 mb-6 border-b border-slate-200 dark:border-slate-700">
                    <button class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Expression
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Graph
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        View
                    </button>
                </div>

                <!-- Expression Editor -->
                <div class="space-y-4">
                    <!-- Header Row -->
                    <div class="grid grid-cols-12 gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400 px-2">
                        <div class="col-span-2">Key</div>
                        <div class="col-span-10">Expression</div>
                    </div>

                    <!-- Expression Rows -->
                    @foreach($rows as $index => $row)
                    <div class="grid grid-cols-12 gap-4 items-start group" wire:key="row-{{ $index }}">
                        <!-- Row Number -->
                        <div class="col-span-12 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <span class="font-mono">#{{ $index + 1 }}</span>
                            @if(count($rows) > 1)
                            <button
                                wire:click="removeRow({{ $index }})"
                                class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-opacity"
                                title="Remove row"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            @endif
                        </div>

                        <!-- Key Input -->
                        <div class="col-span-2">
                            <input
                                type="text"
                                wire:model.live="rows.{{ $index }}.key"
                                placeholder="key"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                            />
                        </div>

                        <!-- Expression Input -->
                        <div class="col-span-10 space-y-2">
                            <div class="relative">
                                <textarea
                                    wire:model.live="rows.{{ $index }}.expression"
                                    placeholder="Enter expression..."
                                    rows="2"
                                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 resize-none"
                                ></textarea>
                            </div>

                            <!-- Expression Preview with Syntax Highlighting -->
                            <div class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
                                <div class="text-xs font-mono leading-relaxed">
                                    {!! $this->highlightExpression($row['expression']) !!}
                                </div>
                            </div>

                            <!-- Quick Operators -->
                            <div class="flex flex-wrap gap-1">
                                @foreach($operators as $operator)
                                <button
                                    type="button"
                                    wire:click="updateExpression({{ $index }}, '{{ $row['expression'] }}{{ $operator['value'] }}')"
                                    class="px-2 py-1 text-xs font-mono bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded transition-colors"
                                    title="{{ $operator['label'] }}"
                                >
                                    {{ trim($operator['value']) }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    @if(!$loop->last)
                    <div class="border-t border-slate-100 dark:border-slate-700"></div>
                    @endif
                    @endforeach

                    <!-- Add Row Button -->
                    <div class="pt-4">
                        <button
                            wire:click="addRow"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add row
                        </button>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Expression Syntax</h3>
                    <div class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                        <p><strong>Operators:</strong> ==, !=, >, <, >=, <=, IN, NOT IN, &&, ||</p>
                        <p><strong>Ternary:</strong> condition ? value_if_true : value_if_false</p>
                        <p><strong>Functions:</strong> len(array), sum(array), avg(array), min(array), max(array)</p>
                        <p><strong>Examples:</strong></p>
                        <ul class="list-disc list-inside ml-2 space-y-1">
                            <li><code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">user.role == "admin"</code></li>
                            <li><code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">len(user.servers) > 2 ? "active" : "inactive"</code></li>
                            <li><code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">fee >= 100 && country IN ["US", "CA"]</code></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    {{ count($rows) }} {{ Str::plural('row', count($rows)) }}
                </div>
                <div class="flex gap-3">
                    <button
                        wire:click="close"
                        class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="save"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
