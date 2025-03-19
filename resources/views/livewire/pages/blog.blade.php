   <?php

   use Livewire\Volt\Component;
   use Livewire\Attributes\Layout;
   use Illuminate\Support\Facades\Auth;

   new #[Layout('layouts.app')] class extends Component {
       public string $facebook = 'https://www.facebook.com';
       public string $twitter = 'https://www.x.com';
       public string $instagram = 'https://www.instagram.com';
       public string $whatsapp = 'https://web.whatsapp.com';
       public string $balance = '$300,000';
       public string $transactions = '';
       public $page = 5;

       public function adFunds() {}

       public function withdraw() {}
   }; ?>
   <div class="h-screen px-16 bg-white" x-data="{
       current: true
   }">


   </div>
