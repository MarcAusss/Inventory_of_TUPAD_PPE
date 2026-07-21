<x-po_dashboard_layout>

    <div class="space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    PDF Print Templates
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Upload multiple official PDFs and assign each layout to a system report.
                </p>

            </div>

            <a
                href="{{ route('tssd.pdf-templates.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-[#339DCB] px-6 py-3 font-semibold text-white hover:bg-red-800"
            >
                Upload PDF Template
            </a>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        @if(session('error'))

            <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
                {{ session('error') }}
            </div>

        @endif

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-blue-900">

            Only one PDF can be active for each report type. Older versions remain saved and may be activated again later.

        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

            <form
                action="{{ route('tssd.pdf-templates.index') }}"
                method="GET"
                class="grid grid-cols-1 gap-4 lg:grid-cols-5"
            >

                <div>

                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Assigned Report
                    </label>

                    <select
                        name="report_type"
                        class="w-full rounded-xl border-gray-300"
                    >

                        <option value="">
                            All reports
                        </option>

                        @foreach($reportTypeOptions as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected($reportType === $value)
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div>

                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Status
                    </label>

                    <select
                        name="status"
                        class="w-full rounded-xl border-gray-300"
                    >

                        <option value="">
                            All statuses
                        </option>

                        <option
                            value="active"
                            @selected($status === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected($status === 'inactive')
                        >
                            Inactive
                        </option>

                    </select>

                </div>

                <div class="lg:col-span-2">

                    <label class="mb-2 block text-sm font-semibold text-gray-700">
                        Search
                    </label>

                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Template name, filename, description..."
                        class="w-full rounded-xl border-gray-300"
                    >

                </div>

                <div class="flex items-end gap-2">

                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-[#339DCB] px-5 py-3 font-semibold text-white"
                    >
                        Apply
                    </button>

                    @if($reportType || $status || $search)

                        <a
                            href="{{ route('tssd.pdf-templates.index') }}"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700"
                        >
                            Reset
                        </a>

                    @endif

                </div>

            </form>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-[#339DCB] px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Uploaded PDF Layouts
                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-100">

                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-700">

                            <th class="px-5 py-4 text-left">No.</th>
                            <th class="px-5 py-4 text-left">Template</th>
                            <th class="px-5 py-4 text-left">Assigned Report</th>
                            <th class="px-5 py-4 text-center">Version</th>
                            <th class="px-5 py-4 text-left">PDF File</th>
                            <th class="px-5 py-4 text-center">Pages</th>
                            <th class="px-5 py-4 text-center">Status</th>
                            <th class="px-5 py-4 text-left">Uploaded By</th>
                            <th class="px-5 py-4 text-center">Actions</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($templates as $template)

                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $templates->firstItem() + $loop->index }}
                                </td>

                                <td class="min-w-56 px-5 py-4">

                                    <p class="font-semibold text-gray-900">
                                        {{ $template->template_name }}
                                    </p>

                                    @if($template->description)

                                        <p class="mt-1 line-clamp-2 text-xs text-gray-500">
                                            {{ $template->description }}
                                        </p>

                                    @endif

                                </td>

                                <td class="min-w-56 px-5 py-4 text-sm text-gray-700">
                                    {{ $template->reportTypeLabel() }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-gray-900">
                                    {{ $template->version }}
                                </td>

                                <td class="min-w-56 px-5 py-4">

                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $template->original_filename }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $template->formattedFileSize() }}
                                    </p>

                                </td>

                                <td class="px-5 py-4 text-center text-gray-700">
                                    {{ $template->page_count ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-center">

                                    @if($template->is_active)

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                            Active
                                        </span>

                                    @else

                                        <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">
                                            Inactive
                                        </span>

                                    @endif

                                </td>

                                <td class="min-w-40 px-5 py-4 text-sm text-gray-700">

                                    <p>
                                        {{ $template->uploader?->name ?? 'Unknown' }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $template->created_at?->format('M d, Y h:i A') }}
                                    </p>

                                </td>

                                <td class="min-w-72 px-5 py-4">

                                    <div class="flex flex-wrap justify-center gap-2">

                                        <a
                                            href="{{ route('tssd.pdf-templates.show', $template) }}"
                                            class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white"
                                        >
                                            Details
                                        </a>

                                        <a
                                            href="{{ route('tssd.pdf-templates.preview', $template) }}"
                                            target="_blank"
                                            class="rounded-lg bg-purple-600 px-3 py-2 text-xs font-semibold text-white"
                                        >
                                            View PDF
                                        </a>

                                        <a
                                            href="{{ route('tssd.pdf-templates.edit', $template) }}"
                                            class="rounded-lg bg-yellow-500 px-3 py-2 text-xs font-semibold text-white"
                                        >
                                            Edit
                                        </a>

                                        @if($template->is_active)

                                            <form
                                                action="{{ route('tssd.pdf-templates.deactivate', $template) }}"
                                                method="POST"
                                            >

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    class="rounded-lg bg-gray-600 px-3 py-2 text-xs font-semibold text-white"
                                                >
                                                    Deactivate
                                                </button>

                                            </form>

                                        @else

                                            <form
                                                action="{{ route('tssd.pdf-templates.activate', $template) }}"
                                                method="POST"
                                            >

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white"
                                                >
                                                    Activate
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="px-6 py-14 text-center text-gray-500"
                                >
                                    No PDF templates have been uploaded.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($templates->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $templates->links() }}
                </div>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>