<!-- component -->
<div id="sidebar"
    class="hidden sm:hidden  md:h-full  relative transition-transform duration-300 ease-in-out bg-white shadow-xl
    md:block w-30 md:w-60 lg:w-60">
    <div class=" my-32 flex justify-center space-y-6 md:space-y-10">
        <div id="menu" class="flex justify-center flex-col space-y-10 ">

            <a href="{{ route('dashboard') }} "wire:navigate
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-110">
                <img width="60" height="60" src="{{ asset('assets/home.png') }}" alt="">
            </a>
            <a href="{{ route('creative.upload') }}" wire:navigate
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-110">
                <img width="60" height="60" src="{{ asset('assets/upload.png') }}" alt="">
            </a>
            <a href=""
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-110">
                <img width="60" height="60" src="{{ asset('assets/explore.png') }}" alt="">
            </a>


        </div>
    </div>
</div>
