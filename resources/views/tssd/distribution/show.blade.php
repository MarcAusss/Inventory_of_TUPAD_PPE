<x-po_dashboard_layout>

    <div class="max-w-7xl mx-auto py-8 space-y-8">

        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div class="bg-white rounded-xl shadow border p-6">

            <h1 class="text-3xl font-bold text-red-900">
                Purchase Order: {{ $purchaseOrder->po_number }}
            </h1>

            <div class="grid grid-cols-3 gap-4 mt-4 text-sm">

                <div>
                    <span class="font-semibold">Supplier:</span>
                    {{ $purchaseOrder->supplier->supplier_name ?? '-' }}
                </div>

                <div>
                    <span class="font-semibold">PO Date:</span>
                    {{ $purchaseOrder->po_date }}
                </div>

                <div>
                    <span class="font-semibold">NEFA:</span>
                    {{ $purchaseOrder->nefa_number ?? '-' }}
                </div>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- TOTAL PURCHASED PPE -->
        <!-- ================================================= -->

        <div class="bg-white rounded-xl shadow border p-6">

            <h2 class="text-xl font-bold text-red-900 mb-4">
                Total Purchased PPE
            </h2>

            <div class="grid grid-cols-7 gap-4 text-center">

                <div>
                    <p class="font-semibold">LS-M</p>
                    <p>{{ $purchased['lsm'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">LS-L</p>
                    <p>{{ $purchased['lsl'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">US9</p>
                    <p>{{ $purchased['us9'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">US10</p>
                    <p>{{ $purchased['us10'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">Bucket</p>
                    <p>{{ $purchased['bucket'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">Gloves</p>
                    <p>{{ $purchased['gloves'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">Mask</p>
                    <p>{{ $purchased['mask'] }}</p>
                </div>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- REMAINING STOCK -->
        <!-- ================================================= -->

        <div class="bg-white rounded-xl shadow border p-6">

            <h2 class="text-xl font-bold text-green-700 mb-4">
                Remaining Stock
            </h2>

            <div class="grid grid-cols-7 gap-4 text-center">

                <div>
                    <p class="font-semibold">LS-M</p>
                    <p>{{ $remaining['lsm'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">LS-L</p>
                    <p>{{ $remaining['lsl'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">US9</p>
                    <p>{{ $remaining['us9'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">US10</p>
                    <p>{{ $remaining['us10'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">Bucket</p>
                    <p>{{ $remaining['bucket'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">Gloves</p>
                    <p>{{ $remaining['gloves'] }}</p>
                </div>

                <div>
                    <p class="font-semibold">Mask</p>
                    <p>{{ $remaining['mask'] }}</p>
                </div>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- PROVINCE DISTRIBUTION -->
        <!-- ================================================= -->

        <div class="bg-white rounded-xl shadow border overflow-hidden">

            <div class="bg-red-900 text-white px-6 py-4 font-bold text-lg">
                Province Distribution
            </div>

            <table class="min-w-full">

                <thead class="bg-gray-100">

                    <tr>
                        <th class="px-4 py-3 text-left">Province</th>
                        <th class="px-4 py-3">LS-M</th>
                        <th class="px-4 py-3">LS-L</th>
                        <th class="px-4 py-3">US9</th>
                        <th class="px-4 py-3">US10</th>
                        <th class="px-4 py-3">Bucket</th>
                        <th class="px-4 py-3">Gloves</th>
                        <th class="px-4 py-3">Mask</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($provinces as $province)

                        @php
                            $d = $distributions->get($province->id, collect());

                            $lsm = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Long Sleeve' &&
                                $r->item->label == 'Medium'
                            )->sum('quantity');

                            $lsl = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Long Sleeve' &&
                                $r->item->label == 'Large'
                            )->sum('quantity');

                            $us9 = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Rubber Boots' &&
                                $r->item->label == 'US9'
                            )->sum('quantity');

                            $us10 = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Rubber Boots' &&
                                $r->item->label == 'US10'
                            )->sum('quantity');

                            $bucket = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Bucket Hat'
                            )->sum('quantity');

                            $gloves = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Hand Gloves'
                            )->sum('quantity');

                            $mask = $d->filter(
                                fn($r) =>
                                $r->item &&
                                $r->item->item_name == 'Mask'
                            )->sum('quantity');
                        @endphp

                        <tr class="border-t">

                            <td class="px-4 py-2 font-semibold">
                                {{ $province->name }}
                            </td>

                            <td class="text-center">{{ $lsm }}</td>
                            <td class="text-center">{{ $lsl }}</td>
                            <td class="text-center">{{ $us9 }}</td>
                            <td class="text-center">{{ $us10 }}</td>
                            <td class="text-center">{{ $bucket }}</td>
                            <td class="text-center">{{ $gloves }}</td>
                            <td class="text-center">{{ $mask }}</td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</x-po_dashboard_layout>