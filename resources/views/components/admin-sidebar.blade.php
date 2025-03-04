<!-- component -->
<div id="sidebar"
    class="h-[90vh] px-3 overflow-x-hidden transition-transform duration-300 ease-in-out shadow-xl bg-navy-blue md:block w-30 md:w-60 lg:w-60">
    <div class="mt-10 space-y-6 md:space-y-10">
        <div id="menu" class="flex flex-col space-y-2">

            <a href=""
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:text-base">
                <svg class="inline-block w-6 h-6 fill-current" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                <span class="">Dashboard</span>
            </a>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="flex items-center w-full px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-105">
                    <svg class="inline-block w-6 h-6 fill-current" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="flex-1 text-left">Uploaded Designs</span>
                    <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform transform fill-current"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
                <div x-show="open" @click.away='open = false'
                    class="left-0 w-full mt-2 origin-top-right rounded-md shadow-lg bg-navy-blue transition-75"
                    style="display: none;">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <?xml version="1.0" encoding="UTF-8"?>
                        <a href="#" class="block px-4 py-2 text-sm text-white hover:bg-white hover:text-navy-blue"
                            role="menuitem">
                            Design Stack
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-white hover:bg-white hover:text-navy-blue"
                            role="menuitem">
                            Printable Stack
                        </a>

                    </div>
                </div>
            </div>
            <a href=""
                class="px-2 py-2 text-sm font-medium text-white transition duration-150 ease-in-out rounded-md hover:bg-white hover:text-navy-blue hover:scale-105">
                <svg class="inline-block w-6 h-6 fill-current" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z">
                    </path>
                </svg>
                <span class="">Promote Design</span>
            </a>


        </div>
    </div>
</div>
