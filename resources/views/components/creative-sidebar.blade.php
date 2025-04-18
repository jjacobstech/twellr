<div id="sidebar"
    class="relative hidden transition-transform duration-300 ease-in-out bg-white shadow-xl sm:hidden md:block md:w-36 lg:w-44">
    <div class="flex justify-center p-1 space-y-6 my-28 md:space-y-10">
        <div id="menu" class="flex flex-col justify-center space-y-10 ">

            <a href="{{ route('dashboard') }} "
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-110">
                <img width="60" height="60" src="{{ asset('assets/home.png') }}" alt="">
            </a>
            <a href="{{ route('creative.upload') }}"
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-110">
                <img width="60" height="60" src="{{ asset('assets/upload.png') }}" alt="">
            </a>
            <a href="{{ route('explore') }}"
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-110">
                <img width="60" height="60" src="{{ asset('assets/explore.png') }}" alt="">
            </a>


        </div>
    </div>
</div>
