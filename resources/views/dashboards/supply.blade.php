<x-po_dashboard_layout title="Supply Unit Dashboard">

    <div class="mb-4">
        <h1 class="text-[25px]">Dashboard</h1>
        <div class="flex">
            <a href="{{ Route('dashboard')}}">Dashboard/</a>
        </div>
    </div>

    {{-- <div class="grid grid-cols-[repeat(3,minmax(0,0.85fr))] gap-6 w-full h-fit">
        <div class="h-[235px] bg-white rounded flex items-center justify-center">1</div>

        <div class="col-span-2 h-[235px] bg-white rounded flex items-center justify-center">3</div>

        <div class="col-span-3 h-[330px] bg-white rounded flex items-center justify-center">4</div>
    </div> --}}

    <div class="grid grid-col md:grid-cols-9 grid-rows-2 md:grid-rows-2 gap-2 md:gap-2 m-4">
        <div
            class="col-start-1 h-82.75  row-start-1 col-span-4 md:col-start-1 md:row-start-1 md:col-span-4 md:row-span-1 bg-gray-300 rounded-md p-10">
            Supply Account</div>
        <div
            class="col-start-5 h-82.75  row-start-1 col-span-5 md:col-start-5 md:row-start-1 md:col-span-5 md:row-span-1 bg-gray-300 rounded-md p-10">
            1</div>
        <div
            class="col-start-1 h-82.5 row-start-2 col-span-9 md:col-start-1 md:row-start-2 md:col-span-9 md:row-span-1 bg-gray-300 rounded-md p-10">
            2</div>

    </div>

</x-po_dashboard_layout>