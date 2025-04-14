<?php

use App\Models\State;
use App\Models\Country;
use App\Models\Category;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use function Livewire\Volt\{state, computed, mount, rules, layout};

layout('layouts.admin');
state(['categories' => fn() => Category::all()]);
state(['newCategory' => '']);
state([
    'shippingFee' => fn() => AdminSetting::where('id', 1)->first()->shipping_fee,
]);
state([
    'commissionRate' => fn() => AdminSetting::where('id', 1)->first()->commission_fee
    ]);
state(['debugMode' => fn() => config('app.debug', false)]);
state(['countries' => fn() => Country::with('states')->get()]);
state(['newCountry' => ['name' => '', 'code' => '']]);
state(['newState' => ['name' => '', 'country_id' => '']]);
state(['editingCategory' => null]);
state(['editCategoryName' => '']);
state(['showSuccessMessage' => false]);
state(['successMessage' => '']);

mount(function () {
    $this->debugMode = config('app.debug', false);
});

rules([
    'newCategory' => 'required|min:3|max:50|unique:categories,name',
    'shippingFee' => 'required|numeric|min:0',
    'commissionRate' => 'required|numeric|min:0|max:100',
    'newCountry.name' => 'required|min:3|max:100',
    'newCountry.code' => 'required|size:2|alpha|unique:countries,code',
    'newState.name' => 'required|min:3|max:100',
    'newState.country_id' => 'required|exists:countries,id',
    'editCategoryName' => 'required|min:3|max:50',
]);

$updateShippingFee = function () {
    $this->validate(['shippingFee' => 'required|numeric|min:0']);

    $shipping = AdminSetting::where('id', 1);

    if ($shipping) {
        $shipping->update(['shipping_fee' => $this->shippingFee]);
        $this->showSuccessMessage = true;
        $this->successMessage = 'Shipping fee updated successfully.';
        $this->hideSuccessMessage();
    }
};

$updateCommissionRate = function () {
    $this->validate(['commissionRate' => 'required|numeric|min:0|max:100']);

    $commission = AdminSetting::where('id', 1);

    if ($commission) {
        $commission->update(['commission_fee' => $this->commissionRate]);

        $this->showSuccessMessage = true;
        $this->successMessage = 'Commission rate updated successfully.';
        $this->hideSuccessMessage();
    }
};

$toggleDebugMode = function () {
    $this->debugMode = !$this->debugMode;

    $envContent = File::get(base_path('.env'));

    if (strpos($envContent, 'APP_DEBUG=') !== false) {
        $envContent = preg_replace('/APP_DEBUG=(.*)/', 'APP_DEBUG=' . ($this->debugMode ? 'true' : 'false'), $envContent);
        File::put(base_path('.env'), $envContent);
    }

    // Clear config cache
    Artisan::call('config:clear');

    $this->showSuccessMessage = true;
    $this->successMessage = 'Debug mode ' . ($this->debugMode ? 'enabled' : 'disabled') . ' successfully.';
    $this->hideSuccessMessage();
};

$addCategory = function () {
    $this->validate(['newCategory' => 'required|min:3|max:50|unique:categories,name']);

    Category::create(['name' => $this->newCategory]);
    $this->categories = Category::all();
    $this->newCategory = '';

    $this->showSuccessMessage = true;
    $this->successMessage = 'Category added successfully.';
    $this->hideSuccessMessage();
};

$deleteCategory = function ($categoryId) {
    $category = Category::findOrFail($categoryId);
    $category->delete();
    $this->categories = Category::all();

    $this->showSuccessMessage = true;
    $this->successMessage = 'Category deleted successfully.';
    $this->hideSuccessMessage();
};

$startEditCategory = function ($categoryId) {
    $this->editingCategory = $categoryId;
    $this->editCategoryName = Category::find($categoryId)->name;
};

$saveEditCategory = function () {
    $this->validate(['editCategoryName' => 'required|min:3|max:50']);

    $category = Category::findOrFail($this->editingCategory);
    $category->name = $this->editCategoryName;
    $category->save();

    $this->categories = Category::all();
    $this->editingCategory = null;
    $this->editCategoryName = '';

    $this->showSuccessMessage = true;
    $this->successMessage = 'Category updated successfully.';
    $this->hideSuccessMessage();
};

$cancelEditCategory = function () {
    $this->editingCategory = null;
    $this->editCategoryName = '';
};

$addCountry = function () {
    $this->validate([
        'newCountry.name' => 'required|min:3|max:100',
        'newCountry.code' => 'required|size:2|alpha|unique:countries,code',
    ]);

    Country::create([
        'name' => $this->newCountry['name'],
        'code' => strtoupper($this->newCountry['code']),
    ]);

    $this->countries = Country::with('states')->get();
    $this->newCountry = ['name' => '', 'code' => ''];

    $this->showSuccessMessage = true;
    $this->successMessage = 'Country added successfully.';
    $this->hideSuccessMessage();
};

$deleteCountry = function ($countryId) {
    $country = Country::findOrFail($countryId);
    $country->delete();
    $this->countries = Country::with('states')->get();

    $this->showSuccessMessage = true;
    $this->successMessage = 'Country deleted successfully.';
    $this->hideSuccessMessage();
};

$addState = function () {
    $this->validate([
        'newState.name' => 'required|min:3|max:100',
        'newState.country_id' => 'required|exists:countries,id',
    ]);

    State::create([
        'name' => $this->newState['name'],
        'country_id' => $this->newState['country_id'],
    ]);

    $this->countries = Country::with('states')->get();
    $this->newState = ['name' => '', 'country_id' => $this->newState['country_id']];

    $this->showSuccessMessage = true;
    $this->successMessage = 'State added successfully.';
    $this->hideSuccessMessage();
};

$deleteState = function ($stateId) {
    $state = State::findOrFail($stateId);
    $state->delete();
    $this->countries = Country::with('states')->get();

    $this->showSuccessMessage = true;
    $this->successMessage = 'State deleted successfully.';
    $this->hideSuccessMessage();
};

$updateSettingsFile = function ($key, $value) {
    $settingsPath = config_path('settings.php');

    // Create the settings file if it doesn't exist
    if (!File::exists($settingsPath)) {
        $settingsContent = "<?php\n\nreturn [\n    '{$key}' => {$value},\n];\n";
        File::put($settingsPath, $settingsContent);
    } else {
        $settings = include $settingsPath;
        $settings[$key] = $value;

        $settingsContent = "<?php\n\nreturn [\n";
        foreach ($settings as $k => $v) {
            // Format the value based on type
            if (is_string($v)) {
                $formattedValue = "'{$v}'";
            } elseif (is_bool($v)) {
                $formattedValue = $v ? 'true' : 'false';
            } else {
                $formattedValue = $v;
            }

            $settingsContent .= "    '{$k}' => {$formattedValue},\n";
        }
        $settingsContent .= "];\n";

        File::put($settingsPath, $settingsContent);
    }

    // Clear config cache
    Artisan::call('config:clear');
};

$hideSuccessMessage = function () {
    // Auto-hide the success message after 3 seconds
    $this->dispatch(
        'setTimeout',
        handler: "
        document.querySelector('#success-alert').classList.add('opacity-0');
        setTimeout(() => {
            $this->showSuccessMessage = false;
        }, 500);
    ",
        ms: 3000,
    );
};
?>
<div class=" ">

    <header class="flex items-center justify-between w-full bg-white mb-2 px-3">
        <h2 class="py-4 text-4xl font-extrabold text-gray-500 capitalize">
            {{ __('System Preferences') }}

        </h2>
    </header>

    @if ($showSuccessMessage)
        <div id="success-alert" class="toast toast-top top-16 z-[9999]">
            <div class=" alert-info alert top-10 bg-navy-blue border border-navy-blue text-white px-4 py-3 rounded mb-6 transition-opacity duration-500 "
                role="alert">
                <span class="block sm:inline ">{{ $successMessage }}</span>
            </div>
        </div>
    @endif

    <div wire:loading class="toast toast-top top-28 z-[9999]">
        <div class=" alert-info alert top-10 bg-navy-blue border border-navy-blue text-white py-3 rounded mb-6 transition-opacity duration-500 "
            role="alert">
            <svg class="animate-spin inline-block bw-spinner h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
    </div>


    <!-- Shipping Settings Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-700 ">Shipping & Commission Settings</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Shipping Fee -->
            <div>
                <label for="shippingFee" class="block text-sm font-medium text-gray-700 mb-1">Default Shipping
                    Fee</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" wire:model="shippingFee" id="shippingFee"
                        class="text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                        placeholder="0.00" step="0.01">
                </div>
                @error('shippingFee')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <button wire:click="updateShippingFee"
                    class="mt-2 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm">
                    Update Shipping Fee
                </button>
            </div>

            <!-- Commission Rate -->
            <div>
                <label for="commissionRate" class="block text-sm font-medium text-gray-700 mb-1">Commission Rate
                    (%)</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="number" wire:model="commissionRate" id="commissionRate"
                        class="text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md"
                        placeholder="0" step="0.1">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">%</span>
                    </div>
                </div>
                @error('commissionRate')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <button wire:click="updateCommissionRate"
                    class="mt-2 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm">
                    Update Commission Rate
                </button>
            </div>
        </div>
    </div>

    <!-- Categories Management Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Category Management</h2>

        <!-- Add Category -->
        <div class="mb-6 text-gray-700">
            <label for="newCategory" class="block text-sm font-medium text-gray-700 mb-1">Add New Category</label>
            <div class="flex gap-2">
                <input type="text" wire:model="newCategory" id="newCategory"
                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-gray-700"
                    placeholder="Category name">
                <button wire:click="addCategory"
                    class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-sm">
                    Add
                </button>
            </div>
            @error('newCategory')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Categories List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
                    @foreach ($categories as $category)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if ($editingCategory === $category->id)
                                    <input type="text" wire:model="editCategoryName"
                                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md ">
                                    @error('editCategoryName')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                @else
                                    {{ $category->name }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if ($editingCategory === $category->id)
                                    <button wire:click="saveEditCategory"
                                        class="text-green-600 hover:text-green-900 mr-3">Save</button>
                                    <button wire:click="cancelEditCategory"
                                        class="text-gray-600 hover:text-gray-900">Cancel</button>
                                @else
                                    <button wire:click="startEditCategory({{ $category->id }})"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button wire:click="deleteCategory({{ $category->id }})"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if (count($categories) === 0)
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                No categories found. Add your first category above.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Countries and States Management -->
    <div class="bg-white shadow rounded-lg p-6 mb-6 text-gray-700">
        <h2 class="text-xl font-semibold mb-4">Location Management</h2>

        <!-- Add Country -->
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Add New Country</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="countryName" class="block text-sm font-medium text-gray-700 mb-1">Country Name</label>
                    <input type="text" wire:model="newCountry.name" id="countryName"
                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="e.g. United States">
                    @error('newCountry.name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="countryCode" class="block text-sm font-medium text-gray-700 mb-1">Country Code (2
                        letters)</label>
                    <input type="text" wire:model="newCountry.code" id="countryCode"
                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="e.g. US" maxlength="2">
                    @error('newCountry.code')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex items-end">
                    <button wire:click="addCountry"
                        class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-sm">
                        Add Country
                    </button>
                </div>
            </div>
        </div>

        <!-- Add State -->
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Add New State/Province</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="stateName" class="block text-sm font-medium text-gray-700 mb-1">State/Province
                        Name</label>
                    <input type="text" wire:model="newState.name" id="stateName"
                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="e.g. California">
                    @error('newState.name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="stateCountry" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <select wire:model="newState.country_id" id="stateCountry"
                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="">Select a country</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                    @error('newState.country_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex items-end">
                    <button wire:click="addState"
                        class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-sm">
                        Add State/Province
                    </button>
                </div>
            </div>
        </div>

        <!-- Countries and States List -->
        <div class="mt-6">
            <h3 class="text-lg font-medium mb-2">Countries & States/Provinces</h3>

            @foreach ($countries as $country)
                <div class="mb-6 border border-gray-200 rounded-md">
                    <div class="flex justify-between items-center bg-gray-50 p-4 border-b border-gray-200">
                        <div>
                            <h4 class="font-medium">{{ $country->name }} ({{ $country->code }})</h4>
                        </div>
                        <div>
                            <button wire:click="deleteCountry({{ $country->id }})"
                                class="text-red-600 hover:text-red-900 text-sm"
                                onclick="return confirm('Are you sure you want to delete this country? All associated states will also be deleted.')">
                                Delete Country
                            </button>
                        </div>
                    </div>

                    <div class="p-4">
                        @if (count($country->states) > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            State/Province</th>
                                        <th
                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($country->states as $state)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $state->name }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <button wire:click="deleteState({{ $state->id }})"
                                                    class="text-red-600 hover:text-red-900 text-sm"
                                                    onclick="return confirm('Are you sure you want to delete this state?')">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm text-gray-500">No states/provinces added for this country yet.</p>
                        @endif
                    </div>
                </div>
            @endforeach

            @if (count($countries) === 0)
                <div class="text-center p-6 bg-gray-50 rounded-md">
                    <p class="text-gray-500">No countries added yet. Add your first country using the form above.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Debug Mode Settings -->
    <div class="bg-white shadow rounded-lg p-6 text-gray-700">
        <h2 class="text-xl font-semibold mb-4">System Debug Settings </h2>

        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-md">
            <div>
                <h3 class="font-medium">Debug Mode</h3>
                <p class="text-sm text-gray-500">Enable debug mode to see detailed error messages and stack traces.
                    Disable in production environments.</p>
            </div>
            <div>
                <button wire:click="toggleDebugMode"
                    class="{{ $debugMode ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 hover:bg-gray-400' }} relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none">
                    <span class="sr-only">Toggle debug mode</span>
                    <span
                        class="{{ $debugMode ? 'translate-x-6' : 'translate-x-1' }} inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                </button>
                <p class="text-sm font-medium mt-1">{{ $debugMode ? 'Enabled' : 'Disabled' }}</p>
            </div>

        </div>
    </div>

</div>
