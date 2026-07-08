@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

<div class="bg-white rounded-xl shadow">

    <div class="bg-red-900 text-white px-6 py-4">

        <h2 class="text-2xl font-bold">
            Supply Designations
        </h2>

    </div>

    <table class="min-w-full">

        <thead class="bg-gray-100">

            <tr>

                <th class="px-4 py-3 text-left">
                    Designation No.
                </th>

                <th class="px-4 py-3 text-left">
                    Project
                </th>

                <th class="px-4 py-3 text-left">
                    Item
                </th>

                <th class="px-4 py-3 text-center">
                    Quantity
                </th>

                <th class="px-4 py-3 text-center">
                    Date
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($designations as $designation)

                <tr>

                    <td class="border px-4 py-3">
                        {{ $designation->designation_number }}
                    </td>

                    <td class="border px-4 py-3">
                        {{ $designation->project_name }}
                    </td>

                    <td class="border px-4 py-3">

                        {{ $designation->item->item_name }}

                        @if($designation->item->label)

                            ({{ $designation->item->label }})

                        @endif

                    </td>

                    <td class="border text-center">

                        {{ $designation->quantity }}

                    </td>

                    <td class="border text-center">

                        {{ $designation->designation_date->format('F d, Y') }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" class="text-center py-8">

                        No designations found.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

@endsection