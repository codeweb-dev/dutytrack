<section class="bg-gray-100 px-4 py-16 dark:bg-zinc-950 sm:px-6 sm:py-20 lg:py-24" id="teams">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16
            opacity-0 translate-y-6
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-500 ease-out intersect-once">
            <div class="inline-block mb-5">
                <flux:badge rounded icon="sparkles" color="red">Our Team</flux:badge>
            </div>
            <h2 class="text-4xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight mb-4">
                The People Behind<br>DutyTrack
            </h2>
            <p class="text-lg text-gray-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
                A small but passionate team building tools that make OJT
                management simpler, smarter, and more transparent.
            </p>
        </div>

        <div class="opacity-0 translate-y-8
            intersect:opacity-100 intersect:translate-y-0
            transition-all duration-600 ease-out intersect-once mb-5">
            <div class="relative bg-white dark:bg-zinc-900 rounded-3xl border border-gray-100 dark:border-zinc-800 overflow-hidden p-8 flex flex-col lg:flex-row items-center lg:items-start gap-8">
                <div class="absolute top-0 right-0 w-80 h-80 rounded-full pointer-events-none opacity-5 dark:opacity-10"
                    style="background: radial-gradient(circle, #DC2626, transparent 70%); transform: translate(30%, -30%);"></div>

                <div class="relative shrink-0">
                    <img src="{{ asset('teams/allen.png') }}" class="w-52 h-52 rounded-2xl object-cover object-center" />

                    <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-white dark:bg-zinc-900 rounded-full flex items-center justify-center shadow-md border border-gray-100 dark:border-zinc-700">
                        <flux:icon.check-badge class="size-6 text-red-500" />
                    </div>
                </div>

                <div class="flex-1 text-center lg:text-left">
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-1">Allen I. Labrague</h3>
                    <p class="text-md text-red-600 dark:text-red-400 uppercase mb-4">Leader & Solo Developer</p>
                    <p class="text-sm text-gray-500 dark:text-zinc-400 leading-relaxed max-w-lg mb-6">
                        Built DutyTrack from the ground up — from the database architecture to the last pixel of the UI.
                        Handles everything so students and supervisors don't have to.
                    </p>

                    <div class="flex flex-wrap gap-2 justify-center lg:justify-start">
                        <span class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-medium px-3 py-1.5 rounded-lg">
                            <svg class="size-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                            </svg>
                            Developer
                        </span>

                        <span class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-medium px-3 py-1.5 rounded-lg">
                            <svg class="size-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                            </svg>
                            Frontend
                        </span>

                        {{-- Backend --}}
                        <span class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-medium px-3 py-1.5 rounded-lg">
                            <svg class="size-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            </svg>
                            Backend
                        </span>

                        <span class="inline-flex items-center gap-1.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-zinc-300 text-xs font-medium px-3 py-1.5 rounded-lg">
                            <svg class="size-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                            UI/UX Designer
                        </span>

                    </div>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-5">
            @foreach ([
                [
                    'initials' => 'DM',
                    'name'     => 'Drexel B. Malilay',
                    'role'     => 'Member',
                    'gender'   => 'female',
                    'from'     => 'from-blue-500',
                    'to'       => 'to-blue-700',
                    'shadow'   => 'shadow-blue-200 dark:shadow-blue-950/40',
                    'delay'    => '0',
                    'image'    => 'drex.jpg',
                    'tags'     => ['Reference Manager', 'Diagram Designer'],
                ],
                [
                    'initials' => 'KN',
                    'name'     => 'Khian Nixon L. Banatlao',
                    'role'     => 'Member',
                    'gender'   => 'male',
                    'from'     => 'from-emerald-500',
                    'to'       => 'to-emerald-700',
                    'shadow'   => 'shadow-emerald-200 dark:shadow-emerald-950/40',
                    'delay'    => '75',
                    'image'    => 'khian.png',
                    'tags'     => ['Data Analyst', 'Tester'],
                ],
                [
                    'initials' => 'SF',
                    'name'     => 'Shanley I. Faye Datiles',
                    'role'     => 'Member',
                    'gender'   => 'female',
                    'from'     => 'from-pink-500',
                    'to'       => 'to-pink-700',
                    'shadow'   => 'shadow-pink-200 dark:shadow-pink-950/40',
                    'delay'    => '150',
                    'image'    => 'shanley.png',
                    'tags'     => ['Data Collector', 'RRL Writer'],
                ],
                [
                    'initials' => 'AE',
                    'name'     => 'Althea P. Entona',
                    'role'     => 'Member',
                    'gender'   => 'female',
                    'from'     => 'from-violet-500',
                    'to'       => 'to-violet-700',
                    'shadow'   => 'shadow-violet-200 dark:shadow-violet-950/40',
                    'delay'    => '225',
                    'image'    => 'entona.png',
                    'tags'     => ['Citation Specialist', 'Literature Reviewer'],
                ],
            ] as $m)
                <div
                    class="group w-full sm:max-w-none mx-auto bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300
                        opacity-0 translate-y-8
                        intersect:opacity-100 intersect:translate-y-0
                        intersect-once"
                    style="transition-duration: 500ms; transition-delay: {{ $m['delay'] }}ms;">
                    <div class="relative aspect-4/3 sm:aspect-5/4 lg:aspect-4/3 bg-linear-to-br {{ $m['from'] }} {{ $m['to'] }} overflow-hidden">
                        <img
                            src="{{ asset('teams/' . $m['image']) }}"
                            alt="{{ $m['name'] }}"
                            class="w-full h-full object-cover" />
                    </div>

                    <div class="p-5">
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <h4 class="text-md font-bold text-gray-900 dark:text-white leading-tight">{{ $m['name'] }}</h4>
                            <flux:icon.check-badge class="size-4 text-red-500 shrink-0" />
                        </div>

                        <p class="text-xs text-gray-400 dark:text-zinc-500 font-semibold uppercase tracking-widest mb-3">{{ $m['role'] }}</p>

                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($m['tags'] as $tag)
                                <span class="text-xs bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 px-2 py-0.5 rounded-md font-medium">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
