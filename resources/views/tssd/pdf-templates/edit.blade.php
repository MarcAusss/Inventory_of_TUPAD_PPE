<x-po_dashboard_layout>

    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Edit PDF Template
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    {{ $pdfTemplate->template_name }} — Version {{ $pdfTemplate->version }}
                </p>

            </div>

            <a
                href="{{ route('tssd.pdf-templates.show', $pdfTemplate) }}"
                class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700"
            >
                Back
            </a>

        </div>

        @if($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">

                <ul class="list-disc space-y-1 pl-5">

                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        <form
            action="{{ route('tssd.pdf-templates.update', $pdfTemplate) }}"
            method="POST"
            enctype="multipart/form-data"
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow"
        >

            @csrf
            @method('PUT')

            <div class="bg-red-900 px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Template Information
                </h2>

            </div>

            <div class="space-y-6 p-7">

                <div>

                    <label class="mb-2 block font-semibold text-gray-700">
                        Template Name
                    </label>

                    <input
                        type="text"
                        name="template_name"
                        value="{{ old('template_name', $pdfTemplate->template_name) }}"
                        required
                        class="w-full rounded-xl border-gray-300"
                    >

                </div>

                <div>

                    <label class="mb-2 block font-semibold text-gray-700">
                        Assigned Report
                    </label>

                    <select
                        name="report_type"
                        required
                        class="w-full rounded-xl border-gray-300"
                    >

                        @foreach($reportTypeOptions as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'report_type',
                                        $pdfTemplate->report_type
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                    <p class="mt-2 text-sm text-yellow-700">
                        Changing the assigned report may affect its active-template version history.
                    </p>

                </div>

                <div>

                    <label class="mb-2 block font-semibold text-gray-700">
                        Current PDF
                    </label>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                        <p class="font-medium text-gray-900">
                            {{ $pdfTemplate->original_filename }}
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $pdfTemplate->formattedFileSize() }}
                            ·
                            {{ $pdfTemplate->page_count ?? 'Unknown' }} page(s)
                        </p>

                        <a
                            href="{{ route('tssd.pdf-templates.preview', $pdfTemplate) }}"
                            target="_blank"
                            class="mt-3 inline-flex rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white"
                        >
                            View Current PDF
                        </a>

                    </div>

                </div>

                <div>

                    <label class="mb-2 block font-semibold text-gray-700">
                        Replace PDF
                    </label>

                    <input
                        type="file"
                        name="pdf_file"
                        accept="application/pdf,.pdf"
                        class="w-full rounded-xl border border-gray-300 p-3"
                    >

                    <p class="mt-2 text-sm text-gray-500">
                        Leave blank to keep the current PDF. Replacing it preserves this version record.
                    </p>

                </div>

                <div>

                    <label class="mb-2 block font-semibold text-gray-700">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="w-full rounded-xl border-gray-300"
                    >{{ old('description', $pdfTemplate->description) }}</textarea>

                </div>

                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(
                            old(
                                'is_active',
                                $pdfTemplate->is_active
                            )
                        )
                        class="rounded border-gray-300 text-red-900"
                    >

                    <span class="font-medium text-gray-700">
                        Active template for this report
                    </span>

                </label>

                <div class="flex justify-end">

                    <button
                        type="submit"
                        class="rounded-xl bg-red-900 px-7 py-3 font-semibold text-white"
                    >
                        Update PDF Template
                    </button>

                </div>

            </div>

        </form>

    </div>

</x-po_dashboard_layout>