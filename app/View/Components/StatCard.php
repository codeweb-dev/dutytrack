<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatCard extends Component
{
    public string $title;
    public string $value;
    public ?string $change;
    public string $changeColor;
    public string $icon = 'bolt';

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $title,
        string $value,
        ?string $change = null,
        string $changeColor = 'text-green-500',
        string $icon = 'bolt'
    ) {
        $this->title = $title;
        $this->value = $value;
        $this->change = $change;
        $this->changeColor = $changeColor;
        $this->icon = $icon;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.stat-card');
    }
}
