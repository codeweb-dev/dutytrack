<?php

namespace App\Livewire;

use App\Support\OnboardingTours;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class OnboardingTour extends Component
{
    public string $mode = 'closed';

    #[Locked]
    public int $step = 0;

    public function mount(): void
    {
        if (request()->boolean('tour')) {
            $this->mode = 'tour';
        } elseif (request()->routeIs('*.dashboard') && ! Auth::user()->has_completed_onboarding) {
            $this->mode = 'welcome';
        }
    }

    public function start(): void
    {
        $this->mode = 'tour';
        $this->step = 0;
    }

    public function next(): void
    {
        if ($this->step === count($this->steps()) - 1) {
            $this->dismiss();

            return;
        }

        $this->step++;
    }

    public function previous(): void
    {
        $this->step = max(0, $this->step - 1);
    }

    public function dismiss(): void
    {
        $user = Auth::user();

        if (! $user->has_completed_onboarding) {
            $user->forceFill(['has_completed_onboarding' => true])->save();
        }

        $this->mode = 'closed';
    }

    #[On('onboarding-replay')]
    public function replay(): mixed
    {
        $route = match (true) {
            Auth::user()->hasRole('admin') => 'admin.dashboard',
            Auth::user()->hasRole('ojt_coordinator') => 'coordinator.dashboard',
            Auth::user()->hasRole('supervisor') => 'supervisor.dashboard',
            Auth::user()->hasRole('student') => 'student.dashboard',
            default => null,
        };

        if ($route === null) {
            return null;
        }

        return $this->redirectRoute($route, ['tour' => 1]);
    }

    protected function steps(): array
    {
        return OnboardingTours::for(Auth::user());
    }

    public function render(): mixed
    {
        return view('livewire.onboarding-tour', ['steps' => $this->steps()]);
    }
}
