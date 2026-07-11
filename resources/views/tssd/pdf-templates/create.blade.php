<x-po_dashboard_layout>

    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <h1 class="text-3xl font-bold text-gray-900">
                    Upload PDF Print Layout
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Upload an official blank PDF and assign it to a system report.
                </p>

            </div>

            <a
                href="{{ route('tssd.pdf-templates.index') }}"
                class="rounded-xl border border-gray-300 bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50"
            >
                Back to Templates
            </a>

        </div>

        @if($errors->any())

            <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">

                <p class="font-semibold">
                    Please correct the following:
                </p>

                <ul class="mt-3 list-disc space-y-1 pl-5">

                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        <form
            action="{{ route('tssd.pdf-templates.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow"
        >

            @csrf

            <div class="bg-red-900 px-7 py-5">

                <h2 class="text-xl font-semibold text-white">
                    PDF Template Information
                </h2>

                <p class="mt-1 text-sm text-red-100">
                    The uploaded PDF will be preserved as the fixed report background.
                </p>

            </div>

            <div class="space-y-6 p-7">

                <div>

                    <label
                        for="template_name"
                        class="mb-2 block font-semibold text-gray-700"
                    >
                        Template Name
                    </label>

                    <input
                        type="text"
                        id="template_name"
                        name="template_name"
                        value="{{ old('template_name') }}"
                        required
                        maxlength="255"
                        placeholder="Example: Official Call-Off Summary 2026"
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                    @error('template_name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label
                        for="report_type"
                        class="mb-2 block font-semibold text-gray-700"
                    >
                        Which table or report will use this PDF?
                    </label>

                    <select
                        id="report_type"
                        name="report_type"
                        required
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >

                        <option value="">
                            Select report or table
                        </option>

                        @foreach($reportTypeOptions as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(old('report_type') === $value)
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                    @error('report_type')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label
                        for="pdf_file"
                        class="mb-2 block font-semibold text-gray-700"
                    >
                        Blank PDF Layout
                    </label>

                    <input
                        type="file"
                        id="pdf_file"
                        name="pdf_file"
                        accept="application/pdf,.pdf"
                        required
                        class="w-full rounded-xl border border-gray-300 bg-white p-3"
                    >

                    <p class="mt-2 text-sm text-gray-500">
                        PDF only, maximum 20 MB. The file should contain the fixed header, borders, labels, and empty fields.
                    </p>

                    @error('pdf_file')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label
                        for="description"
                        class="mb-2 block font-semibold text-gray-700"
                    >
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        maxlength="5000"
                        placeholder="Optional notes about this PDF version."
                        class="w-full rounded-xl border-gray-300 focus:border-red-900 focus:ring-red-900"
                    >{{ old('description') }}</textarea>

                </div>

                <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', true))
                        class="mt-1 rounded border-gray-300 text-red-900 focus:ring-red-900"
                    >

                    <span>

                        <span class="block font-medium text-gray-800">
                            Make this the active template
                        </span>

                        <span class="mt-1 block text-sm text-gray-500">
                            Any currently active template assigned to the same report will automatically be deactivated.
                        </span>

                    </span>

                </label>

                <div class="flex justify-end">

                    <button
                        type="submit"
                        class="rounded-xl bg-red-900 px-7 py-3 font-semibold text-white hover:bg-red-800"
                    >
                        Upload PDF Template
                    </button>

                </div>

            </div>

        </form>

    </div>

</x-po_dashboard_layout>