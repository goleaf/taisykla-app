{{-- Day View --}}
<div class="p-6">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $currentDate->format('l') }}
        </h2>
        <p class="text-gray-500 dark:text-gray-400">{{ $currentDate->format('F j, Y') }}</p>
    </div>

    {{-- Time Grid --}}
    <div class="space-y-1">
        @for($hour = 6; $hour <= 20; $hour++)
            @php
                $timeString = sprintf('%02d:00', $hour);
                $dateKey = $currentDate->format('Y-m-d');
                $dayData = $calendarData[$dateKey] ?? null;
                $hourSchedules = collect();
                
                if ($dayData) {
                    $hourSchedules = $dayData['schedules']->filter(function($schedule) use ($hour) {
                        foreach ($schedule->periods as $period) {
                            $startHour = (int) substr($period->start_time, 0, 2);
                            $endHour = (int) substr($period->end_time, 0, 2);
                            if ($hour >= $startHour && $hour < $endHour) {
                                return true;
                            }
                        }
                        return false;
                    });
                }
            @endphp
            
            <div class="flex">
                <div class="w-20 flex-shrink-0 pr-4 py-3 text-right">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $timeString }}</span>
                </div>
                <div class="flex-1 border-l border-gray-200 dark:border-gray-700 pl-4 py-2 min-h-[60px] relative
                            {{ $hour >= 8 && $hour < 18 ? 'bg-gray-50 dark:bg-gray-700/30' : '' }}">
                    @foreach($hourSchedules as $schedule)
                        @php
                            $bgColor = match($schedule->type) {
                                'availability' => 'bg-green-100 border-green-400 dark:bg-green-900/30 dark:border-green-600',
                                'blocked' => 'bg-red-100 border-red-400 dark:bg-red-900/30 dark:border-red-600',
                                'appointment' => 'bg-blue-100 border-blue-400 dark:bg-blue-900/30 dark:border-blue-600',
                                default => 'bg-gray-100 border-gray-400',
                            };
                            $textColor = match($schedule->type) {
                                'availability' => 'text-green-800 dark:text-green-200',
                                'blocked' => 'text-red-800 dark:text-red-200',
                                'appointment' => 'text-blue-800 dark:text-blue-200',
                                default => 'text-gray-800',
                            };
                        @endphp
                        <div class="absolute inset-x-4 px-3 py-2 rounded-lg border-l-4 {{ $bgColor }}" style="top: 4px;">
                            <p class="text-sm font-medium {{ $textColor }}">{{ $schedule->name }}</p>
                            @foreach($schedule->periods as $period)
                                <p class="text-xs {{ $textColor }} opacity-75">{{ $period->start_time }} - {{ $period->end_time }}</p>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endfor
    </div>
</div>
