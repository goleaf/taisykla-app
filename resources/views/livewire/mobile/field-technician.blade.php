<div class="flex flex-col min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    {{-- Header --}}
    @include('livewire.mobile.partials.header')

    {{-- Main Content Area --}}
    <main class="flex-1 overflow-y-auto scroll-smooth safe-area-bottom pb-20">
        @if ($activeScreen === 'jobs')
            @include('livewire.mobile.partials.job-list')
        @elseif ($activeScreen === 'detail')
            @include('livewire.mobile.partials.job-detail')
        @elseif ($activeScreen === 'photos')
            @include('livewire.mobile.partials.photo-capture')
        @elseif ($activeScreen === 'notes')
            @include('livewire.mobile.partials.work-notes')
        @elseif ($activeScreen === 'parts')
            @include('livewire.mobile.partials.parts-usage')
        @elseif ($activeScreen === 'time')
            @include('livewire.mobile.partials.time-tracking')
        @elseif ($activeScreen === 'signoff')
            @include('livewire.mobile.partials.customer-signoff')
        @endif
    </main>

    {{-- Bottom Navigation --}}
    @include('livewire.mobile.partials.bottom-nav')

    {{-- Quick Actions FAB --}}
    @include('livewire.mobile.partials.quick-actions')

    {{-- Modals --}}
    @include('livewire.mobile.partials.modals')
</div>