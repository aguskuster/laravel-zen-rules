<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoLevel Zen Laravel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 min-h-screen">
    <div class="relative min-h-screen flex flex-col items-center justify-center px-6 py-12">
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 dark:bg-purple-900 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-30 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 dark:bg-blue-900 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-300 dark:bg-pink-900 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-30 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Main content -->
        <div class="relative z-10 max-w-4xl w-full">
            <!-- Logo/Brand -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-blue-600 rounded-2xl shadow-lg mb-6 transform hover:scale-110 transition-transform duration-300">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h1 class="text-6xl font-bold bg-gradient-to-r from-purple-600 via-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">
                    GoLevel Zen
                </h1>
                <p class="text-2xl text-slate-600 dark:text-slate-300 font-light">
                    Laravel Version
                </p>
            </div>

            <!-- Feature cards -->
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <!-- Laravel Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 01.375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.01.026-.023.041-.032h.001l4.513-2.598a.375.375 0 01.375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.011.02.018.04.024.06.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.039-.01-.012-.021-.023-.028-.037h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Laravel 12</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">The latest version of the elegant PHP framework</p>
                </div>

                <!-- Livewire Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900/30 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Livewire 3</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Dynamic interfaces without leaving PHP</p>
                </div>

                <!-- Tailwind Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-slate-200 dark:border-slate-700">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-2">Tailwind CSS 4</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Utility-first CSS framework for rapid UI development</p>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200 dark:border-slate-700">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4">
                    Ready to Build Something Amazing?
                </h2>
                <p class="text-slate-600 dark:text-slate-400 mb-6">
                    Your development environment is set up and ready to go. Start creating your next masterpiece.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="/graph" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        Open Graph Editor
                    </a>
                    <a href="https://laravel.com/docs" target="_blank" class="px-6 py-3 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                        Laravel Docs
                    </a>
                    <a href="https://livewire.laravel.com/docs" target="_blank" class="px-6 py-3 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 border border-slate-200 dark:border-slate-600">
                        Livewire Docs
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 mt-12 text-center text-slate-500 dark:text-slate-400 text-sm">
            <p>Powered by Laravel {{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
        </div>
    </div>

    @livewireScripts
</body>
</html>

