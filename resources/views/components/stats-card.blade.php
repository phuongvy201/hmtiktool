@props(['title', 'value', 'icon', 'color' => 'blue', 'trend' => null, 'trendValue' => null])

@php
    $colors = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'red' => 'bg-red-100 text-red-600',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
    
    $iconColors = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm font-medium text-slate-500 uppercase mb-1">{{ $title }}</div>
            <div class="text-3xl font-bold text-slate-800">{{ $value }}</div>
            @if($trend && $trendValue)
                <div class="flex items-center mt-2">
                    @if($trend === 'up')
                        <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L12 10.586z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L12 9.414z" clip-rule="evenodd" />
                        </svg>
                    @endif
                    <span class="text-sm {{ $trend === 'up' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $trendValue }}
                    </span>
                </div>
            @endif
        </div>
        <div class="w-12 h-12 {{ $iconColors }} rounded-lg flex items-center justify-center">
            {!! $icon !!}
        </div>
    </div>
</div>
