<?php

namespace App\Livewire;

use App\Services\MenuResolver;
use Livewire\Component;

class FluxSidebar extends Component
{
    public array $menuGroups = [];
    public array $mobileConfig = [];

    public function mount(): void
    {
        $this->loadMenu();
    }

    public function loadMenu(): void
    {
        $resolver = app(MenuResolver::class);
        $this->menuGroups = $resolver->resolve();
        $this->mobileConfig = $resolver->getMobileConfig();
    }

    public function render()
    {
        return view('components.flux-sidebar');
    }
}