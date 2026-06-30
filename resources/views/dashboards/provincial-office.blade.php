<x-po_dashboard_layout>

    <div class="mb-4">
        <h1 class="text-[25px]">Dashboard</h1>
        <div class="flex">
            <a href="{{ Route('dashboard')}}">Dashboard/</a>
        </div>
    </div>

    <div class="grid grid-col md:grid-cols-9 grid-rows-2 md:grid-rows-2 gap-2 md:gap-2 m-4">
        <div
            class="col-start-1 h-82.75  row-start-1 col-span-4 md:col-start-1 md:row-start-1 md:col-span-4 md:row-span-1 bg-gray-300 rounded-md p-10">
            PO account</div>
        <div
            class="col-start-5 h-82.75  row-start-1 col-span-5 md:col-start-5 md:row-start-1 md:col-span-5 md:row-span-1 bg-gray-300 rounded-md p-10">
            1</div>
        <div
            class="col-start-1 h-82.5 row-start-2 col-span-9 md:col-start-1 md:row-start-2 md:col-span-9 md:row-span-1 bg-gray-300 rounded-md p-10">
            2</div>
    </div>
</x-po_dashboard_layout>