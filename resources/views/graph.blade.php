<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Graph - GoLevel Zen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/graph-editor.js'])
    @livewireStyles
</head>
<body class="antialiased bg-slate-50 dark:bg-slate-900">
    <div class="flex flex-col h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Graph</h1>
                <span class="px-2 py-1 text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded">Draft</span>
                <span class="px-2 py-1 text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded">Unsaved changes</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="clear-all" class="px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300" title="Clear All">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Clear All
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Canvas Area -->
            <div class="flex-1 relative overflow-hidden">
                <!-- Dotted Grid Canvas -->
                <div id="canvas" class="absolute inset-0 bg-dotted overflow-auto">
                    <div class="relative min-w-full min-h-full p-8">
                        <!-- Request Component (draggable) -->
                        <div class="absolute top-20 left-20 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 w-48 cursor-move hover:shadow-xl transition-shadow" data-component-id="request-0">
                            <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                <div class="w-6 h-6 bg-slate-500 rounded flex items-center justify-center text-white text-xs font-bold">
                                    R
                                </div>
                                <span class="text-sm font-medium text-slate-800 dark:text-slate-100">request</span>
                                <button class="ml-auto text-slate-400 hover:text-red-600 dark:hover:text-red-400 delete-btn" data-component-id="request-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-4 py-3">
                                <button class="configure-btn text-sm text-blue-600 dark:text-blue-400 hover:underline" data-component-id="request-0">Configure</button>
                            </div>
                        </div>

                        <!-- Decision Table Component (draggable) -->
                        <div class="absolute top-20 left-96 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 w-56 cursor-move hover:shadow-xl transition-shadow" data-component-id="decision-table-0">
                            <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                <div class="w-6 h-6 bg-blue-500 rounded flex items-center justify-center text-white text-xs font-bold">
                                    DT
                                </div>
                                <span class="text-sm font-medium text-slate-800 dark:text-slate-100">decisionTable1</span>
                                <button class="ml-auto text-slate-400 hover:text-red-600 dark:hover:text-red-400 delete-btn" data-component-id="decision-table-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-4 py-3 flex gap-2">
                                <button class="configure-btn text-sm text-blue-600 dark:text-blue-400 hover:underline" data-component-id="decision-table-0">Edit Table</button>
                                <button class="configure-btn text-sm text-blue-600 dark:text-blue-400 hover:underline" data-component-id="decision-table-0">Settings</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zoom Controls -->
                <div class="absolute bottom-4 left-4 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 flex flex-col">
                    <button id="zoom-in" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 border-b border-slate-200 dark:border-slate-700" title="Zoom In">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                    <button id="zoom-out" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 border-b border-slate-200 dark:border-slate-700" title="Zoom Out">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <button id="fit-screen" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 border-b border-slate-200 dark:border-slate-700" title="Fit to Screen">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                    </button>
                    <button id="reset-view" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700" title="Reset View">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Right Sidebar - Components Panel -->
            <div class="w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 overflow-y-auto">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Components</h2>
                        <span class="text-xs text-slate-500 dark:text-slate-400">(Drag and drop)</span>
                    </div>

                    <!-- Component List -->
                    <div class="space-y-2">
                        <!-- Request Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="request">
                            <div class="w-8 h-8 bg-slate-200 dark:bg-slate-600 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Request</span>
                        </div>

                        <!-- Response Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="response">
                            <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Response</span>
                        </div>

                        <!-- Decision Table Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="decision-table">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Decision Table</span>
                        </div>

                        <!-- Expression Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="expression">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Expression</span>
                        </div>

                        <!-- Function Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="function">
                            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Function</span>
                        </div>

                        <!-- Switch Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="switch">
                            <div class="w-8 h-8 bg-violet-100 dark:bg-violet-900/30 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Switch</span>
                        </div>

                        <!-- Decision Component -->
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg cursor-move hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" draggable="true" data-component-type="decision">
                            <div class="w-8 h-8 bg-slate-200 dark:bg-slate-600 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Decision</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JSON Editor Modal -->
    <div id="json-editor-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-2xl w-full max-w-4xl h-5/6 flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100" id="modal-component-name">request</h2>
                    <div class="flex gap-2">
                        <button class="tab-btn active px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400" data-tab="schema">
                            Schema
                        </button>
                        <button class="tab-btn px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100" data-tab="graph">
                            Graph
                        </button>
                    </div>
                </div>
                <button id="close-modal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-hidden">
                <!-- Schema Tab -->
                <div id="schema-tab" class="h-full flex">
                    <!-- Line Numbers -->
                    <div class="bg-slate-50 dark:bg-slate-900 px-4 py-4 text-right text-sm text-slate-400 dark:text-slate-500 font-mono select-none border-r border-slate-200 dark:border-slate-700">
                        <div id="line-numbers">1<br>2<br>3<br>4<br>5</div>
                    </div>

                    <!-- JSON Editor -->
                    <div class="flex-1 relative">
                        <textarea
                            id="json-editor"
                            class="w-full h-full p-4 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-mono text-sm resize-none focus:outline-none"
                            spellcheck="false"
                            placeholder='{\n  "key": "value"\n}'
                        >{
  "country": "US",
  "fee": 0
}</textarea>
                        <div id="json-error" class="hidden absolute bottom-4 left-4 right-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-400 px-4 py-2 rounded text-sm"></div>
                    </div>
                </div>

                <!-- Graph Tab (hidden by default) -->
                <div id="graph-tab" class="hidden h-full p-6">
                    <p class="text-slate-600 dark:text-slate-400">Graph view coming soon...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" id="preview-changes" class="rounded">
                        <span>Preview changes</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <button id="cancel-modal" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                        Cancel
                    </button>
                    <button id="save-json" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Switch Editor Modal -->
    <div id="switch-editor-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-2xl w-full max-w-4xl max-h-5/6 flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100" id="switch-modal-title">Switch Configuration</h2>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 rounded-lg">
                        <label class="text-xs font-medium text-slate-600 dark:text-slate-300">Mode:</label>
                        <select id="switch-mode" class="text-xs bg-transparent border-none focus:outline-none text-slate-700 dark:text-slate-200 font-medium cursor-pointer">
                            <option value="first">First Match</option>
                            <option value="all">All Matches</option>
                        </select>
                    </div>
                </div>
                <button id="close-switch-modal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <div id="switch-conditions-editor" class="space-y-3">
                    <!-- Conditions will be rendered here -->
                </div>

                <div class="mt-4 flex gap-2">
                    <button id="add-switch-condition" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        + Add Condition
                    </button>
                    <button id="add-switch-else" class="px-4 py-2 text-sm bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-medium">
                        + Add Else
                    </button>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-medium">Tip:</span> Use operators like ==, !=, >, <, >=, <=, IN, NOT IN
                </div>
                <div class="flex gap-2">
                    <button id="cancel-switch-modal" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                        Cancel
                    </button>
                    <button id="save-switch" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Expression Editor Modal (GoRules.io style - Clean White Design) -->
    <div id="expression-editor-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900" id="expression-modal-title">expression1</h2>
                <button id="close-expression-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Header Row -->
                <div class="grid grid-cols-12 gap-4 mb-4 px-2">
                    <div class="col-span-3 text-sm font-medium text-gray-600">Key</div>
                    <div class="col-span-9 text-sm font-medium text-gray-600">Expression</div>
                </div>

                <!-- Expression Rows Container -->
                <div id="expression-rows-container" class="space-y-3">
                    <!-- Rows will be dynamically added here -->
                </div>

                <!-- Add Row Button -->
                <div class="mt-4">
                    <button id="add-expression-row" class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-gray-400 hover:text-gray-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add row
                    </button>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-white">
                <button id="cancel-expression-modal" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors">
                    Cancel
                </button>
                <button id="save-expression" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    Save
                </button>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>

