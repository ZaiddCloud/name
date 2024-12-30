@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-8">📚 {{ __('قائمة الكتب') }}</h1>
        <a href="{{ route('books.create') }}" class="bg-gradient-to-r from-blue-500 to-teal-400 hover:from-blue-600 hover:to-teal-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg mb-8 inline-block transition duration-200 ease-in-out transform hover:scale-105">{{ __('Add New Book') }}</a>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @if(is_array($books) || $books instanceof \Illuminate\Support\Collection)
                @foreach($books as $book)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-200 hover:scale-105">
                        <div class="p-6">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-3">{{ $book->title }}</h2>
                            <p class="text-gray-700 mb-5">{{ \Illuminate\Support\Str::limit($book->description, 100) }}</p>
                            <a href="{{ route('books.show', $book->id) }}" class="bg-gradient-to-r from-green-400 to-blue-500 hover:from-green-500 hover:to-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">{{ __('View Details') }}</a>
                        </div>
                    </div>
                @endforeach
            @else
                <p>{{ __('No books available.') }}</p>
            @endif
        </div>
    </div>
@endsection
