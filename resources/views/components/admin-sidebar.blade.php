<!-- Optimized Sidebar Component -->
<div id="sidebar"
    class="h-screen transition-all duration-300 shadow-lg bg-white overflow-y-auto overflow-x-hidden w-16 sm:w-20 md:w-64 lg:w-72">
    <div class="py-6 px-2 md:px-4 space-y-6">
        <div id="menu" class="flex flex-col space-y-2">
            <!-- Dashboard Link -->
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">
                @svg('heroicon-o-home', ['class' => 'w-6 h-6'])
                <span class="hidden md:block hover:block ">Dashboard</span>
            </a>

            <!-- Uploaded Designs Dropdown -->
            <a href="{{ route('admin.designs') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">

            @svg('eva-upload', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">Uploaded Designs</span>


            </a>


            <!-- Promote Design Link -->
            <a href=""
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z">
                    </path>
                </svg>
                <span class="hidden md:block">Promote Design</span>
            </a>
            <a href="{{ route('admin.orders') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">
                @svg('eva-car', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">Orders</span>
            </a>

            <a href="{{ route('admin.withdrawal') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">
               @svg('eva-credit-card', ['class' => 'w-6 h-6'])
                  <span class="hidden md:block">Withdrawal</span>
            </a>

            <a href=""
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">
                @svg('eva-globe', ['class' => 'w-6 h-6'])
                <span class="hidden md:block"> {{ __('Blog Posts') }}</span>

            </a>
            <a href="{{ route('admin.preferences') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group">
                @svg('heroicon-o-cog', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">System Preferences</span>
            </a>
        </div>
    </div>
</div>
