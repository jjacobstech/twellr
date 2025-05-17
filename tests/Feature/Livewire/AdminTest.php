<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Admin::class)
            ->assertStatus(200);
    }
}
