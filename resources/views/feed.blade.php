<x-app-layout>
    <div class="mx-5 my-5" x-data="{
        open: false,
        name: 'Joshua',
        search: ' ',
        posts: [
            { title: 'post one' },
            { title: 'post two' },
            { title: 'post three' },
            { title: 'post four' }
        ]
    }">
        <button x-on:click="open = !open" class="px-4 py-2 mt-2 ml-1 text-white rounded-xl"
            x-bind:class="open ? 'bg-blue-800' : 'bg-slate-700'">Toggle</button>
        <div x-show="open" x-transition x-cloak="display:none" class="p-4 my-6 bg-gray-200 rounded">Hello world</div>
        <div class="my-4 ml-1">
            My name is <span x-text='name' class="font-bold"></span>
        </div>

        <div x-effect="console.log(open)"></div>

        <input x-model="search" type="text" class='w-full p-2 mt-6 mb-2 border' placeholder="Search for something" />

        <p>
            <span class="font-bold">Searching for:</span>
            <span x-text="search"></span>
        </p>
        <template x-if="open">
            <div class="p-2 mt-8 bg-gray-50">Template based on a condition</div>
        </template>
        <h3 class="mt-6 mb-3 text-2xl font-bold">Posts</h3>

        <template x-for="post in posts">
            <div x-text="post.title" class="capitalize "></div>
        </template>


        <button @click="posts.push({title:'new post'})" class="px-4 py-3 text-white bg-blue-800 rounded-lg">Add
            Post</button>
    </div>
</x-app-layout>