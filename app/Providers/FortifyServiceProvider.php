<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request): ?User {
            $role = $request->validate([
                'role' => ['required', Rule::in(array_column(Role::cases(), 'value'))],
                'birthdate' => [$request->input('role') === Role::STUDENT->value ? 'required' : 'nullable', 'date', 'before:today'],
            ])['role'];

            $query = User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', $role))
                ->when(
                    $role === Role::STUDENT->value,
                    fn ($query) => $query->whereHas('studentProfile', fn ($query) => $query
                        ->whereRaw('LOWER(student_id) = ?', [Str::lower($request->input(Fortify::username()))])
                        ->whereDate('birthdate', $request->input('birthdate'))),
                    fn ($query) => $query->where('email', $request->input(Fortify::username())),
                );

            $user = (clone $query)->first();

            // Rejected sign-ups are archived; tell the student why instead of "invalid credentials".
            $user ??= $query->onlyTrashed()->whereNotNull('rejection_reason')->latest('deleted_at')->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return null;
            }

            if ($user->trashed()) {
                throw ValidationException::withMessages([
                    'account' => __('Your sign-up was rejected by your OJT coordinator. Reason: :reason', ['reason' => $user->rejection_reason]),
                ]);
            }

            if ($user->status === 'pending') {
                throw ValidationException::withMessages([
                    'account' => __('Your account is still under review by your OJT coordinator.'),
                ]);
            }

            if ($user->status === 'inactive') {
                throw ValidationException::withMessages([
                    'account' => __('Your account has been set to inactive by your OJT coordinator.'),
                ]);
            }

            return $user;
        });

        // Fortify logs new users in; students wait for coordinator approval instead.
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        {
            public function toResponse($request)
            {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', __('Account created! Your OJT coordinator will review it before you can log in.'));
            }
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(fn () => view('pages::auth.register'));
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
