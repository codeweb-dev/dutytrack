<x-layouts::auth :title="__('Register')" wide>
    <div class="flex flex-col gap-8">
        <x-auth-header :title="__('Create your student account')" :description="__('Sign up to start logging your OJT duty hours')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="flex flex-col gap-8">
            @csrf

            <!-- Profile Photo -->
            <div x-data="{ preview: null, tooBig: false }" class="flex flex-col items-center gap-3">
                <label for="photo" class="group relative cursor-pointer">
                    <div
                        class="flex size-24 items-center justify-center overflow-hidden rounded-full bg-zinc-100 ring-4 ring-white transition group-hover:ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-900 dark:group-hover:ring-zinc-700">
                        <template x-if="preview">
                            <img :src="preview" alt="" class="size-full object-cover" />
                        </template>
                        <flux:icon.user x-show="!preview" class="size-10 text-zinc-400" />
                    </div>
                    <span
                        class="absolute bottom-0 right-0 flex size-8 items-center justify-center rounded-full bg-zinc-900 text-white shadow ring-2 ring-white dark:bg-white dark:text-zinc-900 dark:ring-zinc-900">
                        <flux:icon.camera variant="micro" />
                    </span>
                </label>
                <input id="photo" name="photo" type="file" accept="image/*" class="sr-only"
                    x-on:change="
                        const file = $event.target.files[0];
                        tooBig = !!file && file.size > 2 * 1024 * 1024;
                        if (tooBig) $event.target.value = '';
                        preview = file && !tooBig ? URL.createObjectURL(file) : null;
                    " />
                <div class="flex items-center gap-2">
                    <flux:text class="text-xs">{{ __('Upload a profile photo (max 2MB)') }}</flux:text>
                    <flux:badge size="sm">{{ __('Optional') }}</flux:badge>
                </div>
                <flux:text x-show="tooBig" x-cloak class="text-xs !text-red-500">{{ __('That photo is larger than 2MB. Please choose a smaller one.') }}</flux:text>
                <flux:error name="photo" />
            </div>

            <!-- Personal Information -->
            <section class="flex flex-col gap-5">
                <div class="flex items-center gap-3">
                    <flux:heading>{{ __('Personal information') }}</flux:heading>
                    <flux:separator class="flex-1" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <flux:input class="sm:order-1" name="first_name" :label="__('First name')" :value="old('first_name')" required autofocus
                        autocomplete="given-name" placeholder="Juan" />
                    <flux:input class="sm:order-3" name="middle_name" :label="__('Middle name')" :value="old('middle_name')" badge="Optional"
                        autocomplete="additional-name" placeholder="Santos" />
                    <flux:input class="sm:order-2" name="last_name" :label="__('Last name')" :value="old('last_name')" required
                        autocomplete="family-name" placeholder="Dela Cruz" />
                    <flux:select class="sm:order-4" name="extension_name" :label="__('Extension name')" badge="Optional">
                        <option value="">None</option>
                        @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $extension)
                            <option value="{{ $extension }}" @selected(old('extension_name') === $extension)>{{ $extension }}</option>
                        @endforeach
                    </flux:select>

                    <flux:field class="sm:order-5">
                        <flux:label>{{ __('Contact number') }}</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>+63</flux:input.group.prefix>
                            <flux:input name="contact_number" :value="old('contact_number')" type="tel" required
                                autocomplete="tel-national" placeholder="9123456789" />
                        </flux:input.group>
                        <flux:error name="contact_number" />
                    </flux:field>

                    <flux:input class="sm:order-6" name="email" :label="__('Email address')" :value="old('email')" type="email" required
                        autocomplete="email" placeholder="email@example.com" />

                    <div class="sm:col-span-2 sm:order-7">
                        <flux:input name="address" :label="__('Address')" :value="old('address')" required
                            autocomplete="street-address" placeholder="Street, Barangay, City" />
                    </div>
                </div>
            </section>

            <!-- Academic Information -->
            <section class="flex flex-col gap-5">
                <div class="flex items-center gap-3">
                    <flux:heading>{{ __('Academic information') }}</flux:heading>
                    <flux:separator class="flex-1" />
                </div>

                <flux:input name="student_id" :label="__('Student ID')" :value="old('student_id')" required
                    placeholder="2316-02373C" description="{{ __('Found on your school ID card.') }}" />

                <flux:input name="birthdate" :label="__('Birthdate')" :value="old('birthdate')" type="date" required />

                <div class="grid gap-5 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Course') }}</flux:label>
                        <div class="grid grid-cols-2 gap-1 rounded-lg bg-zinc-800/5 p-1 dark:bg-white/10">
                            @foreach (array_keys(\App\Actions\Fortify\CreateNewUser::COURSES) as $course)
                                <label class="cursor-pointer">
                                    <input type="radio" name="course" value="{{ $course }}" class="peer sr-only" required
                                        @checked(old('course', 'BSCS') === $course) />
                                    <span
                                        class="flex h-8 items-center justify-center rounded-md text-sm font-medium text-zinc-500 transition peer-checked:bg-white peer-checked:text-zinc-900 peer-checked:shadow-xs peer-focus-visible:ring-2 peer-focus-visible:ring-zinc-400 dark:text-zinc-400 dark:peer-checked:bg-zinc-700 dark:peer-checked:text-white">
                                        {{ $course }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <flux:error name="course" />
                    </flux:field>

                    <flux:input :label="__('Year level')" value="4th Year" readonly icon="academic-cap" />
                </div>
            </section>

            <!-- Guardian Information -->
            <section class="flex flex-col gap-5">
                <div class="flex items-center gap-3">
                    <flux:heading>{{ __('Guardian information') }}</flux:heading>
                    <flux:separator class="flex-1" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <flux:input name="guardian_name" :label="__('Guardian name')" :value="old('guardian_name')" required
                        placeholder="Maria Dela Cruz" />

                    <flux:field>
                        <flux:label>{{ __('Guardian contact number') }}</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>+63</flux:input.group.prefix>
                            <flux:input name="guardian_contact_number" :value="old('guardian_contact_number')" type="tel"
                                required placeholder="9123456789" />
                        </flux:input.group>
                        <flux:error name="guardian_contact_number" />
                    </flux:field>

                    <div class="sm:col-span-2">
                        <flux:input name="guardian_address" :label="__('Guardian address')" :value="old('guardian_address')"
                            required placeholder="Street, Barangay, City" />
                    </div>
                </div>
            </section>

            <!-- Security -->
            <section class="flex flex-col gap-5" x-data="{ password: '' }">
                <div class="flex items-center gap-3">
                    <flux:heading>{{ __('Account security') }}</flux:heading>
                    <flux:separator class="flex-1" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <flux:input name="password" :label="__('Password')" type="password" required
                        autocomplete="new-password" :placeholder="__('Password')" viewable x-model="password" />

                    <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" required
                        autocomplete="new-password" :placeholder="__('Confirm password')" viewable />

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/80 p-4 sm:col-span-2 dark:border-white/10 dark:bg-white/5">
                        <p class="mb-3 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                            {{ __('Your password must include') }}
                        </p>
                        <ul class="space-y-2 text-sm sm:grid sm:grid-cols-2 sm:gap-x-6 sm:gap-y-2 sm:space-y-0"
                            aria-label="{{ __('Password requirements') }}">
                            <li class="flex items-center gap-2.5 transition-colors"
                                :class="Array.from(password).length >= 12 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'">
                                <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center rounded-full bg-zinc-200/70 text-xs dark:bg-white/10"
                                    :class="Array.from(password).length >= 12 ? 'bg-emerald-100 dark:bg-emerald-400/15' : ''"
                                    x-text="Array.from(password).length >= 12 ? '✓' : '○'"></span>
                                {{ __('At least 12 characters') }}
                            </li>
                            <li class="flex items-center gap-2.5 transition-colors"
                                :class="/\p{Lu}/u.test(password) ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'">
                                <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center rounded-full bg-zinc-200/70 text-xs dark:bg-white/10"
                                    :class="/\p{Lu}/u.test(password) ? 'bg-emerald-100 dark:bg-emerald-400/15' : ''"
                                    x-text="/\p{Lu}/u.test(password) ? '✓' : '○'"></span>
                                {{ __('An uppercase letter') }}
                            </li>
                            <li class="flex items-center gap-2.5 transition-colors"
                                :class="/\p{Ll}/u.test(password) ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'">
                                <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center rounded-full bg-zinc-200/70 text-xs dark:bg-white/10"
                                    :class="/\p{Ll}/u.test(password) ? 'bg-emerald-100 dark:bg-emerald-400/15' : ''"
                                    x-text="/\p{Ll}/u.test(password) ? '✓' : '○'"></span>
                                {{ __('A lowercase letter') }}
                            </li>
                            <li class="flex items-center gap-2.5 transition-colors"
                                :class="/\p{N}/u.test(password) ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'">
                                <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center rounded-full bg-zinc-200/70 text-xs dark:bg-white/10"
                                    :class="/\p{N}/u.test(password) ? 'bg-emerald-100 dark:bg-emerald-400/15' : ''"
                                    x-text="/\p{N}/u.test(password) ? '✓' : '○'"></span>
                                {{ __('A number') }}
                            </li>
                            <li class="flex items-center gap-2.5 transition-colors"
                                :class="/[\p{Z}\p{S}\p{P}]/u.test(password) ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400'">
                                <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center rounded-full bg-zinc-200/70 text-xs dark:bg-white/10"
                                    :class="/[\p{Z}\p{S}\p{P}]/u.test(password) ? 'bg-emerald-100 dark:bg-emerald-400/15' : ''"
                                    x-text="/[\p{Z}\p{S}\p{P}]/u.test(password) ? '✓' : '○'"></span>
                                {{ __('A symbol (such as !, @, or #)') }}
                            </li>
                        </ul>
                        @if (app()->isProduction())
                            <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Your password is also checked against known data breaches.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
