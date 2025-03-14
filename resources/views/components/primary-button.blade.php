<button
    {{ $attributes->merge(['type' => 'submit', 'id' => '', 'class' => "w-full mt-14 'inline-flex items-center px-3 py-3 bg-navy-blue border border-transparent rounded-md font-semibold text-xl text-white  capitalize tracking-widest hover:bg-white hover:text-navy-blue hover:border-navy-blue border-navy-blue  focus:bg-white  active:bg-navy-blue active:text-white  focus:outline-none focus:ring-2 focus:ring-navy-blue focus:ring-offset-2  transition ease-in-out duration-150"]) }}>
    {{ $slot }}
</button>
