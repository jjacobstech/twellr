<ul class="flex md:w-1/2 md:font-bold md gap-2">
    <li class="w-1/2 h-5">
        <input type="radio" wire:model='role' id="creative" name="role" value="creative" class="hidden peer" required />
        <label for="creative"
            class="block w-full py-2.5 font-extrabold text-lg text-center bg-white border rounded-lg cursor-pointer lg:px-3 xl:px-5 text-navy-blue border-navy-blue peer-checked:border-navy-blue peer-checked:text-white peer-checked:bg-navy-blue">

            {{__('Creative') }}

        </label>
    </li>
    <li class="w-1/2">
        <input type="radio" wire:model='role' id="user" name="role" checked value="user" class="hidden peer">
        <label for="user"
            class="block w-full py-2.5 text-center text-lg bg-white font-extrabold border rounded-lg cursor-pointer lg:px-3 xl:px-5 text-navy-blue border-navy-blue peer-checked:border-navy-blue peer-checked:text-white peer-checked:bg-navy-blue">{{__('User')
            }}

        </label>
    </li>
</ul>
