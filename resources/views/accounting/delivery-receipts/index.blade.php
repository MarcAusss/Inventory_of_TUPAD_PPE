<x-po_dashboard_layout title="Delivery Receipt Summary">
    <div
        x-data="{
            modalOpen: false,
            selectedReceipt: null,

            openDetails(event) {
                try {
                    const encodedPayload =
                        event.currentTarget.dataset.payload;

                    if (!encodedPayload) {
                        return;
                    }

                    const binary = atob(encodedPayload);
                    const bytes = Uint8Array.from(
                        binary,
                        character => character.charCodeAt(0)
                    );

                    const payload = JSON.parse(
                        new TextDecoder().decode(bytes)
                    );

                    this.selectedReceipt = payload;
                    this.modalOpen = true;

                    document.body.classList.add('overflow-hidden');
                } catch (error) {
                    console.error(
                        'Unable to open Delivery Receipt details:',
                        error
                    );
                }
            },

            closeDetails() {
                this.modalOpen = false;
                this.selectedReceipt = null;

                document.body.classList.remove('overflow-hidden');
            },

            formatNumber(value) {
                return Number(value ?? 0).toLocaleString();
            }
        }"
        class="mx-auto max-w-[1900px] space-y-6"
    >
        <x-accounting-summary-header
            title="Delivery Receipt Summary"
            description="Read-only monitoring of Delivery Receipts submitted by provincial offices."
        />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-3xl border border-sky-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-500"
                >
                    Total Receipts
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-900">
                    {{ number_format($totalReceipts) }}
                </p>
            </div>

            <div
                class="rounded-3xl border border-emerald-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-500"
                >
                    Received
                </p>

                <p class="mt-2 text-3xl font-bold text-emerald-700">
                    {{ number_format($receivedCount) }}
                </p>
            </div>

            <div
                class="rounded-3xl border border-red-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-500"
                >
                    Pending
                </p>

                <p class="mt-2 text-3xl font-bold text-red-700">
                    {{ number_format($pendingCount) }}
                </p>
            </div>

            <div
                class="rounded-3xl border border-sky-200
                       bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-wider
                           text-slate-500"
                >
                    Total PPE Received
                </p>

                <p class="mt-2 text-3xl font-bold text-sky-700">
                    {{ number_format($totalReceivedPpe) }}
                </p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-3xl border
                   border-slate-200 bg-white shadow-sm"
        >
            <form
                method="GET"
                action="{{ route('accounting.delivery-receipts.index') }}"
                class="grid gap-3 border-b border-slate-200
                       p-5 md:grid-cols-4"
            >
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search DR, Call-Off, PO, NEFA, supplier..."
                    class="rounded-xl border-slate-300
                           focus:border-sky-500 focus:ring-sky-500"
                >

                <select
                    name="province_id"
                    class="rounded-xl border-slate-300
                           focus:border-sky-500 focus:ring-sky-500"
                >
                    <option value="">
                        All provinces
                    </option>

                    @foreach ($provinces as $province)
                        <option
                            value="{{ $province->id }}"
                            @selected(
                                (int) $provinceId
                                === (int) $province->id
                            )
                        >
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>

                <select
                    name="status"
                    class="rounded-xl border-slate-300
                           focus:border-sky-500 focus:ring-sky-500"
                >
                    <option value="">
                        All statuses
                    </option>

                    @foreach (['Pending', 'Received'] as $option)
                        <option
                            value="{{ $option }}"
                            @selected($status === $option)
                        >
                            {{ $option }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-sky-600
                               px-4 py-2.5 text-sm font-bold
                               text-white transition hover:bg-sky-700"
                    >
                        Apply
                    </button>

                    <a
                        href="{{
                            route(
                                'accounting.delivery-receipts.index'
                            )
                        }}"
                        class="rounded-xl border border-sky-200
                               px-4 py-2.5 text-sm font-bold
                               text-sky-700 transition hover:bg-sky-50"
                    >
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table
                    class="w-full min-w-[1850px]
                           divide-y divide-slate-200"
                >
                    <thead
                        class="bg-sky-700 text-xs font-bold
                               uppercase tracking-wide text-white"
                    >
                        <tr>
                            <th class="px-5 py-4 text-left">
                                DR Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Delivery Date
                            </th>

                            <th class="px-5 py-4 text-left">
                                Call-Off
                            </th>

                            <th class="px-5 py-4 text-left">
                                PO Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                NEFA Number
                            </th>

                            <th class="px-5 py-4 text-left">
                                Province
                            </th>

                            <th class="px-5 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-5 py-4 text-left">
                                Received By
                            </th>

                            <th class="px-5 py-4 text-center">
                                Total PPE
                            </th>

                            <th class="px-5 py-4 text-center">
                                Documents
                            </th>

                            <th class="px-5 py-4 text-center">
                                Status
                            </th>

                            <th class="px-5 py-4 text-center">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($receipts as $receipt)
                            @php
                                $distribution =
                                    $receipt->provinceDistribution;

                                $batch =
                                    $distribution?->distributionBatch;

                                $purchaseOrder =
                                    $batch?->purchaseOrder
                                    ?? $receipt->purchaseOrder;

                                $province =
                                    $distribution?->province
                                    ?? $receipt->province;

                                $callOff = $batch?->callOff;

                                $receiverName =
                                    $receipt->physical_receiver_name
                                    ?: $receipt->received_by
                                    ?: $receipt->receivedByUser?->name
                                    ?: '—';

                                $totalReceived = (int) $receipt
                                    ->items
                                    ->sum('received_quantity');

                                $documentRows = $receipt
                                    ->documents
                                    ->map(
                                        fn ($document) => [
                                            'name' =>
                                                $document->original_name
                                                ?: 'Document',

                                            'url' =>
                                                $document->file_path
                                                ? route('documents.receipt-documents', $document)
                                                : null,
                                        ]
                                    )
                                    ->values()
                                    ->all();

                                if (
                                    $receipt->document
                                    && count($documentRows) === 0
                                ) {
                                    $documentRows[] = [
                                        'name' => basename(
                                            $receipt->document
                                        ),
                                        'url' => route(
                                            'documents.receipt-legacy',
                                            $receipt
                                        ),
                                    ];
                                }

                                $itemRows = $receipt
                                    ->items
                                    ->map(
                                        function ($receiptItem) {
                                            return [
                                                'item' =>
                                                    $receiptItem
                                                        ->item
                                                        ?->item_name
                                                    ?? '—',

                                                'variant' =>
                                                    $receiptItem
                                                        ->item
                                                        ?->label
                                                    ?: 'Standard',

                                                'unit' =>
                                                    $receiptItem
                                                        ->item
                                                        ?->unit_of_measurement
                                                    ?? '—',

                                                'assigned_quantity' =>
                                                    (int) (
                                                        $receiptItem
                                                            ->assigned_quantity
                                                        ?? 0
                                                    ),

                                                'received_quantity' =>
                                                    (int) (
                                                        $receiptItem
                                                            ->received_quantity
                                                        ?? 0
                                                    ),

                                                'shortage_quantity' =>
                                                    max(
                                                        0,
                                                        (int) (
                                                            $receiptItem
                                                                ->assigned_quantity
                                                            ?? 0
                                                        )
                                                        - (int) (
                                                            $receiptItem
                                                                ->received_quantity
                                                            ?? 0
                                                        )
                                                    ),
                                            ];
                                        }
                                    )
                                    ->values()
                                    ->all();

                                $modalPayload = [
                                    'dr_number' =>
                                        $receipt->dr_number
                                        ?? '—',

                                    'delivery_date' =>
                                        $receipt->delivery_date
                                            ?->format('F d, Y')
                                        ?? '—',

                                    'call_off_number' =>
                                        $callOff?->call_off_number
                                        ?? 'Pending',

                                    'po_number' =>
                                        $purchaseOrder?->po_number
                                        ?? '—',

                                    'nefa_number' =>
                                        $purchaseOrder?->nefa_number
                                        ?? '—',

                                    'province' =>
                                        $province?->name
                                        ?? '—',

                                    'supplier' =>
                                        $purchaseOrder
                                            ?->supplier
                                            ?->supplier_name
                                        ?? '—',

                                    'received_by' =>
                                        $receiverName,

                                    'status' =>
                                        $receipt->status
                                        ?? 'Pending',

                                    'remarks' =>
                                        $receipt->remarks
                                        ?: 'No remarks provided.',

                                    'submitted_at' =>
                                        $receipt->submitted_at
                                            ?->format(
                                                'F d, Y h:i A'
                                            )
                                        ?? '—',

                                    'total_received' =>
                                        $totalReceived,

                                    'items' =>
                                        $itemRows,

                                    'documents' =>
                                        $documentRows,
                                ];

                                $encodedPayload = base64_encode(
                                    json_encode(
                                        $modalPayload,
                                        JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                    )
                                );
                            @endphp

                            <tr class="hover:bg-sky-50/60">
                                <td
                                    class="px-5 py-4 font-bold
                                           text-slate-900"
                                >
                                    {{ $receipt->dr_number ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{
                                        $receipt->delivery_date
                                            ?->format('M d, Y')
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-semibold
                                           text-sky-700"
                                >
                                    {{
                                        $callOff?->call_off_number
                                        ?? 'Pending'
                                    }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{
                                        $purchaseOrder?->po_number
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{
                                        $purchaseOrder?->nefa_number
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $province?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{
                                        $purchaseOrder
                                            ?->supplier
                                            ?->supplier_name
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{ $receiverName }}
                                </td>

                                <td
                                    class="px-5 py-4 text-center
                                           text-lg font-bold
                                           text-sky-700"
                                >
                                    {{
                                        number_format(
                                            $totalReceived
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-10
                                               items-center justify-center
                                               rounded-full bg-sky-100
                                               px-3 py-1 text-xs font-bold
                                               text-sky-800 ring-1
                                               ring-sky-200"
                                    >
                                        {{ count($documentRows) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        @class([
                                            'inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1',
                                            'bg-red-100 text-red-700 ring-red-200' =>
                                                $receipt->status === 'Pending',
                                            'bg-emerald-100 text-emerald-700 ring-emerald-200' =>
                                                $receipt->status !== 'Pending',
                                        ])
                                    >
                                        {{
                                            $receipt->status
                                            ?? 'Pending'
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <button
                                        type="button"
                                        data-payload="{{
                                            $encodedPayload
                                        }}"
                                        x-on:click="openDetails($event)"
                                        class="inline-flex items-center
                                               rounded-xl bg-sky-700
                                               px-4 py-2 text-xs
                                               font-bold text-white
                                               transition
                                               hover:bg-sky-900"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="12"
                                    class="px-6 py-14 text-center
                                           text-sm text-slate-500"
                                >
                                    No Delivery Receipt records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 p-5">
                {{ $receipts->links() }}
            </div>
        </section>

        <div
            x-cloak
            x-show="modalOpen"
            x-on:keydown.escape.window="closeDetails()"
            class="fixed inset-0 z-[100] overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div
                x-show="modalOpen"
                x-transition.opacity
                x-on:click="closeDetails()"
                class="fixed inset-0 bg-slate-950/60
                       backdrop-blur-sm"
            ></div>

            <div
                class="relative flex min-h-full items-center
                       justify-center p-4 sm:p-6"
            >
                <div
                    x-show="modalOpen"
                    x-transition
                    x-on:click.stop
                    class="relative w-full max-w-7xl
                           overflow-hidden rounded-3xl bg-white
                           shadow-2xl"
                >
                    <div
                        class="bg-gradient-to-r from-sky-900
                               via-sky-700 to-sky-500
                               px-6 py-6 text-white sm:px-8"
                    >
                        <div
                            class="flex items-start
                                   justify-between gap-5"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase
                                           tracking-[0.18em]
                                           text-sky-100"
                                >
                                    Delivery Receipt Details
                                </p>

                                <h2
                                    class="mt-2 text-2xl font-bold"
                                    x-text="
                                        selectedReceipt
                                            ?.dr_number
                                        ?? '—'
                                    "
                                ></h2>

                                <p class="mt-2 text-sm text-sky-100">
                                    <span
                                        x-text="
                                            selectedReceipt
                                                ?.province
                                            ?? '—'
                                        "
                                    ></span>

                                    <span class="mx-2">•</span>

                                    <span
                                        x-text="
                                            selectedReceipt
                                                ?.delivery_date
                                            ?? '—'
                                        "
                                    ></span>
                                </p>
                            </div>

                            <button
                                type="button"
                                x-on:click="closeDetails()"
                                class="inline-flex h-10 w-10
                                       items-center justify-center
                                       rounded-xl bg-white/15
                                       text-2xl text-white
                                       transition hover:bg-white/25"
                            >
                                &times;
                            </button>
                        </div>
                    </div>

                    <div
                        class="grid gap-4 border-b border-slate-200
                               bg-sky-50 px-6 py-5
                               md:grid-cols-2 xl:grid-cols-4
                               sm:px-8"
                    >
                        <div
                            class="rounded-2xl border border-sky-200
                                   bg-white p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-500"
                            >
                                Call-Off
                            </p>

                            <p
                                class="mt-1 font-bold text-slate-900"
                                x-text="
                                    selectedReceipt
                                        ?.call_off_number
                                    ?? '—'
                                "
                            ></p>
                        </div>

                        <div
                            class="rounded-2xl border border-sky-200
                                   bg-white p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-500"
                            >
                                Purchase Order
                            </p>

                            <p
                                class="mt-1 font-bold text-slate-900"
                                x-text="
                                    selectedReceipt
                                        ?.po_number
                                    ?? '—'
                                "
                            ></p>
                        </div>

                        <div
                            class="rounded-2xl border border-sky-200
                                   bg-white p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-500"
                            >
                                Supplier
                            </p>

                            <p
                                class="mt-1 font-bold text-slate-900"
                                x-text="
                                    selectedReceipt
                                        ?.supplier
                                    ?? '—'
                                "
                            ></p>
                        </div>

                        <div
                            class="rounded-2xl border border-sky-200
                                   bg-white p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-500"
                            >
                                Total Received
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold
                                       text-sky-700"
                                x-text="
                                    formatNumber(
                                        selectedReceipt
                                            ?.total_received
                                    )
                                "
                            ></p>
                        </div>
                    </div>

                    <div
                        class="grid max-h-[65vh] gap-6
                               overflow-y-auto p-6 lg:grid-cols-3
                               sm:p-8"
                    >
                        <div class="space-y-6 lg:col-span-2">
                            <div
                                class="overflow-hidden rounded-2xl
                                       border border-slate-200"
                            >
                                <div
                                    class="border-b border-slate-200
                                           bg-slate-50 px-5 py-4"
                                >
                                    <h3
                                        class="font-bold
                                               text-slate-900"
                                    >
                                        PPE Item Breakdown
                                    </h3>
                                </div>

                                <div class="overflow-x-auto">
                                    <table
                                        class="w-full min-w-[760px]"
                                    >
                                        <thead
                                            class="bg-slate-100
                                                   text-xs font-bold
                                                   uppercase
                                                   text-slate-600"
                                        >
                                            <tr>
                                                <th
                                                    class="px-5 py-3
                                                           text-left"
                                                >
                                                    Item
                                                </th>

                                                <th
                                                    class="px-5 py-3
                                                           text-left"
                                                >
                                                    Variant
                                                </th>

                                                <th
                                                    class="px-5 py-3
                                                           text-left"
                                                >
                                                    Unit
                                                </th>

                                                <th
                                                    class="px-5 py-3
                                                           text-right"
                                                >
                                                    Assigned
                                                </th>

                                                <th
                                                    class="px-5 py-3
                                                           text-right"
                                                >
                                                    Received
                                                </th>

                                                <th
                                                    class="px-5 py-3
                                                           text-right"
                                                >
                                                    Shortage
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody
                                            class="divide-y
                                                   divide-slate-100"
                                        >
                                            <template
                                                x-for="
                                                    item in
                                                    selectedReceipt
                                                        ?.items
                                                    ?? []
                                                "
                                                :key="
                                                    item.item
                                                    + item.variant
                                                "
                                            >
                                                <tr>
                                                    <td
                                                        class="px-5 py-4
                                                               font-semibold
                                                               text-slate-900"
                                                        x-text="
                                                            item.item
                                                        "
                                                    ></td>

                                                    <td
                                                        class="px-5 py-4
                                                               text-slate-600"
                                                        x-text="
                                                            item.variant
                                                        "
                                                    ></td>

                                                    <td
                                                        class="px-5 py-4
                                                               text-slate-600"
                                                        x-text="
                                                            item.unit
                                                        "
                                                    ></td>

                                                    <td
                                                        class="px-5 py-4
                                                               text-right"
                                                        x-text="
                                                            formatNumber(
                                                                item
                                                                    .assigned_quantity
                                                            )
                                                        "
                                                    ></td>

                                                    <td
                                                        class="px-5 py-4
                                                               text-right
                                                               font-bold
                                                               text-sky-700"
                                                        x-text="
                                                            formatNumber(
                                                                item
                                                                    .received_quantity
                                                            )
                                                        "
                                                    ></td>

                                                    <td
                                                        class="px-5 py-4
                                                               text-right
                                                               font-bold"
                                                        x-bind:class="
                                                            Number(
                                                                item
                                                                    .shortage_quantity
                                                            ) > 0
                                                                ? 'text-red-700'
                                                                : 'text-emerald-700'
                                                        "
                                                        x-text="
                                                            formatNumber(
                                                                item
                                                                    .shortage_quantity
                                                            )
                                                        "
                                                    ></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div
                                class="rounded-2xl border
                                       border-slate-200 p-5"
                            >
                                <h3
                                    class="font-bold text-slate-900"
                                >
                                    Receipt Information
                                </h3>

                                <dl
                                    class="mt-4 space-y-4 text-sm"
                                >
                                    <div>
                                        <dt
                                            class="font-semibold
                                                   text-slate-500"
                                        >
                                            NEFA Number
                                        </dt>

                                        <dd
                                            class="mt-1 text-slate-900"
                                            x-text="
                                                selectedReceipt
                                                    ?.nefa_number
                                                ?? '—'
                                            "
                                        ></dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="font-semibold
                                                   text-slate-500"
                                        >
                                            Received By
                                        </dt>

                                        <dd
                                            class="mt-1 text-slate-900"
                                            x-text="
                                                selectedReceipt
                                                    ?.received_by
                                                ?? '—'
                                            "
                                        ></dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="font-semibold
                                                   text-slate-500"
                                        >
                                            Submitted At
                                        </dt>

                                        <dd
                                            class="mt-1 text-slate-900"
                                            x-text="
                                                selectedReceipt
                                                    ?.submitted_at
                                                ?? '—'
                                            "
                                        ></dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="font-semibold
                                                   text-slate-500"
                                        >
                                            Status
                                        </dt>

                                        <dd class="mt-2">
                                            <span
                                                class="inline-flex
                                                       rounded-full
                                                       px-3 py-1
                                                       text-xs font-bold
                                                       ring-1"
                                                x-bind:class="
                                                    selectedReceipt
                                                        ?.status
                                                        === 'Pending'
                                                        ? 'bg-red-100 text-red-700 ring-red-200'
                                                        : 'bg-emerald-100 text-emerald-700 ring-emerald-200'
                                                "
                                                x-text="
                                                    selectedReceipt
                                                        ?.status
                                                    ?? 'Pending'
                                                "
                                            ></span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div
                                class="rounded-2xl border
                                       border-slate-200 p-5"
                            >
                                <h3
                                    class="font-bold text-slate-900"
                                >
                                    Remarks
                                </h3>

                                <p
                                    class="mt-3 whitespace-pre-line
                                           text-sm leading-6
                                           text-slate-600"
                                    x-text="
                                        selectedReceipt
                                            ?.remarks
                                        ?? 'No remarks provided.'
                                    "
                                ></p>
                            </div>

                            <div
                                class="rounded-2xl border
                                       border-slate-200 p-5"
                            >
                                <h3
                                    class="font-bold text-slate-900"
                                >
                                    Attached Documents
                                </h3>

                                <div class="mt-4 space-y-2">
                                    <template
                                        x-for="
                                            document in
                                            selectedReceipt
                                                ?.documents
                                            ?? []
                                        "
                                        :key="
                                            document.url
                                            ?? document.name
                                        "
                                    >
                                        <a
                                            x-bind:href="
                                                document.url
                                                ?? '#'
                                            "
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center
                                                   justify-between
                                                   rounded-xl
                                                   border
                                                   border-sky-200
                                                   bg-sky-50
                                                   px-4 py-3
                                                   text-sm font-semibold
                                                   text-sky-700
                                                   transition
                                                   hover:bg-sky-100"
                                        >
                                            <span
                                                class="truncate"
                                                x-text="
                                                    document.name
                                                "
                                            ></span>

                                            <span class="ml-3">
                                                View
                                            </span>
                                        </a>
                                    </template>

                                    <template
                                        x-if="
                                            (
                                                selectedReceipt
                                                    ?.documents
                                                ?? []
                                            ).length === 0
                                        "
                                    >
                                        <p
                                            class="text-sm
                                                   text-slate-500"
                                        >
                                            No documents attached.
                                        </p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end border-t
                               border-slate-200 bg-slate-50
                               px-6 py-4 sm:px-8"
                    >
                        <button
                            type="button"
                            x-on:click="closeDetails()"
                            class="rounded-xl bg-slate-800
                                   px-5 py-2.5 text-sm font-bold
                                   text-white transition
                                   hover:bg-slate-700"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-po_dashboard_layout>
