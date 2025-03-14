<?php
use Carbon\Carbon;
use App\Models\Transaction;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    public string $facebook = 'https://www.facebook.com';
    public string $twitter = 'https://www.x.com';
    public string $instagram = 'https://www.instagram.com';
    public string $whatsapp = 'https://web.whatsapp.com';
    public string $balance = '';
    public int $page;
    public int $user;
    public int $count = 10;

    public function mount()
    {
        $this->page = 1;
        $this->user = Auth::user()->id;
        $this->balance = '$' . Auth::user()->wallet_balance;
    }
    public function addFunds() {}

    public function withdraw() {}
};
?>
<div class="h-screen px-16 bg-white" x-data="{
    current: true
}">
    <header class="">
        <h2 class="pt-2 text-3xl font-extrabold text-gray-500">
            {{ __('Wallet') }}
        </h2>
    </header>
    <div class=" py-4 bg-gray-100 px-5 w-100 rounded-[14px] text-center items-center justify-center mb-2 flex">
        <div class="grid justify-start w-1/2">
            <p class="grid justify-start pb-3 text-gray-400">Current Balance</p>
            <p class="grid justify-start text-4xl font-extrabold text-gray-700 duration-500 hover:scale-110">
                {{ $balance }}</p>
        </div>
        <div class="flex justify-end w-1/2 gap-5 font-bold">
            <button wire:click=''addFunds
                class="px-5 py-2 duration-500 bg-golden text-neutral-600 hover:scale-110 rounded-xl">Add
                Funds</button>
            @if (Auth::user()->isCreative())
                <button wire:click='withdraw'
                    class="bg-transparent border-[1px] border-neutral-600 text-neutral-600 py-2 px-5 rounded-xl hover:scale-110 duration-500">Withdraw</button>
            @endif
        </div>
    </div>
    <h1 class="px-5 py-2 text-2xl font-extrabold text-left  text-gray-500 bg-gray-100 rounded-t-[14px]">
        Trasaction History

    </h1>

    <div class="relative overflow-x-auto  shadow-md sm:rounded-b-[14px] h-72 bg-gray-100">

        @php
            $transactions = Transaction::where('user_id', '=', Auth::id())->paginate($count)->reverse();
            $paginator = Transaction::where('user_id', '=', Auth::id())->paginate($count);

        @endphp
        <table
            class="justify-center w-full h-64 text-sm text-left text-gray-500 bg-gray-100 rtl:text-right dark:text-gray-400">
            @if ($transactions == null)
                <h1>No Transactions</h1>
            @else
                <thead class="text-xs text-gray-700 uppercase bg-gray-300">
                    <tr>

                        <th scope="col" class="px-6 py-3 text-center">
                            Transaction
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-center">
                            Date
                        </th>

                    </tr>
                </thead>
                <tbody class="h-auto overflow-y-scroll bg-gray-100 w-100">
                    @foreach ($transactions as $transaction)
                        <tr class="h-5 bg-gray-100 border-b border-gray-200 ">

                            <th scope="row"
                                class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap dark:text-white ">
                                {{ $transaction->transaction_type }}
                            </th>

                            <td class="px-6 py-4 text-center ">
                                {{ $transaction->amount }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $transaction->status }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ Carbon::parse($transaction->created_at)->format('d/m/Y') }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            @endif
        </table>

    </div>
    <div class="pt-2">
        {{ $paginator }}
    </div>

</div>
