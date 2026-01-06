{{-- Month View --}}
<div>
    {{-- Days of Week Header --}}
    <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div
                class="p-3 text-center text-sm font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                {{ $day }}
            </div>
        @endforeach
    </div>

    {{-- Calendar Grid --}}
    @php
        $weeks = collect($calendarData)->chunk(7);
    @endphp

    @foreach($weeks as $week)
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
            @foreach($week as $dateKey => $dayData)
                <div class="min-h-[100px] p-2 border-r border-gray-200 dark:border-gray-700 last:border-r-0
                                    {{ $dayData['isToday'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}
                                    {{ !$dayData['isCurrentMonth'] ? 'bg-gray-50 dark:bg-gray-900/50' : '' }}">
                    {{-- Day Number --}}
                    <div class="flex items-center justify-between mb-1">
                        <span
                            class="text-sm font-medium {{ $dayData['isToday'] ? 'text-blue-600 dark:text-blue-400' : ($dayData['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600') }}">
                            {{ $dayData['date']->format('j') }}
                        </span>
                        @if($dayData['schedules']->count() > 3)
                            <span class="text-xs text-gray-500">+{{ $dayData['schedules']->count() - 3 }}</span>
                        @endif
                    </div>

                    {{-- Schedules (max 3 visible) --}}
                    <div class="space-y-1">
                        @foreach($dayData['schedules']->take(3) as $schedule)
                            @php
                                $bgColor = match ($schedule->type) {
                                    'availability' => 'bg-green-200 dark:bg-green-800',
                                    'blocked' => 'bg-red-200 dark:bg-red-800',
                                    'appointment' => 'bg-blue-200 dark:bg-blue-800',
                                    default => 'bg-gray-200',
                                };
                                $textColor = match ($schedule->type) {
                                    'availability' => 'text-green-900 dark:text-green-100',
                                    'blocked' => 'text-red-900 dark:text-red-100',
                                    'appointment' => 'text-blue-900 dark:text-blue-100',
                                    default => 'text-gray-900',
                                };
                            @endphp
                            <div class="px-2 py-0.5 rounded text-xs truncate {{ $bgColor }} {{ $textColor }}"
                                title="{{ $schedule->name }} @foreach($schedule->periods as $p) {{ $p->start_time }}-{{ $p->end_time }} @endforeach">
                                @if($schedule->periods->first())
                                    <span class="font-medium">{{ substr($schedule->periods->first()->start_time, 0, 5) }}</span>
                                @endif
                                {{ Str::limit($schedule->name, 12) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>