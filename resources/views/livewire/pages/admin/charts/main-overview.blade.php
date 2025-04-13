<?php
use Mary\Traits\Toast;
use Illuminate\Support\Arr;
use Livewire\Volt\Component;

new class extends Component {
    use Toast;

    public function mount()
    {
        // Initialize chart data
    }

    public array $overviewChart = [
        'type' => 'line',
        'data' => [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label' => 'Designer Signups',
                    'data' => [12, 19, 25, 32, 38, 45, 50, 56, 60, 65, 70, 75],
                    'borderColor' => 'rgba(219, 39, 119, 0.8)',
                    'backgroundColor' => 'rgba(219, 39, 119, 0.2)',
                ],
                [
                    'label' => 'Designs Uploaded',
                    'data' => [30, 45, 55, 60, 75, 85, 90, 100, 110, 120, 130, 140],
                    'borderColor' => 'rgba(79, 70, 229, 0.8)',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                ],
                [
                    'label' => 'Shirts Purchased',
                    'data' => [25, 35, 40, 50, 60, 65, 75, 80, 85, 90, 95, 100],
                    'borderColor' => 'rgba(16, 185, 129, 0.8)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                ]
            ],
        ],
        'options' => [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Platform Performance Overview'
                ]
            ]
        ]
    ];

    public function toggleDataset($index)
    {
        // This method would toggle visibility of a dataset
        $visibility = Arr::get($this->overviewChart, "data.datasets.$index.hidden", false);
        Arr::set($this->overviewChart, "data.datasets.$index.hidden", !$visibility);
    }

    public function changeChartType($type)
    {
        if (in_array($type, ['line', 'bar', 'radar'])) {
            Arr::set($this->overviewChart, 'type', $type);
        }
    }

    public function filterByTimeRange($range)
    {
        // This would update the chart data based on selected time range
        // Implementation would depend on how you want to filter data
    }
};
?>

<div class="overflow-y-scroll h-screen">
    <div class="flex justify-between mb-4">
        <div class="flex space-x-2">
            <x-mary-button label="Line Chart" wire:click="changeChartType('line')" size="sm" />
            <x-mary-button label="Bar Chart" wire:click="changeChartType('bar')" size="sm" />
            <x-mary-button label="Radar Chart" wire:click="changeChartType('radar')" size="sm" />
        </div>

        <div class="flex space-x-2">
            <x-mary-button label="Week" wire:click="filterByTimeRange('week')" size="sm" />
            <x-mary-button label="Month" wire:click="filterByTimeRange('month')" size="sm" />
            <x-mary-button label="Year" wire:click="filterByTimeRange('year')" size="sm" />
        </div>
    </div>

    <div class="h-80">
        <x-mary-chart wire:model="overviewChart" />
    </div>

    <div class="flex justify-center mt-4 space-x-4">
        <div class="flex items-center">
            <div class="w-4 h-4 rounded-full bg-pink-500 mr-2"></div>
            <button wire:click="toggleDataset(0)" class="text-sm text-black">Designer Signups</button>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 rounded-full bg-indigo-500 mr-2"></div>
            <button wire:click="toggleDataset(1)" class="text-sm text-black">Designs Uploaded</button>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
            <button wire:click="toggleDataset(2)" class="text-sm text-black">Shirts Purchased</button>
        </div>
    </div>
</div>
