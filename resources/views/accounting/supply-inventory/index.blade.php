<x-po_dashboard_layout title="Supply Inventory Summary">
    <div
        x-data="{
            modalOpen: false,
            selectedItem: '',
            selectedVariant: '',
            selectedUnit: '',
            selectedQuantity: 0,
            selectedPurchaseOrders: [],

            openPurchaseOrderModal(event) {
                try {
                    const encodedPayload = event.currentTarget.dataset.payload;

                    if (!encodedPayload) {
                        console.error('Missing modal payload.');
                        return;
                    }

                    const jsonPayload = decodeURIComponent(
                        Array.prototype.map.call(
                            atob(encodedPayload),
                            character => {
                                return '%' + character
                                    .charCodeAt(0)
                                    .toString(16)
                                    .padStart(2, '0');
                            }
                        ).join('')
                    );

                    const payload = JSON.parse(jsonPayload);

                    this.selectedItem = payload.item_name ?? '';
                    this.selectedVariant = payload.variant ?? 'Standard';
                    this.selectedUnit = payload.unit ?? '—';
                    this.selectedQuantity = Number(
                        payload.available_quantity ?? 0
                    );
                    this.selectedPurchaseOrders =
                        payload.purchase_orders ?? [];

                    this.modalOpen = true;
                    document.body.classList.add('overflow-hidden');
                } catch (error) {
                    console.error(
                        'Unable to open Purchase Order modal:',
                        error
                    );
                }
            },

            closePurchaseOrderModal() {
                this.modalOpen = false;
                this.selectedItem = '';
                this.selectedVariant = '';
                this.selectedUnit = '';
                this.selectedQuantity = 0;
                this.selectedPurchaseOrders = [];
                document.body.classList.remove('overflow-hidden');
            }
        }"
        class="mx-auto max-w-[1800px] space-y-6"
    >
        <section
            class="relative overflow-hidden rounded-3xl border
                   border-sky-200 bg-white shadow-sm"
        >
            <div
                class="absolute inset-y-0 left-0 w-2
                       bg-gradient-to-b from-sky-900 via-sky-600
                       to-sky-400"
            ></div>

            <div
                class="flex flex-col gap-5 px-6 py-7 sm:px-8
                       lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <span
                        class="inline-flex rounded-full bg-sky-100
                               px-3 py-1 text-xs font-bold uppercase
                               tracking-[0.16em] text-sky-700
                               ring-1 ring-sky-200"
                    >
                        Accounting · Read Only
                    </span>

                    <h1
                        class="mt-4 text-2xl font-bold text-slate-950
                               sm:text-3xl"
                    >
                        Supply Inventory Summary
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm text-slate-600">
                        PPE quantities from Purchase Orders that have not
                        been distributed to any provincial office.
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-sky-50 px-5 py-4
                           ring-1 ring-sky-200"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-wider
                               text-slate-500"
                    >
                        Total available PPE
                    </p>

                    <p class="mt-1 text-3xl font-bold text-sky-700">
                        {{ number_format((int) $totalAvailable) }}
                    </p>
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-3xl border border-slate-200
                   bg-white shadow-sm"
        >
            <form
                method="GET"
                action="{{ route('accounting.supply-inventory.index') }}"
                class="flex flex-col gap-3 border-b border-slate-200
                       p-5 sm:flex-row"
            >
                <div class="relative w-full sm:max-w-md">
                    <svg
                        class="pointer-events-none absolute left-4 top-1/2
                               h-5 w-5 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search PPE item, variant, or unit..."
                        class="w-full rounded-xl border-slate-300
                               py-2.5 pl-11 pr-4 text-sm
                               focus:border-sky-500 focus:ring-sky-500"
                    >
                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-sky-600 px-5 py-2.5
                           text-sm font-bold text-white transition
                           hover:bg-sky-700"
                >
                    Search
                </button>

                <a
                    href="{{ route('accounting.supply-inventory.index') }}"
                    class="rounded-xl border border-sky-200
                           px-5 py-2.5 text-center text-sm font-bold
                           text-sky-700 transition hover:bg-sky-50"
                >
                    Reset
                </a>
            </form>

            <div class="overflow-x-auto">
                <table
                    class="w-full min-w-[980px]
                           divide-y divide-slate-200"
                >
                    <thead class="bg-sky-700 text-white">
                        <tr
                            class="text-xs font-bold uppercase
                                   tracking-wide"
                        >
                            <th class="px-6 py-4 text-left">
                                PPE Item
                            </th>

                            <th class="px-6 py-4 text-left">
                                Variant
                            </th>

                            <th class="px-6 py-4 text-left">
                                Unit
                            </th>

                            <th class="px-6 py-4 text-center">
                                Purchase Orders
                            </th>

                            <th class="px-6 py-4 text-right">
                                Available Quantity
                            </th>

                            <th class="px-6 py-4 text-center">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($inventories as $inventory)
                            @php
                                $availableQuantity = (int) (
                                    $inventory->available_quantity ?? 0
                                );

                                $purchaseOrderGroups =
                                    $inventory->purchaseOrderItems
                                        ->filter(
                                            fn ($purchaseOrderItem) =>
                                                $purchaseOrderItem
                                                    ->purchaseOrder !== null
                                        )
                                        ->groupBy('purchase_order_id');

                                $purchaseOrderModalData =
                                    $purchaseOrderGroups
                                        ->map(
                                            function ($purchaseOrderItems) {
                                                $purchaseOrder =
                                                    $purchaseOrderItems
                                                        ->first()
                                                        ?->purchaseOrder;

                                                return [
                                                    'id' =>
                                                        $purchaseOrder?->id,

                                                    'po_number' =>
                                                        $purchaseOrder
                                                            ?->po_number
                                                        ?? '—',

                                                    'po_date' =>
                                                        $purchaseOrder
                                                            ?->po_date
                                                            ?->format(
                                                                'F d, Y'
                                                            )
                                                        ?? '—',

                                                    'nefa_number' =>
                                                        $purchaseOrder
                                                            ?->nefa_number
                                                        ?? '—',

                                                    'supplier' =>
                                                        $purchaseOrder
                                                            ?->supplier
                                                            ?->supplier_name
                                                        ?? '—',

                                                    'status' =>
                                                        $purchaseOrder
                                                            ?->status
                                                        ?? '—',

                                                    'quantity' =>
                                                        (int) $purchaseOrderItems
                                                            ->sum(
                                                                'quantity'
                                                            ),
                                                ];
                                            }
                                        )
                                        ->values()
                                        ->all();

                                $modalPayload = [
                                    'item_name' =>
                                        $inventory->item_name,

                                    'variant' =>
                                        $inventory->label
                                        ?: 'Standard',

                                    'unit' =>
                                        $inventory
                                            ->unit_of_measurement
                                        ?: '—',

                                    'available_quantity' =>
                                        $availableQuantity,

                                    'purchase_orders' =>
                                        $purchaseOrderModalData,
                                ];

                                $encodedModalPayload = base64_encode(
                                    json_encode(
                                        $modalPayload,
                                        JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                    )
                                );
                            @endphp

                            <tr
                                class="transition hover:bg-sky-50/60"
                            >
                                <td class="px-6 py-4">
                                    <p
                                        class="font-semibold
                                               text-slate-900"
                                    >
                                        {{ $inventory->item_name }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{
                                        $inventory->label
                                        ?: 'Standard'
                                    }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{
                                        $inventory
                                            ->unit_of_measurement
                                        ?: '—'
                                    }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-10
                                               items-center justify-center
                                               rounded-full bg-sky-100
                                               px-3 py-1 text-xs font-bold
                                               text-sky-800 ring-1
                                               ring-sky-200"
                                    >
                                        {{
                                            $purchaseOrderGroups->count()
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-6 py-4 text-right text-lg
                                           font-bold text-sky-700"
                                >
                                    {{
                                        number_format(
                                            $availableQuantity
                                        )
                                    }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if (
                                        $purchaseOrderGroups
                                            ->isNotEmpty()
                                    )
                                        <button
                                            type="button"
                                            data-payload="{{
                                                $encodedModalPayload
                                            }}"
                                            x-on:click="
                                                openPurchaseOrderModal(
                                                    $event
                                                )
                                            "
                                            class="inline-flex items-center
                                                   gap-2 rounded-xl
                                                   bg-sky-700 px-4 py-2
                                                   text-xs font-bold
                                                   text-white shadow-sm
                                                   transition
                                                   hover:bg-sky-900"
                                        >
                                            View Purchase Orders
                                        </button>
                                    @else
                                        <span
                                            class="inline-flex rounded-xl
                                                   bg-slate-100 px-4 py-2
                                                   text-xs font-semibold
                                                   text-slate-400"
                                        >
                                            No available PO
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-14 text-center
                                           text-sm font-semibold
                                           text-slate-600"
                                >
                                    No supply inventory records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 p-5">
                {{ $inventories->links() }}
            </div>
        </section>

        <div
            x-cloak
            x-show="modalOpen"
            x-on:keydown.escape.window="
                closePurchaseOrderModal()
            "
            class="fixed inset-0 z-[100] overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div
                x-show="modalOpen"
                x-transition.opacity
                x-on:click="closePurchaseOrderModal()"
                class="fixed inset-0 bg-slate-950/60
                       backdrop-blur-sm"
            ></div>

            <div
                class="relative flex min-h-full items-center
                       justify-center p-4 sm:p-6"
            >
                <div
                    x-show="modalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="
                        opacity-0 translate-y-4 scale-95
                    "
                    x-transition:enter-end="
                        opacity-100 translate-y-0 scale-100
                    "
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="
                        opacity-100 translate-y-0 scale-100
                    "
                    x-transition:leave-end="
                        opacity-0 translate-y-4 scale-95
                    "
                    x-on:click.stop
                    class="relative w-full max-w-6xl
                           overflow-hidden rounded-3xl bg-white
                           shadow-2xl"
                >
                    <div
                        class="relative overflow-hidden
                               bg-gradient-to-r from-sky-900
                               via-sky-700 to-sky-500 px-6
                               py-6 text-white sm:px-8"
                    >
                        <div
                            class="relative flex items-start
                                   justify-between gap-5"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase
                                           tracking-[0.18em]
                                           text-sky-100"
                                >
                                    Undistributed Purchase Orders
                                </p>

                                <h2
                                    class="mt-2 text-2xl font-bold"
                                    x-text="selectedItem"
                                ></h2>

                                <p
                                    class="mt-2 text-sm
                                           text-sky-100"
                                >
                                    <span
                                        x-text="selectedVariant"
                                    ></span>

                                    <span class="mx-2">
                                        •
                                    </span>

                                    <span
                                        x-text="selectedUnit"
                                    ></span>
                                </p>
                            </div>

                            <button
                                type="button"
                                x-on:click="
                                    closePurchaseOrderModal()
                                "
                                class="inline-flex h-10 w-10
                                       items-center justify-center
                                       rounded-xl bg-white/15
                                       text-white transition
                                       hover:bg-white/25"
                            >
                                <span
                                    class="text-2xl leading-none"
                                >
                                    &times;
                                </span>
                            </button>
                        </div>
                    </div>

                    <div
                        class="grid gap-4 border-b
                               border-slate-200 bg-sky-50
                               px-6 py-5 sm:grid-cols-2
                               sm:px-8"
                    >
                        <div
                            class="rounded-2xl border
                                   border-sky-200 bg-white p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       tracking-wider text-slate-500"
                            >
                                Purchase Order Count
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold
                                       text-slate-900"
                                x-text="
                                    selectedPurchaseOrders.length
                                "
                            ></p>
                        </div>

                        <div
                            class="rounded-2xl border
                                   border-sky-200 bg-white p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       tracking-wider text-slate-500"
                            >
                                Available Quantity
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold
                                       text-sky-700"
                                x-text="
                                    Number(
                                        selectedQuantity
                                    ).toLocaleString()
                                "
                            ></p>
                        </div>
                    </div>

                    <div class="max-h-[55vh] overflow-auto">
                        <table
                            class="w-full min-w-[950px]"
                        >
                            <thead
                                class="sticky top-0 z-10
                                       bg-slate-100 text-slate-700
                                       shadow-sm"
                            >
                                <tr
                                    class="text-xs font-bold
                                           uppercase tracking-wide"
                                >
                                    <th
                                        class="px-6 py-4 text-left"
                                    >
                                        PO Number
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left"
                                    >
                                        PO Date
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left"
                                    >
                                        NEFA Number
                                    </th>

                                    <th
                                        class="px-6 py-4 text-left"
                                    >
                                        Supplier
                                    </th>

                                    <th
                                        class="px-6 py-4 text-center"
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right"
                                    >
                                        Quantity
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100"
                            >
                                <template
                                    x-for="
                                        purchaseOrder in
                                        selectedPurchaseOrders
                                    "
                                    :key="
                                        purchaseOrder.id
                                        ?? purchaseOrder.po_number
                                    "
                                >
                                    <tr
                                        class="hover:bg-sky-50/60"
                                    >
                                        <td
                                            class="px-6 py-4
                                                   font-bold
                                                   text-slate-900"
                                            x-text="
                                                purchaseOrder
                                                    .po_number
                                            "
                                        ></td>

                                        <td
                                            class="px-6 py-4
                                                   text-sm
                                                   text-slate-600"
                                            x-text="
                                                purchaseOrder
                                                    .po_date
                                            "
                                        ></td>

                                        <td
                                            class="px-6 py-4
                                                   text-sm
                                                   text-slate-600"
                                            x-text="
                                                purchaseOrder
                                                    .nefa_number
                                            "
                                        ></td>

                                        <td
                                            class="px-6 py-4
                                                   text-sm
                                                   text-slate-600"
                                            x-text="
                                                purchaseOrder
                                                    .supplier
                                            "
                                        ></td>

                                        <td
                                            class="px-6 py-4
                                                   text-center"
                                        >
                                            <span
                                                class="inline-flex
                                                       rounded-full
                                                       px-3 py-1
                                                       text-xs
                                                       font-bold
                                                       ring-1"
                                                x-bind:class="
                                                    purchaseOrder
                                                        .status
                                                        ?.toLowerCase()
                                                        .includes(
                                                            'pending'
                                                        )
                                                        ? 'bg-red-100 text-red-700 ring-red-200'
                                                        : 'bg-emerald-100 text-emerald-700 ring-emerald-200'
                                                "
                                                x-text="
                                                    purchaseOrder
                                                        .status
                                                "
                                            ></span>
                                        </td>

                                        <td
                                            class="px-6 py-4
                                                   text-right
                                                   text-base
                                                   font-bold
                                                   text-sky-700"
                                            x-text="
                                                Number(
                                                    purchaseOrder
                                                        .quantity
                                                ).toLocaleString()
                                            "
                                        ></td>
                                    </tr>
                                </template>

                                <template
                                    x-if="
                                        selectedPurchaseOrders
                                            .length === 0
                                    "
                                >
                                    <tr>
                                        <td
                                            colspan="6"
                                            class="px-6 py-12
                                                   text-center
                                                   text-sm
                                                   text-slate-500"
                                        >
                                            No undistributed
                                            Purchase Orders found
                                            for this PPE item.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="flex justify-end border-t
                               border-slate-200 bg-slate-50
                               px-6 py-4 sm:px-8"
                    >
                        <button
                            type="button"
                            x-on:click="
                                closePurchaseOrderModal()
                            "
                            class="rounded-xl bg-slate-800
                                   px-5 py-2.5 text-sm
                                   font-bold text-white
                                   transition hover:bg-slate-700"
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