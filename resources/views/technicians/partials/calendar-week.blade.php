{{-- Week View --}}
<div class="overflow-x-auto">
    {{-- Days Header --}}
    <div class="grid grid-cols-8 border-b border-gray-200 dark:border-gray-700">
        <div
            class="p-3 text-center text-sm font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">
            Time
        </div>
        @foreach($calendarData as $dateKey => $dayData)
            <div class="p-3 text-center border-r border-gray-200 dark:border-gray-700 last:border-r-0
                            {{ $dayData['isToday'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                    {{ $dayData['date']->format('D') }}
                </p>
                <p
                    class="text-lg font-bold {{ $dayData['isToday'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                    {{ $dayData['date']->format('j') }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- Time Grid --}}
    @for($hour = 8; $hour <= 18; $hour++)
        <div class="grid grid-cols-8 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
            <div
                class="p-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">
                {{ sprintf('%02d:00', $hour) }}
            </div>
            @foreach($calendarData as $dateKey => $dayData)
                @php
                    $hourSchedules = $dayData['schedules']->filter(function ($schedule) use ($hour) {
                        foreach ($schedule->periods as $period) {
                            $startHour = (int) substr($period->start_time, 0, 2);
                            $endHour = (int) substr($period->end_time, 0, 2);
                            if ($hour >= $startHour && $hour < $endHour) {
                                return true;
                            }
                        }
                        return false;
                    });
                @endphp
                <div class="min-h-[50px] p-1 border-r border-gray-200 dark:border-gray-700 last:border-r-0
                                    {{ $dayData['isToday'] ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    @foreach($hourSchedules as $schedule)
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
                        <div class="px-1 py-0.5 mb-0.5 rounded text-xs truncate {{ $bgColor }} {{ $textColor }}"
                            title="{{ $schedule->name }}">
                            {{ Str::limit($schedule->name, 15) }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endfor
</div>