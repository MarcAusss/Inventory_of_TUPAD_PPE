@extends('layouts.provincial_office_dashboard')

@section('dashboard-provincial_office')

    <div class="max-w-2xl mx-auto bg-white shadow rounded-xl p-8">

        <h2 class="text-2xl font-bold mb-6">
            Designate PPE
        </h2>

        <div class="mb-6">

            <p>

                <strong>Item:</strong>

                {{ $inventory->item->item_name }}

                @if($inventory->item->label)

                    ({{ $inventory->item->label }})

                @endif

            </p>

            <p>

                <strong>Available:</strong>

                {{ $inventory->quantity }}

            </p>

        </div>

        <form action="{{ route('provincial.inventory.designate.store', $inventory->id) }}" method="POST">

            @csrf

            <div class="mb-4">

                <label class="font-semibold">

                    Project / Beneficiary

                </label>

                <input type="text" name="project_name" class="w-full border rounded p-2" required>

            </div>

            <div class="mb-4">

                <label class="font-semibold">

                    Quantity

                </label>

                <input type="number" min="1" max="{{ $inventory->quantity }}" name="quantity"
                    class="w-full border rounded p-2" required>

            </div>

            <div class="mb-4">

                <label class="font-semibold">

                    Remarks

                </label>

                <textarea name="remarks" class="w-full border rounded p-2"></textarea>

            </div>

            <button class="bg-red-900 text-white px-6 py-2 rounded">

                Save Designation

            </button>

        </form>

    </div>

@endsection