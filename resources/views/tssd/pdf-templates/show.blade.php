<x-po_dashboard_layout>

    <div class="space-y-6">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $pdfTemplate->template_name }}
                    </h1>

                    @if($pdfTemplate->is_active)

                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                            Active
                        </span>

                    @else

                        <span class="rounded-full bg-gray-200 px-3 py-1 text-sm font-semibold text-gray-700">
                            Inactive
                        </span>

                    @endif

                </div>

                <p class="mt-2 text-sm text-gray-600">
                    {{ $pdfTemplate->reportTypeLabel() }}
                    ·
                    Version {{ $pdfTemplate->version }}
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a
                    href="{{ route('tssd.pdf-templates.index') }}"
                    class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700"
                >
                    Back
                </a>

                <a
                    href="{{ route('tssd.pdf-templates.preview', $pdfTemplate) }}"
                    target="_blank"
                    class="rounded-xl bg-purple-600 px-5 py-3 font-semibold text-white"
                >
                    View PDF
                </a>

                <a
                    href="{{ route('tssd.pdf-templates.download', $pdfTemplate) }}"
                    class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white"
                >
                    Download
                </a>

                <a
                    href="{{ route('tssd.pdf-templates.edit', $pdfTemplate) }}"
                    class="rounded-xl bg-yellow-500 px-5 py-3 font-semibold text-white"
                >
                    Edit / Replace
                </a>

            </div>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm text-gray-500">
                    Assigned Report
                </p>

                <p class="mt-2 font-semibold text-gray-900">
                    {{ $pdfTemplate->reportTypeLabel() }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm text-gray-500">
                    Version
                </p>

                <p class="mt-2 font-semibold text-gray-900">
                    {{ $pdfTemplate->versionLabel() }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm text-gray-500">
                    Pages
                </p>

                <p class="mt-2 font-semibold text-gray-900">
                    {{ $pdfTemplate->page_count ?? 'Unknown' }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm text-gray-500">
                    Original Filename
                </p>

                <p class="mt-2 break-all font-semibold text-gray-900">
                    {{ $pdfTemplate->original_filename }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm text-gray-500">
                    File Size
                </p>

                <p class="mt-2 font-semibold text-gray-900">
                    {{ $pdfTemplate->formattedFileSize() }}
                </p>

            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

                <p class="text-sm text-gray-500">
                    File Hash
                </p>

                <p class="mt-2 break-all font-mono text-xs text-gray-700">
                    {{ $pdfTemplate->file_hash ?: 'Not available' }}
                </p>

            </div>

        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow">

            <h2 class="text-xl font-semibold text-gray-900">
                Template Details
            </h2>

            <dl class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>

                    <dt class="text-sm font-medium text-gray-500">
                        Uploaded By
                    </dt>

                    <dd class="mt-1 font-semibold text-gray-900">
                        {{ $pdfTemplate->uploader?->name ?? 'Unknown' }}
                    </dd>

                </div>

                <div>

                    <dt class="text-sm font-medium text-gray-500">
                        Uploaded At
                    </dt>

                    <dd class="mt-1 font-semibold text-gray-900">
                        {{ $pdfTemplate->created_at?->format('F d, Y h:i A') }}
                    </dd>

                </div>

                <div>

                    <dt class="text-sm font-medium text-gray-500">
                        Last Updated By
                    </dt>

                    <dd class="mt-1 font-semibold text-gray-900">
                        {{ $pdfTemplate->updater?->name ?? 'Unknown' }}
                    </dd>

                </div>

                <div>

                    <dt class="text-sm font-medium text-gray-500">
                        Last Updated At
                    </dt>

                    <dd class="mt-1 font-semibold text-gray-900">
                        {{ $pdfTemplate->updated_at?->format('F d, Y h:i A') }}
                    </dd>

                </div>

                <div class="md:col-span-2">

                    <dt class="text-sm font-medium text-gray-500">
                        Description
                    </dt>

                    <dd class="mt-1 whitespace-pre-line text-gray-900">
                        {{ $pdfTemplate->description ?: 'No description provided.' }}
                    </dd>

                </div>

            </dl>

        </div>

        <div class="flex flex-wrap justify-end gap-3">

            @if($pdfTemplate->is_active)

                <form
                    action="{{ route('tssd.pdf-templates.deactivate', $pdfTemplate) }}"
                    method="POST"
                >

                    @csrf
                    @method('PATCH')

                    <button
                        class="rounded-xl bg-gray-600 px-6 py-3 font-semibold text-white"
                    >
                        Deactivate Template
                    </button>

                </form>

            @else

                <form
                    action="{{ route('tssd.pdf-templates.activate', $pdfTemplate) }}"
                    method="POST"
                >

                    @csrf
                    @method('PATCH')

                    <button
                        class="rounded-xl bg-green-600 px-6 py-3 font-semibold text-white"
                    >
                        Set as Active
                    </button>

                </form>

                <form
                    action="{{ route('tssd.pdf-templates.destroy', $pdfTemplate) }}"
                    method="POST"
                    onsubmit="return confirm('Delete this inactive PDF template permanently?');"
                >

                    @csrf
                    @method('DELETE')

                    <button
                        class="rounded-xl bg-red-600 px-6 py-3 font-semibold text-white"
                    >
                        Delete Template
                    </button>

                </form>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>