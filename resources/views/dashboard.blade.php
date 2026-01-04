<x-app-layout>
    <style>
        :root {
            --tech-ink: 15, 23, 42;
            --tech-emerald: 16, 185, 129;
            --tech-amber: 245, 158, 11;
            --tech-sky: 14, 165, 233;
            --tech-rust: 249, 115, 22;
        }

        .route-surface {
            background-image:
                radial-gradient(circle at 18% 20%, rgba(var(--tech-emerald), 0.25), transparent 40%),
                radial-gradient(circle at 80% 10%, rgba(var(--tech-rust), 0.2), transparent 35%),
                linear-gradient(140deg, rgba(var(--tech-ink), 0.95), rgba(3, 44, 38, 0.95));
        }

        .route-grid {
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.15) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.15) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    <div class="admin-atmosphere bg-slate-100">
        <section
            class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 text-white">
            <div class="absolute inset-0 admin-atmosphere opacity-40"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 admin-fade">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-[0.3em] text-emerald-200">Field Ops</p>
                        <h1 class="text-3xl sm:text-4xl font-['DM_Serif_Display']">Field Technician Dashboard</h1>
                        <p class="text-sm text-slate-200">Wednesday, Oct 4 - Central District - Shift 07:30 to 17:30</p>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-200">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">Status: On
                                Site</span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">Vehicle: Van
                                23</span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1">Weather:
                                Light rain</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3">
                            <p class="text-xs text-emerald-100">Next stop</p>
                            <p class="text-sm font-semibold">Acme Tower - 1.2 mi</p>
                            <p class="text-xs text-slate-200">ETA 08:24 with traffic</p>
                        </div>
                        <button
                            class="min-h-[52px] rounded-xl bg-emerald-400 px-5 text-sm font-semibold text-slate-900 shadow-lg shadow-emerald-900/30">
                            Start Navigation
                        </button>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 admin-stagger">
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-200">Jobs Today</p>
                        <p class="mt-2 text-2xl font-semibold">4</p>
                        <p class="text-xs text-slate-200">1 urgent, 1 high, 1 normal, 1 routine</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-200">Travel Window</p>
                        <p class="mt-2 text-2xl font-semibold">1h 12m</p>
                        <p class="text-xs text-slate-200">Traffic +14 min</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-200">Parts to Pick</p>
                        <p class="mt-2 text-2xl font-semibold">3</p>
                        <p class="text-xs text-slate-200">2 in van, 1 at depot</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-200">On-Time Rate</p>
                        <p class="mt-2 text-2xl font-semibold">94%</p>
                        <p class="text-xs text-slate-200">Last 7 days</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-12 space-y-8">
            <section
                class="rounded-2xl bg-white shadow-lg shadow-slate-200/60 border border-slate-200/70 p-4 sm:p-6 admin-fade">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-['Space_Grotesk'] text-slate-900">Status Controls</h2>
                        <p class="text-sm text-slate-500">Update dispatch with one tap.</p>
                    </div>
                    <button
                        class="min-h-[52px] rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white shadow-lg shadow-rose-200/60">
                        Emergency Alert
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <button class="min-h-[56px] rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                        Available
                    </button>
                    <button
                        class="min-h-[56px] rounded-xl border border-amber-200 bg-amber-50 text-sm font-semibold text-amber-800">
                        Traveling
                    </button>
                    <button class="min-h-[56px] rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                        On Site
                    </button>
                    <button
                        class="min-h-[56px] rounded-xl border border-emerald-200 bg-emerald-600 text-sm font-semibold text-white shadow-lg shadow-emerald-200/60">
                        Working
                    </button>
                    <button class="min-h-[56px] rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                        On Break
                    </button>
                    <button class="min-h-[56px] rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                        Off Duty
                    </button>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <button
                        class="min-h-[56px] rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white shadow-md shadow-slate-200/60">
                        Check In to Job
                    </button>
                    <button
                        class="min-h-[56px] rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700">
                        Check Out and Complete
                    </button>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <section
                    class="xl:col-span-7 rounded-2xl bg-white shadow-lg shadow-slate-200/60 border border-slate-200/70 p-4 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-['Space_Grotesk'] text-slate-900">Today's Work Queue</h2>
                            <p class="text-sm text-slate-500">Assigned jobs sorted by priority.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>Urgent
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>High
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>Normal
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>Routine
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <details
                            class="group rounded-2xl border border-slate-200 bg-slate-50/80 shadow-sm transition hover:shadow-md"
                            open>
                            <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <div class="flex flex-col gap-4 p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 h-3 w-3 rounded-full bg-red-500"></span>
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-red-600">Urgent</p>
                                                <h3 class="text-lg font-semibold text-slate-900">Acme Tower - Cooling Failure</h3>
                                                <p class="text-sm text-slate-600">Dana Ortiz - 15 Market St, Floor 8</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase text-slate-500">08:30 - 10:00</p>
                                            <p class="text-sm font-semibold text-slate-900">Est 90 min</p>
                                        </div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Service Type</p>
                                            <p class="text-sm font-medium text-slate-900">Emergency HVAC</p>
                                            <p class="text-xs text-slate-500">Problem: Alarm after restart, no cooling</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Equipment</p>
                                            <p class="text-sm font-medium text-slate-900">Trane RTU-3 (2018, 12 ton)</p>
                                            <p class="text-xs text-slate-500">Serial TR-88341</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 font-semibold text-red-700">Priority
                                            urgent</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Access
                                            code 4912</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Parking
                                            level B</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button class="min-h-[44px] rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white">
                                            Check In
                                        </button>
                                        <button class="min-h-[44px] rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">
                                            Check Out
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Navigate
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Call
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Tap to view full details</span>
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            Expand
                                        </span>
                                    </div>
                                </div>
                            </summary>
                            <div class="border-t border-slate-200 px-4 pb-5 pt-4 sm:px-5">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Customer Contact</h4>
                                        <p class="text-sm text-slate-600">Dana Ortiz - Facility Manager</p>
                                        <p class="text-sm text-slate-600">Phone: (555) 014-2098</p>
                                        <p class="text-sm text-slate-600">Email: dortiz@acmetower.com</p>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Access and Parking</h4>
                                        <p class="text-sm text-slate-600">Dock B, keypad 4912#, security desk on 2nd floor.</p>
                                        <p class="text-sm text-slate-600">Parking: Level B, spots 18-22.</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Problem Details</h4>
                                        <p class="text-sm text-slate-600">Cooling failure after power cycle. Unit enters alarm within 10 minutes. Tenant reports 82F on 8th floor.</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 1
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 2
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 3
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Equipment History and Specs</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Last service Aug 12 - condenser fan replaced.</li>
                                            <li>Warranty: parts through 2026.</li>
                                            <li>Specs: 12 ton, R-410A, VFD blower.</li>
                                        </ul>
                                        <h4 class="text-sm font-semibold text-slate-900">Suggested Parts</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Capacitor 45/5 uF - In stock</li>
                                            <li>Contact relay kit - Low stock</li>
                                            <li>Filter pack 20x25 - In van</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <details
                            class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <div class="flex flex-col gap-4 p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 h-3 w-3 rounded-full bg-orange-500"></span>
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-orange-600">High</p>
                                                <h3 class="text-lg font-semibold text-slate-900">Hartwell Clinic - Vaccine Cooler</h3>
                                                <p class="text-sm text-slate-600">Sam Patel - 422 Pine Ave, Suite 200</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase text-slate-500">10:30 - 12:00</p>
                                            <p class="text-sm font-semibold text-slate-900">Est 60 min</p>
                                        </div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Service Type</p>
                                            <p class="text-sm font-medium text-slate-900">Medical Refrigeration</p>
                                            <p class="text-xs text-slate-500">Problem: Temperature spikes overnight</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Equipment</p>
                                            <p class="text-sm font-medium text-slate-900">True T-49F (2021)</p>
                                            <p class="text-xs text-slate-500">Serial HF-22719</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 font-semibold text-orange-700">Priority
                                            high</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Access
                                            code 2230</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Dock
                                            entrance</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button class="min-h-[44px] rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white">
                                            Check In
                                        </button>
                                        <button class="min-h-[44px] rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">
                                            Check Out
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Navigate
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Call
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Tap to view full details</span>
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            Expand
                                        </span>
                                    </div>
                                </div>
                            </summary>
                            <div class="border-t border-slate-200 px-4 pb-5 pt-4 sm:px-5">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Customer Contact</h4>
                                        <p class="text-sm text-slate-600">Sam Patel - Lab Supervisor</p>
                                        <p class="text-sm text-slate-600">Phone: (555) 018-4471</p>
                                        <p class="text-sm text-slate-600">Email: spatel@hartwellclinic.org</p>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Access and Parking</h4>
                                        <p class="text-sm text-slate-600">Security check-in, code 2230# for lab doors.</p>
                                        <p class="text-sm text-slate-600">Parking: loading dock visitor spots.</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Problem Details</h4>
                                        <p class="text-sm text-slate-600">Vaccine cooler spikes to 46F around 02:00. Alarm logs attached.</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 1
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 2
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Log PDF
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Equipment History and Specs</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Last service Jul 05 - compressor check.</li>
                                            <li>Warranty: compressor through 2027.</li>
                                            <li>Specs: dual evaporator, 115V.</li>
                                        </ul>
                                        <h4 class="text-sm font-semibold text-slate-900">Suggested Parts</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Thermistor kit - In stock</li>
                                            <li>Door gasket 49F - In van</li>
                                            <li>Control board - Order if needed</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <details
                            class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <div class="flex flex-col gap-4 p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 h-3 w-3 rounded-full bg-blue-500"></span>
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-blue-600">Normal</p>
                                                <h3 class="text-lg font-semibold text-slate-900">Riverside Apartments - Low Pressure</h3>
                                                <p class="text-sm text-slate-600">Maya Chen - 930 River Rd, Building C</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase text-slate-500">13:00 - 14:30</p>
                                            <p class="text-sm font-semibold text-slate-900">Est 75 min</p>
                                        </div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Service Type</p>
                                            <p class="text-sm font-medium text-slate-900">Plumbing Diagnostics</p>
                                            <p class="text-xs text-slate-500">Problem: Low pressure on stairwell riser</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Equipment</p>
                                            <p class="text-sm font-medium text-slate-900">Grundfos Hydro Multi-E</p>
                                            <p class="text-xs text-slate-500">Serial GM-44120</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700">Priority
                                            normal</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Key
                                            pickup: lobby</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Parking
                                            alley gate</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button class="min-h-[44px] rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white">
                                            Check In
                                        </button>
                                        <button class="min-h-[44px] rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">
                                            Check Out
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Navigate
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Call
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Tap to view full details</span>
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            Expand
                                        </span>
                                    </div>
                                </div>
                            </summary>
                            <div class="border-t border-slate-200 px-4 pb-5 pt-4 sm:px-5">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Customer Contact</h4>
                                        <p class="text-sm text-slate-600">Maya Chen - Property Manager</p>
                                        <p class="text-sm text-slate-600">Phone: (555) 011-7724</p>
                                        <p class="text-sm text-slate-600">Email: mchen@riversideapts.com</p>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Access and Parking</h4>
                                        <p class="text-sm text-slate-600">Lobby key box, code 9011, take stairwell C.</p>
                                        <p class="text-sm text-slate-600">Parking: alley gate on 9th Ave.</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Problem Details</h4>
                                        <p class="text-sm text-slate-600">Pressure drop started last week on floors 4-6. Tenants report slow faucets.</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 1
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 2
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Diagram
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Equipment History and Specs</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Last service May 18 - seal kit installed.</li>
                                            <li>Warranty: pump parts through 2025.</li>
                                            <li>Specs: dual pump, 3 hp, 208V.</li>
                                        </ul>
                                        <h4 class="text-sm font-semibold text-slate-900">Suggested Parts</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Pressure sensor - In stock</li>
                                            <li>Seal kit - In van</li>
                                            <li>Relief valve - Low stock</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <details
                            class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <div class="flex flex-col gap-4 p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 h-3 w-3 rounded-full bg-green-500"></span>
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-green-600">Routine</p>
                                                <h3 class="text-lg font-semibold text-slate-900">Northwind Hotel - Quarterly PM</h3>
                                                <p class="text-sm text-slate-600">Luis Ramirez - 77 Harbor Dr</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase text-slate-500">15:30 - 17:00</p>
                                            <p class="text-sm font-semibold text-slate-900">Est 80 min</p>
                                        </div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Service Type</p>
                                            <p class="text-sm font-medium text-slate-900">Preventive Maintenance</p>
                                            <p class="text-xs text-slate-500">Problem: Seasonal inspection and filters</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase text-slate-500">Equipment</p>
                                            <p class="text-sm font-medium text-slate-900">Carrier Chiller CH-1200</p>
                                            <p class="text-xs text-slate-500">Serial CH-98802</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 font-semibold text-green-700">Priority
                                            routine</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Guest
                                            elevator access</span>
                                        <span class="inline-flex items-center rounded-full bg-slate-200/70 px-3 py-1 text-slate-600">Parking
                                            valet pass</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button class="min-h-[44px] rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white">
                                            Check In
                                        </button>
                                        <button class="min-h-[44px] rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">
                                            Check Out
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Navigate
                                        </button>
                                        <button
                                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                            Call
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>Tap to view full details</span>
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            Expand
                                        </span>
                                    </div>
                                </div>
                            </summary>
                            <div class="border-t border-slate-200 px-4 pb-5 pt-4 sm:px-5">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Customer Contact</h4>
                                        <p class="text-sm text-slate-600">Luis Ramirez - Chief Engineer</p>
                                        <p class="text-sm text-slate-600">Phone: (555) 013-6642</p>
                                        <p class="text-sm text-slate-600">Email: lramirez@northwindhotel.com</p>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Access and Parking</h4>
                                        <p class="text-sm text-slate-600">Use guest elevator, mechanical room on 5th.</p>
                                        <p class="text-sm text-slate-600">Parking: valet pass at front desk.</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Problem Details</h4>
                                        <p class="text-sm text-slate-600">Quarterly PM checklist, filter changes, vibration check.</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 1
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Photo 2
                                            </div>
                                            <div class="flex h-16 items-center justify-center rounded-lg bg-slate-200 text-[10px] text-slate-600">
                                                Checklist
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <h4 class="text-sm font-semibold text-slate-900">Equipment History and Specs</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Last service Jun 20 - oil analysis normal.</li>
                                            <li>Warranty: compressor through 2028.</li>
                                            <li>Specs: 120 ton, 480V, VFD pumps.</li>
                                        </ul>
                                        <h4 class="text-sm font-semibold text-slate-900">Suggested Parts</h4>
                                        <ul class="space-y-1 text-sm text-slate-600">
                                            <li>Filter pack 24x24 - In stock</li>
                                            <li>Vibration pads - In van</li>
                                            <li>Gasket kit - Order if needed</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                </section>

                <div class="xl:col-span-5 space-y-6">
                    <section
                        class="rounded-2xl bg-white shadow-lg shadow-slate-200/60 border border-slate-200/70 p-4 sm:p-6"
                        data-job-timer data-estimate-minutes="75" data-elapsed-seconds="4420">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-['Space_Grotesk'] text-slate-900">Time Tracking</h2>
                                <p class="text-sm text-slate-500">Current job: Acme Tower - HVAC</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Running
                            </span>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-6">
                            <div>
                                <p class="text-xs uppercase text-slate-500">Elapsed</p>
                                <p class="text-3xl font-mono font-semibold text-slate-900" data-timer-display>01:13:40</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase text-slate-500">Estimate</p>
                                <p class="text-sm font-semibold text-slate-900">75 min</p>
                                <p class="text-xs text-rose-600" data-timer-over>Over by 12 min</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-rose-500 transition-all" data-timer-progress style="width: 110%;"></div>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">Visual alert triggers at 75 min.</p>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <button class="min-h-[44px] rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                                Pause
                            </button>
                            <button class="min-h-[44px] rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700">
                                Add Notes
                            </button>
                            <button class="min-h-[44px] rounded-lg bg-slate-900 text-sm font-semibold text-white">
                                Stop
                            </button>
                        </div>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-500">Work time</p>
                                <p class="text-lg font-semibold text-slate-900">4h 12m</p>
                                <p class="text-xs text-emerald-600">+8% vs target</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-500">Travel time</p>
                                <p class="text-lg font-semibold text-slate-900">1h 02m</p>
                                <p class="text-xs text-slate-500">Traffic +14 min</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-500">Breaks</p>
                                <p class="text-lg font-semibold text-slate-900">22m</p>
                                <p class="text-xs text-slate-500">2 breaks logged</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs text-slate-500">Billable hours</p>
                                <p class="text-lg font-semibold text-slate-900">5h 40m</p>
                                <p class="text-xs text-emerald-600">On track</p>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl bg-white shadow-lg shadow-slate-200/60 border border-slate-200/70 p-4 sm:p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-['Space_Grotesk'] text-slate-900">Communication Center</h2>
                                <p class="text-sm text-slate-500">Dispatch and customer messages.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                3 unread
                            </span>
                        </div>
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3">
                            <p class="text-xs font-semibold text-rose-700">Urgent alert</p>
                            <p class="text-sm text-rose-800">Dispatch: Hartwell Clinic needs arrival before 11:00.</p>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Dispatch</span>
                                    <span>08:02</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-900">Gate code updated to 2230. Please confirm arrival.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Dana Ortiz</span>
                                    <span>07:45</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-900">Please call when you are 10 minutes out.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>Warehouse</span>
                                    <span>Yesterday</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-600">Seal kit restocked, bin A3.</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs text-slate-500">Quick replies</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button class="min-h-[36px] rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">
                                    On my way
                                </button>
                                <button class="min-h-[36px] rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">
                                    Running 10 min late
                                </button>
                                <button class="min-h-[36px] rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">
                                    Job complete, report incoming
                                </button>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <input
                                    class="min-h-[44px] flex-1 rounded-lg border-slate-200 text-sm focus:border-emerald-400 focus:ring-emerald-400"
                                    type="text" placeholder="Type a quick reply" />
                                <button class="min-h-[44px] rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white">
                                    Send
                                </button>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl bg-white shadow-lg shadow-slate-200/60 border border-slate-200/70 p-4 sm:p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-['Space_Grotesk'] text-slate-900">Parts and Inventory</h2>
                                <p class="text-sm text-slate-500">Commonly used parts for today.</p>
                            </div>
                            <button class="min-h-[40px] rounded-full bg-slate-900 px-3 text-xs font-semibold text-white">
                                Open Inventory
                            </button>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Capacitor 45/5 uF</p>
                                    <p class="text-xs text-slate-500">Bin A3 - Compatible with RTU-3</p>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">In stock (12)</span>
                                </div>
                                <button class="min-h-[44px] rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white">
                                    Reserve
                                </button>
                            </div>
                            <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Relay kit RY-220</p>
                                    <p class="text-xs text-slate-500">Bin B1 - HVAC control boards</p>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">Low stock (2)</span>
                                </div>
                                <button class="min-h-[44px] rounded-lg border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-700">
                                    Request
                                </button>
                            </div>
                            <div class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Filter pack 20x25</p>
                                    <p class="text-xs text-slate-500">Van stock - 4 packs remaining</p>
                                    <span class="mt-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">On hand</span>
                                </div>
                                <button class="min-h-[44px] rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white">
                                    Check Out
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <section
                class="rounded-2xl bg-white shadow-lg shadow-slate-200/60 border border-slate-200/70 p-4 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-['Space_Grotesk'] text-slate-900">Interactive Route Planning Map</h2>
                        <p class="text-sm text-slate-500">Drag jobs to reorder. Traffic data updated 2 min ago.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                            data-conflict-summary>No conflicts</span>
                        <button
                            class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                            Optimize Route
                        </button>
                    </div>
                </div>
                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase text-slate-500">Current location</p>
                                <p class="text-sm font-semibold text-slate-900">Warehouse North - 12th Ave and Oak</p>
                                <p class="text-xs text-slate-500">Next stop: Acme Tower, 1.2 mi</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="min-h-[44px] rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white">
                                    Navigate to Next
                                </button>
                                <button
                                    class="min-h-[44px] rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">
                                    Recenter
                                </button>
                            </div>
                        </div>
                        <div class="relative h-[360px] overflow-hidden rounded-2xl route-surface route-grid text-white">
                            <svg class="absolute inset-0 h-full w-full opacity-50" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M5 20 L35 20 L60 35 L90 35" stroke="rgba(148,163,184,0.4)" stroke-width="1.2" fill="none" />
                                <path d="M10 80 L40 60 L70 55 L95 30" stroke="rgba(148,163,184,0.3)" stroke-width="1.1" fill="none" />
                                <path d="M15 40 L30 70 L55 78 L85 70" stroke="rgba(148,163,184,0.25)" stroke-width="1" fill="none" />
                                <path d="M8 72 L24 58 L48 52 L68 40 L88 22" stroke="rgba(16,185,129,0.9)"
                                    stroke-width="1.6" stroke-dasharray="3 3" fill="none" />
                            </svg>

                            <div class="absolute left-[8%] top-[70%] flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-40"></span>
                                    <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
                                </span>
                                <span class="hidden sm:block text-xs text-emerald-100">You</span>
                            </div>

                            <div class="absolute left-[18%] top-[58%] flex items-center gap-2" data-map-marker="job-1">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-xs font-semibold text-white shadow-lg"
                                    data-map-sequence>1</span>
                                <span class="hidden sm:block text-xs">Acme Tower</span>
                            </div>
                            <div class="absolute left-[40%] top-[48%] flex items-center gap-2" data-map-marker="job-2">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-500 text-xs font-semibold text-white shadow-lg"
                                    data-map-sequence>2</span>
                                <span class="hidden sm:block text-xs">Hartwell Clinic</span>
                            </div>
                            <div class="absolute left-[62%] top-[38%] flex items-center gap-2" data-map-marker="job-3">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 text-xs font-semibold text-white shadow-lg"
                                    data-map-sequence>3</span>
                                <span class="hidden sm:block text-xs">Riverside Apts</span>
                            </div>
                            <div class="absolute left-[82%] top-[22%] flex items-center gap-2" data-map-marker="job-4">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 text-xs font-semibold text-white shadow-lg"
                                    data-map-sequence>4</span>
                                <span class="hidden sm:block text-xs">Northwind Hotel</span>
                            </div>

                            <div class="absolute bottom-4 left-4 rounded-xl bg-white/10 px-3 py-2 text-xs text-white">
                                Optimal route: 18.4 mi - 1h 12m with traffic
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                            Drag and drop to reorder jobs. Conflicts update instantly.
                        </div>
                        <ul class="space-y-3" data-route-list>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"
                                draggable="true" data-stop-id="job-1" data-window-start="08:30" data-window-end="10:00"
                                data-travel-minutes="14">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-sm font-semibold text-white"
                                    data-sequence>1</div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-900">Acme Tower</p>
                                            <p class="text-xs text-slate-500">08:30 - 10:00 | 14 min traffic</p>
                                        </div>
                                        <span class="text-xs font-semibold text-red-600">Urgent</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">Drag to reorder</span>
                                        <span
                                            class="ml-auto hidden rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700"
                                            data-conflict>Conflict</span>
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"
                                draggable="true" data-stop-id="job-2" data-window-start="10:30" data-window-end="12:00"
                                data-travel-minutes="18">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500 text-sm font-semibold text-white"
                                    data-sequence>2</div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-900">Hartwell Clinic</p>
                                            <p class="text-xs text-slate-500">10:30 - 12:00 | 18 min traffic</p>
                                        </div>
                                        <span class="text-xs font-semibold text-orange-600">High</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">Drag to reorder</span>
                                        <span
                                            class="ml-auto hidden rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700"
                                            data-conflict>Conflict</span>
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"
                                draggable="true" data-stop-id="job-3" data-window-start="13:00" data-window-end="14:30"
                                data-travel-minutes="12">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500 text-sm font-semibold text-white"
                                    data-sequence>3</div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-900">Riverside Apartments</p>
                                            <p class="text-xs text-slate-500">13:00 - 14:30 | 12 min traffic</p>
                                        </div>
                                        <span class="text-xs font-semibold text-blue-600">Normal</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">Drag to reorder</span>
                                        <span
                                            class="ml-auto hidden rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700"
                                            data-conflict>Conflict</span>
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"
                                draggable="true" data-stop-id="job-4" data-window-start="15:30" data-window-end="17:00"
                                data-travel-minutes="20">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-sm font-semibold text-white"
                                    data-sequence>4</div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-slate-900">Northwind Hotel</p>
                                            <p class="text-xs text-slate-500">15:30 - 17:00 | 20 min traffic</p>
                                        </div>
                                        <span class="text-xs font-semibold text-green-600">Routine</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">Drag to reorder</span>
                                        <span
                                            class="ml-auto hidden rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700"
                                            data-conflict>Conflict</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const timer = document.querySelector('[data-job-timer]');
            if (timer) {
                const display = timer.querySelector('[data-timer-display]');
                const progress = timer.querySelector('[data-timer-progress]');
                const over = timer.querySelector('[data-timer-over]');
                const estimateMinutes = Number(timer.dataset.estimateMinutes || 0);
                let elapsedSeconds = Number(timer.dataset.elapsedSeconds || 0);

                const updateTimer = () => {
                    const hours = Math.floor(elapsedSeconds / 3600);
                    const minutes = Math.floor((elapsedSeconds % 3600) / 60);
                    const seconds = elapsedSeconds % 60;
                    if (display) {
                        display.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    }

                    const elapsedMinutes = elapsedSeconds / 60;
                    if (estimateMinutes > 0 && progress) {
                        const percent = Math.min((elapsedMinutes / estimateMinutes) * 100, 160);
                        progress.style.width = `${percent}%`;
                        const overMinutes = Math.max(0, Math.round(elapsedMinutes - estimateMinutes));
                        if (overMinutes > 0) {
                            progress.classList.remove('bg-emerald-500');
                            progress.classList.add('bg-rose-500');
                            if (over) {
                                over.textContent = `Over by ${overMinutes} min`;
                                over.classList.remove('text-emerald-600');
                                over.classList.add('text-rose-600');
                            }
                        } else if (over) {
                            progress.classList.remove('bg-rose-500');
                            progress.classList.add('bg-emerald-500');
                            over.textContent = 'On track';
                            over.classList.remove('text-rose-600');
                            over.classList.add('text-emerald-600');
                        }
                    }
                };

                updateTimer();
                setInterval(() => {
                    elapsedSeconds += 1;
                    updateTimer();
                }, 1000);
            }

            const routeList = document.querySelector('[data-route-list]');
            if (routeList) {
                let draggingItem = null;

                const parseTime = (value) => {
                    if (!value) {
                        return null;
                    }
                    const parts = value.split(':');
                    if (parts.length !== 2) {
                        return null;
                    }
                    const hours = Number(parts[0]);
                    const minutes = Number(parts[1]);
                    if (Number.isNaN(hours) || Number.isNaN(minutes)) {
                        return null;
                    }
                    return hours * 60 + minutes;
                };

                const listItems = () => Array.from(routeList.querySelectorAll('[data-stop-id]'));

                const updateConflicts = () => {
                    let lastEnd = null;
                    listItems().forEach((item) => {
                        const end = parseTime(item.dataset.windowEnd);
                        const travelMinutes = Number(item.dataset.travelMinutes || 0);
                        const conflict = lastEnd !== null && end !== null && lastEnd + travelMinutes > end;
                        const badge = item.querySelector('[data-conflict]');
                        if (badge) {
                            badge.classList.toggle('hidden', !conflict);
                        }
                        item.classList.toggle('border-rose-300', conflict);
                        item.classList.toggle('bg-rose-50', conflict);
                        if (end !== null) {
                            lastEnd = end;
                        }
                    });

                    const conflictCount = listItems().filter((item) => {
                        const badge = item.querySelector('[data-conflict]');
                        return badge && !badge.classList.contains('hidden');
                    }).length;
                    const summary = document.querySelector('[data-conflict-summary]');
                    if (summary) {
                        summary.textContent = conflictCount
                            ? `${conflictCount} conflict${conflictCount === 1 ? '' : 's'} detected`
                            : 'No conflicts';
                        summary.classList.toggle('text-rose-700', conflictCount > 0);
                        summary.classList.toggle('bg-rose-100', conflictCount > 0);
                        summary.classList.toggle('text-emerald-700', conflictCount === 0);
                        summary.classList.toggle('bg-emerald-100', conflictCount === 0);
                    }
                };

                const updateSequence = () => {
                    listItems().forEach((item, index) => {
                        const badge = item.querySelector('[data-sequence]');
                        if (badge) {
                            badge.textContent = index + 1;
                        }
                        const mapMarker = document.querySelector(`[data-map-marker="${item.dataset.stopId}"]`);
                        if (mapMarker) {
                            const mapBadge = mapMarker.querySelector('[data-map-sequence]');
                            if (mapBadge) {
                                mapBadge.textContent = index + 1;
                            }
                        }
                    });
                    updateConflicts();
                };

                routeList.addEventListener('dragstart', (event) => {
                    const target = event.target.closest('[data-stop-id]');
                    if (!target) {
                        return;
                    }
                    draggingItem = target;
                    draggingItem.classList.add('opacity-70', 'ring-2', 'ring-emerald-300');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', '');
                });

                routeList.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    const target = event.target.closest('[data-stop-id]');
                    if (!target || target === draggingItem) {
                        return;
                    }
                    const rect = target.getBoundingClientRect();
                    const offset = event.clientY - rect.top;
                    const shouldInsertAfter = offset > rect.height / 2;
                    routeList.insertBefore(draggingItem, shouldInsertAfter ? target.nextSibling : target);
                });

                routeList.addEventListener('dragend', () => {
                    if (!draggingItem) {
                        return;
                    }
                    draggingItem.classList.remove('opacity-70', 'ring-2', 'ring-emerald-300');
                    draggingItem = null;
                    updateSequence();
                });

                updateSequence();
            }
        })();
    </script>
</x-app-layout>
