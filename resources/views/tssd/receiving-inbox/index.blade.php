<x-po_dashboard_layout>

    <div class="space-y-6">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    Provincial Receiving Inbox
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    Review Delivery Receipts, Provincial Office remarks, and quantity discrepancies.
                </p>
            </div>

            <span class="inline-flex w-fit rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-800">
                {{ number_format($unreadCount) }} Unread
            </span>

        </div>

        @if(session('success'))

            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-800">
                {{ session('success') }}
            </div>

        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow">

            <div class="bg-[#339DCB] px-6 py-5">

                <h2 class="text-xl font-semibold text-white">
                    Receiving Notifications
                </h2>

            </div>

            <div class="divide-y divide-gray-200">

                @forelse($notifications as $notification)

                    @php
                        $receipt = $notification->deliveryReceipt;

                        $statusClass = match($notification->status) {
                            'Resolved' => 'bg-green-100 text-green-800',
                            'Read' => 'bg-blue-100 text-blue-800',
                            default => 'bg-red-100 text-red-800',
                        };

                        $typeClass = $notification->type === 'Receiving Discrepancy'
                            ? 'border-yellow-400 bg-yellow-50'
                            : 'border-green-400 bg-green-50';
                    @endphp

                    <div class="border-l-4 p-6 {{ $typeClass }}">

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center gap-3">

                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $notification->title }}
                                    </h3>

                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $notification->status }}
                                    </span>

                                    @if($notification->type === 'Receiving Discrepancy')

                                        <span class="rounded-full bg-yellow-200 px-3 py-1 text-xs font-semibold text-yellow-900">
                                            Requires Review
                                        </span>

                                    @endif

                                </div>

                                <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-gray-700 sm:grid-cols-3">

                                    <div>
                                        <span class="font-medium">
                                            Province:
                                        </span>

                                        {{ $notification->province?->name ?? 'Not available' }}
                                    </div>

                                    <div>
                                        <span class="font-medium">
                                            Call-Off:
                                        </span>

                                        {{ $notification->callOff?->call_off_number ?? 'Not available' }}
                                    </div>

                                    <div>
                                        <span class="font-medium">
                                            Delivery Receipt:
                                        </span>

                                        {{ $receipt?->dr_number ?? 'Not available' }}
                                    </div>

                                </div>

                                <p class="mt-4 line-clamp-2 text-sm text-gray-700">
                                    {{ $notification->message }}
                                </p>

                                <p class="mt-3 text-xs text-gray-500">
                                    {{ $notification->created_at->format('F d, Y h:i A') }}
                                </p>

                            </div>

                            <div class="flex flex-wrap gap-2">

                                <a
                                    href="{{ route('tssd.receiving-inbox.show', $notification) }}"
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                >
                                    Review
                                </a>

                                @if($notification->status === 'Unread')

                                    <form
                                        action="{{ route('tssd.receiving-inbox.read', $notification) }}"
                                        method="POST"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                        >
                                            Mark Read
                                        </button>

                                    </form>

                                @endif

                                @if($notification->status !== 'Resolved')

                                    <form
                                        action="{{ route('tssd.receiving-inbox.resolve', $notification) }}"
                                        method="POST"
                                        onsubmit="return confirm('Mark this receiving notification as resolved?');"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                                        >
                                            Resolve
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-14 text-center text-gray-500">
                        No Provincial Office receiving notifications found.
                    </div>

                @endforelse

            </div>

            @if($notifications->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $notifications->links() }}
                </div>

            @endif

        </div>

    </div>

</x-po_dashboard_layout>