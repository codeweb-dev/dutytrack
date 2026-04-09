<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageHeader extends Component
{
    public array $breadcrumbs;
    public string $title;
    public ?string $description;

    /**
     * Create a new component instance.
     */
    public function __construct(
        array $breadcrumbs = [],
        string $title = '',
        ?string $description = null
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.page-header');
    }
}
