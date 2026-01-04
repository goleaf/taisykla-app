<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Component;

new class extends Component {
    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->generateBreadcrumbs();
    }

    public function generateBreadcrumbs(): void
    {
        $route = Route::current();
        if (!$route)
            return;

        $routeName = $route->getName();
        if (!$routeName)
            return;

        $this->breadcrumbs = [];

        // Always start with Dashboard if not on dashboard
        if ($routeName !== 'dashboard' && $routeName !== 'customer.portal') {
            $this->breadcrumbs[] = ['label' => 'Dashboard', 'route' => 'dashboard'];
        }

        // Handle specific route groups
        if (str_starts_with($routeName, 'knowledge-base.')) {
            $this->breadcrumbs[] = ['label' => 'Knowledge Base', 'route' => 'knowledge-base.index'];

            if ($routeName === 'knowledge-base.show') {
                $article = $route->parameter('article');
                if ($article instanceof \App\Models\KnowledgeArticle) {
                    $this->breadcrumbs[] = ['label' => $article->title, 'route' => null];
                }
            } elseif ($routeName === 'knowledge-base.search') {
                $this->breadcrumbs[] = ['label' => 'Search', 'route' => null];
            } elseif ($routeName === 'knowledge-base.manage') {
                $this->breadcrumbs[] = ['label' => 'Manage', 'route' => null];
            }
            return;
        }

        // Simple mapping based on route name segments for other routes
        $segments = explode('.', $routeName);

        if (count($segments) >= 1) {
            $resource = $segments[0];
            $action = $segments[1] ?? null;

            if ($resource === 'dashboard' || $resource === 'customer') {
                if ($resource === 'customer' && $action === 'portal') {
                    $this->breadcrumbs = [['label' => 'Customer Portal', 'route' => null]];
                }
                return;
            }

            $resourceLabel = str_replace('-', ' ', $resource);
            $resourceLabel = \Illuminate\Support\Str::plural(ucwords($resourceLabel)); // Ensure plural for index

            if ($action === 'index' || $action === null) {
                $this->breadcrumbs[] = ['label' => $resourceLabel, 'route' => null];
            } else {
                $indexRoute = "$resource.index";
                if (Route::has($indexRoute)) {
                    $this->breadcrumbs[] = ['label' => $resourceLabel, 'route' => $indexRoute];
                } else {
                    $this->breadcrumbs[] = ['label' => $resourceLabel, 'route' => null];
                }

                if ($action === 'show') {
                    // Try to get model for specific label
                    $paramName = \Illuminate\Support\Str::camel(\Illuminate\Support\Str::singular($resource));
                    $model = $route->parameter($paramName) ?? $route->parameter($resource);

                    if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                        $label = match (get_class($model)) {
                            \App\Models\Equipment::class => $model->name ?? ($model->manufacturer . ' ' . $model->model) ?? $model->serial_number,
                            \App\Models\WorkOrder::class => 'WO #' . $model->id . ($model->subject ? ': ' . \Illuminate\Support\Str::limit($model->subject, 20) : ''),
                            \App\Models\ServiceRequest::class => 'Request #' . $model->id,
                            \App\Models\Invoice::class => 'Invoice #' . $model->number,
                            \App\Models\Quote::class => 'Quote #' . $model->number,
                            default => $model->name ?? $model->title ?? $model->subject ?? "Details (#{$model->id})"
                        };

                        $this->breadcrumbs[] = ['label' => $label, 'route' => null];
                    } else {
                        $this->breadcrumbs[] = ['label' => 'Details', 'route' => null];
                    }
                } else {
                    $actionLabel = str_replace(['-', 'edit', 'create'], ['', 'Edit', 'Create'], $action);
                    $this->breadcrumbs[] = ['label' => ucwords($actionLabel), 'route' => null];
                }
            }
        }
    }
}; ?>

<nav class="flex mb-4" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        @foreach ($breadcrumbs as $breadcrumb)
            <li class="inline-flex items-center">
                @if (!$loop->first)
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                @endif

                @if ($breadcrumb['route'] && !$loop->last)
                    @php
                        $routeParams = [];
                        try {
                            $routeParams = request()->route() ? request()->route()->parameters() : [];
                        } catch (\Throwable $e) {
                        }
                    @endphp
                    <a href="{{ route($breadcrumb['route'], $routeParams) }}" wire:navigate
                        class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors duration-200">
                        @if ($loop->first)
                            <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                            </svg>
                        @endif
                        {{ $breadcrumb['label'] }}
                    </a>
                @else
                    <span
                        class="inline-flex items-center text-sm font-medium {{ $loop->last ? 'text-gray-500 cursor-default' : 'text-gray-700' }}">
                        @if ($loop->first)
                            <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                            </svg>
                        @endif
                        {{ $breadcrumb['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>