<!-- Optimized Sidebar Component -->
<div id="sidebar"
    class="h-screen transition-all duration-300 shadow-lg bg-white overflow-y-auto overflow-x-hidden w-16 sm:w-20 md:w-64 lg:w-72">
    <div class="py-6 px-2 md:px-4 space-y-6">
        <div id="menu" class="flex flex-col space-y-2">
            <!-- Dashboard Link -->
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group  @if (url()->current() == route('admin.dashboard'))
                    border-b-2 border-golden
                @endif">
                @svg('heroicon-o-home', ['class' => 'w-6 h-6'])
                <span class="hidden md:block hover:block ">Dashboard</span>
            </a>

            <!-- Uploaded Designs Dropdown -->
            <a href="{{ route('admin.designs') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group  @if (url()->current() == route('admin.designs'))
                    border-b-2 border-golden
                @endif">

            @svg('eva-upload', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">Uploaded Designs</span>


            </a>


            <!-- Promote Design Link -->
            <a href="{{ route('admin.user.management') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group @if (url()->current() == route('admin.user.management'))
                    border-b-2 border-golden
                @endif">
                @svg('solar-users-group-two-rounded-bold-duotone', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">User Management</span>
            </a>
            <a href="{{ route('admin.orders') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group  @if (url()->current() == route('admin.orders'))
                    border-b-2 border-golden
                @endif">
                @svg('eva-car', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">Orders</span>
            </a>

            <a href="{{ route('admin.withdrawal') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group  @if (url()->current() == route('admin.withdrawal'))
                    border-b-2 border-golden
                @endif">
               @svg('eva-credit-card', ['class' => 'w-6 h-6'])
                  <span class="hidden md:block">Withdrawal</span>
            </a>

            <a href="{{ route('admin.blog.post') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group  @if (url()->current() == route('admin.blog.post'))
                    border-b-2 border-golden
                @endif">
                @svg('eva-globe', ['class' => 'w-6 h-6'])
                <span class="hidden md:block"> {{ __('Blog Posts') }}</span>

            </a>
            <a href="{{ route('admin.preferences') }}"
                class="flex items-center gap-2 px-3 py-3 text-sm font-medium text-gray-600 rounded-lg transition-all hover:bg-golden hover:text-white group  @if (url()->current() == route('admin.preferences'))
                    border-b-2 border-golden
                @endif">
                @svg('heroicon-o-cog', ['class' => 'w-6 h-6'])
                <span class="hidden md:block">System Preferences</span>
            </a>
        </div>
    </div>
</div>
