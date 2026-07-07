@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

    <div class="bg-white rounded-xl shadow">

        <div class="bg-red-900 text-white px-6 py-4">

            <h2 class="text-2xl font-bold">
                Provincial Inventory
            </h2>

        </div>

        <table class="min-w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="px-4 py-3 text-left">
                        Item
                    </th>

                    <th class="px-4 py-3 text-center">
                        Available Quantity
                    </th>

                    <th class="px-4 py-3 text-center">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($inventories as $inventory)

                    <tr>

                        <td class="border px-4 py-3">

                            {{ $inventory->item->item_name }}

                            @if($inventory->item->label)
                                ({{ $inventory->item->label }})
                            @endif

                        </td>

                        <td class="border px-4 py-3 text-center">

                            {{ $inventory->quantity }}

                        </td>

                        <td class="border px-4 py-3 text-center">

                            <a href="{{ route('provincial.inventory.designate', $inventory->id) }}"
                                class="text-blue-600 underline">

                                Designate

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="3" class="text-center py-8">

                            No inventory available.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

@endsection