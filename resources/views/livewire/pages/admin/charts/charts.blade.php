<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

new class extends Component {
    use Toast;

    public function mount()
    {
        //  $this->dispatchToast('success', 'Welcome to the dashboard!');
    }
    public array $myChart = [
        'type' => 'pie',
        'data' => [
            'labels' => ['Mary', 'Joe', 'Ana'],
            'datasets' => [
                [
                    'label' => '# of Votes',
                    'data' => [12, 19, 3],
                ],
            ],
        ],
    ];

    public function randomize()
    {
        Arr::set($this->myChart, 'data.datasets.0.data', [fake()->randomNumber(2), fake()->randomNumber(2), fake()->randomNumber(2)]);
    }

    public function switch()
    {
        $type = $this->myChart['type'] == 'bar' ? 'pie' : 'bar';
        Arr::set($this->myChart, 'type', $type);
    }
};

?>
<div>
    <div class="grid gap-5">
        <x-mary-button label="Randomize" wire:click="randomize" class="btn-primary" spinner />
        <x-mary-button label="Switch" wire:click="switch" spinner />
    </div>

    <x-mary-chart wire:model="myChart" />
</div>
