<?php

use App\Models\State;
use Mary\Traits\Toast;
use App\Models\Country;
use App\Models\Category;
use App\Models\Material;
use App\Models\ShippingFee;
use App\Models\AdminSetting;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use function Livewire\Volt\{state, computed, mount, rules, layout, uses};
layout('layouts.admin');
uses(Toast::class);

state(['categories' => fn() => Category::all()]);
state(['blogCategories' => fn() => BlogCategory::all()]);
state(['materials' => fn() => Material::all()]);
state(['shippingRates' => fn() => ShippingFee::all()]);
state(['countries' => fn() => Country::with('states')->get()]);

state([
    'commissionRate' => fn() => AdminSetting::where('id', 1)->first()->commission_fee,
]);

state(['newCategory' => '']);
state(['newBlogCategory' => '']);
state(['newMaterial' => '']);
state(['newMaterialPrice' => '']);

state(['debugMode' => fn() => config('app.debug', false)]);
state(['newCountry' => ['name' => '', 'code' => '']]);
state(['newState' => ['name' => '', 'country_id' => '']]);

state(['editingCategory' => null]);
state(['editingBlogCategory' => null]);
state(['editingShippingFee' => null]);
state(['editingMaterial' => null]);

state(['editCategoryName' => '']);
state(['editBlogCategoryName' => '']);
state(['editShippingFee' => '']);
state(['editShippingFeeLocation' => '']);
state(['shippingFee' => '']);
state(['shippingFeeLocation' => '']);
state(['successMessage' => '']);
state(['materialPrice' => '']);
state(['editMaterialPrice' => '']);
state(['material' => '']);
state(['editMaterialName' => '']);
state(['showSuccessMessage' => false]);

mount(function () {
    $this->debugMode = config('app.debug', false);
});

rules([
    'newCategory' => 'required|min:3|max:50|unique:categories,name',
    'shippingFee' => 'required|numeric|min:0',
    'shippingFeeLocation' => 'required|string|min:0',
    'commissionRate' => 'required|numeric|min:0|max:100',
    'newCountry.name' => 'required|min:3|max:100',
    'newCountry.code' => 'required|size:2|alpha|unique:countries,code',
    'newState.name' => 'required|min:3|max:100',
    'newState.country_id' => 'required|exists:countries,id',
    'editCategoryName' => 'required|min:3|max:50',
]);

$addShippingFee = function () {
    $this->validate([
        'shippingFee' => 'required|numeric|min:0',
        'shippingFeeLocation' => 'required|string|min:0|unique:shippingRates,location',
    ]);

    ShippingFee::create([
        'rate' => $this->shippingFee,
        'location' => $this->shippingFeeLocation,
    ]);
    $this->shippingRates = ShippingFee::all();
    $this->shippingFee = '';
    $this->shippingFeeLocation = '';

    $this->success('Category added successfully.');
};

$deleteShippingFee = function ($id) {
    $shipping = ShippingFee::where('id', $id);

    if ($shipping) {
        $shipping->delete();
        $this->editingShippingFee = null;
        $this->editShippingFee = '';
        $this->editShippingFeeLocation = '';
        $this->success('Shipping rate deleted successfully.');
    }
};

$saveEditShippingFee = function () {
    $this->validate([
        'editShippingFee' => 'required|numeric|min:0',
        'editShippingFeeLocation' => 'required|string|min:0',
    ]);

    $shipping = ShippingFee::where('id', $this->editingShippingFee);

    if ($shipping) {
        $shipping->update(['rate' => $this->editShippingFee, 'location' => $this->editShippingFeeLocation]);
        $this->editingShippingFee = null;
        $this->editShippingFee = '';
        $this->editShippingFeeLocation = '';
        $this->success('Shipping rate updated successfully.');
    }
};

$startEditShippingFee = function ($id) {
    $this->editingShippingFee = $id;
    $this->editShippingFee = ShippingFee::find($id)->rate;
    $this->editShippingFeeLocation = ShippingFee::find($id)->location;
};

$cancelEditShippingFee = function () {
    $this->editingShippingFee = null;
    $this->editShippingFee = '';
    $this->editShippingFeeLocation = '';
};

$updateCommissionRate = function () {
    $this->validate(['commissionRate' => 'required|numeric|min:0|max:100']);

    $commission = AdminSetting::where('id', 1);

    if ($commission) {
        $commission->update(['commission_fee' => $this->commissionRate]);
        $this->success('Commission rate updated successfully.');
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

    $this->success('Debug mode ' . ($this->debugMode ? 'enabled' : 'disabled') . ' successfully.');
};

$addCategory = function () {
    $this->validate(['newCategory' => 'required|min:3|max:50|unique:categories,name']);

    Category::create(['name' => $this->newCategory]);
    $this->categories = Category::all();
    $this->newCategory = '';

    $this->success('Category added successfully.');
};

$deleteCategory = function ($categoryId) {
    $category = Category::findOrFail($categoryId);
    $category->delete();
    $this->categories = Category::all();

    $this->success('Category deleted successfully.');
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

    $this->success('Category updated successfully.');
};

$cancelEditCategory = function () {
    $this->editingCategory = null;
    $this->editCategoryName = '';
};

$addBlogCategory = function () {
    $this->validate(['newBlogCategory' => 'required|min:3|max:50|unique:categories,name']);

    BlogCategory::create(['name' => $this->newBlogCategory]);
    $this->blogCategories = BlogCategory::all();
    $this->newBlogCategory = '';

    $this->success('Blog Category added successfully.');
};

$deleteBlogCategory = function ($blogCategoryId) {
    $blogCategory = BlogCategory::findOrFail($blogCategoryId);
    $blogCategory->delete();
    $this->blogCategories = BlogCategory::all();

    $this->success('Blog Category deleted successfully.');
};

$startEditBlogCategory = function ($blogCategoryId) {
    $this->editingBlogCategory = $blogCategoryId;
    $this->editBlogCategoryName = BlogCategory::find($blogCategoryId)->name;
};

$saveEditBlogCategory = function () {
    $this->validate(['editBlogCategoryName' => 'required|min:3|max:50']);

    $blogCategory = BlogCategory::findOrFail($this->editingBlogCategory);
    $blogCategory->name = $this->editBlogCategoryName;
    $blogCategory->save();

    $this->blogCategories = Category::all();
    $this->editingBlogCategory = null;
    $this->editBlogCategoryName = '';

    $this->success('Category updated successfully.');
};

$cancelEditBlogCategory = function () {
    $this->editingBlogCategory = null;
    $this->editBlogCategoryName = '';
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

    $this->success('Country added successfully.');
};

$deleteCountry = function ($countryId) {
    $country = Country::findOrFail($countryId);
    $country->delete();
    $this->countries = Country::with('states')->get();

    $this->success('Country deleted successfully.');
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

    $this->success('State added successfully.');
};

$deleteState = function ($stateId) {
    $state = State::findOrFail($stateId);
    $state->delete();
    $this->countries = Country::with('states')->get();

    $this->success('State deleted successfully.');
};

$addMaterial = function () {
    $this->validate(['newMaterial' => 'required|min:3|max:50|unique:materials,name',
        'newMaterialPrice' => 'required|numeric|min:0',
    ]);

    Material::create(['name' => $this->newMaterial, 'price' => $this->newMaterialPrice]);
    $this->materials = Material::all();
    $this->newMaterial = '';
      $this->newMaterialPrice = '';

    $this->success('Material added successfully.');
};

$deleteMaterial = function ($material_id) {
    $material = Material::findOrFail($material_id);
    $material->delete();
    $this->materials = Material::all();

    $this->success('Material deleted successfully.');
};

$startEditMaterial = function ($material_id) {
    $this->editingMaterial = $material_id;
    $material =  Material::find($material_id);

    $this->editMaterialName =$material->name;
     $this->editMaterialPrice = $material->price;
};

$saveEditMaterial = function () {
    $this->validate(['editMaterialName' => 'required|min:3|max:50','editMaterialPrice' => 'required|min:3|max:50']);

    $material = Material::findOrFail($this->editingMaterial);
    $material->name = $this->editMaterialName;
    $material->price = $this->editMaterialPrice;
    $material->save();

    $this->materials = Material::all();
    $this->editingMaterial = null;
    $this->editMaterialName = '';
    $this->editMaterialPrice = '';

    $this->success('Material updated successfully.');
};

$cancelEditMaterial = function () {
    $this->editingMaterial = null;
    $this->editMaterialName = '';
     $this->editMaterialPrice = '';
};

?>
<div class="w-screen bg-gray-100 h-screen overflow-hidden fixed pt-1">

    {{-- <header class="flex items-center justify-between w-full bg-white mb-1 px-5">
        <h2 class="py-4 text-3xl font-extrabold text-gray-500 capitalize">
            {{ __('System Preferences') }}

        </h2>
    </header> --}}

    @if ($showSuccessMessage)
        <div id="success-alert" class="toast toast-top top-16 z-[9999]">
            <div class=" alert-info alert top-10 bg-navy-blue border border-navy-blue text-white px-4 py-3 rounded mb-6 transition-opacity duration-500 "
                role="alert">
                <span class="block sm:inline ">{{ $successMessage }}</span>
            </div>
        </div>
    @endif

    <div wire:loading class="toast toast-top top-28 z-[9999]">
        <div class=" alert-info alert top-10 bg-green-500 border border-green-500 text-white py-3 rounded mb-6 transition-opacity duration-500 "
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

    <div class="flex w-screen bg-gray-100 overflow-hidden h-screen fixed">

        <!-- Sidebar component -->
        <x-admin-sidebar />
        {{-- settings --}}
        <div class="overflow-y-scroll mb-16 pb-2 w-full px-1 scrollbar-thin scrollbar-thumb-navy-blue scrollbar-track-gray-100">
            <!-- Shipping Settings Section -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 ">Shipping & Commission Settings</h2>

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

                <div class="grid gap-6 mt-3">
                    <!-- Shipping Fee -->
                    <div class="flex w-full gap-3">
                        <div class="w-1/2">
                            <label for="shippingFee" class="block text-sm font-medium text-gray-700 mb-1">
                                Shipping
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

                            <button wire:click="addShippingFee"
                                class="mt-2 bg-green-600 hover:bg-blue-700 text-white py-2 px-4 rounded text-sm ">
                                Add Shipping Fee
                            </button>
                        </div>

                        <div class="w-1/2">
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                                Location
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0  flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm pl-1.5">
                                        @svg('eva-pin', ['class' => 'w-5 h-5'])
                                    </span>
                                </div>
                                <input type="text" wire:model="shippingFeeLocation" id="shippingLocation"
                                    class="text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Add Location">
                            </div>
                            @error('shippingLocation')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fee
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
                            @foreach ($shippingRates as $shippingRate)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($editingShippingFee === $shippingRate->id)
                                            <input type="text" wire:model="editShippingFeeLocation"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md ">
                                            @error('editShippingFee')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        @else
                                            {{ $shippingRate->location }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($editingShippingFee === $shippingRate->id)
                                            <input type="text" wire:model="editShippingFee"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md ">
                                            @error('editShippingFee')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        @else
                                            {{ $shippingRate->rate }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if ($editingShippingFee === $shippingRate->id)
                                            <button wire:click="saveEditShippingFee"
                                                class="text-green-600 hover:text-green-900 mr-3">Save</button>
                                            <button wire:click="cancelEditShippingFee"
                                                class="text-gray-600 hover:text-gray-900">Cancel</button>
                                        @else
                                            <button wire:click="startEditShippingFee({{ $shippingRate->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button wire:click="deleteShippingFee({{ $shippingRate->id }})"
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if (count($shippingRates) === 0)
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No Shipping Rates found. Add your first Shipping Rate above.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Categories Management Section -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Category Management</h2>

                <!-- Add Category -->
                <div class="mb-6 text-gray-700">
                    <label for="newCategory" class="block text-sm font-medium text-gray-700 mb-1">Add New
                        Category</label>
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

                <!-- Add Blog Category -->
                <div class="mb-6 text-gray-700">
                    <label for="newBlogCategory" class="block text-sm font-medium text-gray-700 mb-1">Add New Blog
                        Category</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="newBlogCategory" id="newBlogCategory"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-gray-700"
                            placeholder="Category name">
                        <button wire:click="addBlogCategory"
                            class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-sm">
                            Add
                        </button>
                    </div>
                    @error('newBlogCategory')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Blog Categories List -->
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
                            @foreach ($blogCategories as $blogCategory)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($editingBlogCategory === $blogCategory->id)
                                            <input type="text" wire:model="editBlogCategoryName"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md ">
                                            @error('editBlogCategoryName')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        @else
                                            {{ $blogCategory->name }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if ($editingBlogCategory === $blogCategory->id)
                                            <button wire:click="saveEditBlogCategory"
                                                class="text-green-600 hover:text-green-900 mr-3">Save</button>
                                            <button wire:click="cancelEditBlogCategory"
                                                class="text-gray-600 hover:text-gray-900">Cancel</button>
                                        @else
                                            <button wire:click="startEditBlogCategory({{ $blogCategory->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button wire:click="deleteBlogCategory({{ $blogCategory->id }})"
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if (count($blogCategories) === 0)
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

            <!-- Material Management Section -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Material Management</h2>

                <!-- Add Material -->
                <div class="mb-6 text-gray-700">
                    <label for="newMaterial" class="block text-sm font-medium text-gray-700 mb-1">Add New
                        Material</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="newMaterial" id="newMaterial"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-gray-700"
                            placeholder="Material name">
                             <input type="text" wire:model="newMaterialPrice" id="newMaterialPrice"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md text-gray-700"
                            placeholder="Material Price">
                        <button wire:click="addMaterial"
                            class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-sm">
                            Add
                        </button>
                    </div>
                    @error('newMaterial')
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-gray-700">
                            @foreach ($materials as $material)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($editingMaterial === $material->id)
                                            <input type="text" wire:model="editMaterialName"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md ">
                                            @error('editMaterialName')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        @else
                                            {{ $material->name }}
                                        @endif
                                    </td>
                                     <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if ($editingMaterial === $material->id)
                                            <input type="text" wire:model="editMaterialPrice"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md ">
                                            @error('editMaterialPrice')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        @else
                                            {{ $material->price }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if ($editingMaterial === $material->id)
                                            <button wire:click="saveEditMaterial"
                                                class="text-green-600 hover:text-green-900 mr-3">Save</button>
                                            <button wire:click="cancelEditMaterial"
                                                class="text-gray-600 hover:text-gray-900">Cancel</button>
                                        @else
                                            <button wire:click="startEditMaterial({{ $material->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                            <button wire:click="deleteMaterial({{ $material->id }})"
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this material?')">Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if (count($materials) === 0)
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No categories found. Add your first material above.
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
                            <label for="countryName" class="block text-sm font-medium text-gray-700 mb-1">Country
                                Name</label>
                            <input type="text" wire:model="newCountry.name" id="countryName"
                                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                placeholder="e.g. United States">
                            @error('newCountry.name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="countryCode" class="block text-sm font-medium text-gray-700 mb-1">Country
                                Code
                                (2
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
                            <label for="stateCountry"
                                class="block text-sm font-medium text-gray-700 mb-1">Country</label>
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
                                    <p class="text-sm text-gray-500">No states/provinces added for this country
                                        yet.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if (count($countries) === 0)
                        <div class="text-center p-6 bg-gray-50 rounded-md">
                            <p class="text-gray-500">No countries added yet. Add your first country using the form
                                above.
                            </p>
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
                        <p class="text-sm text-gray-500">Enable debug mode to see detailed error messages and stack
                            traces.
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
    </div>

</div>
