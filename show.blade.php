@extends('layouts.app')

@section('content')
    @php
        $isRtl = app()->getLocale() == 'ar';
    @endphp
    <div class="container mx-auto p-6" style="direction: {{ $isRtl ? 'rtl' : 'ltr' }}; text-align: {{ $isRtl ? 'right' : 'left' }};">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-8">{{ __('Book Details') }}</h1>
        <div class="bg-white p-8 shadow-xl rounded-lg mb-8">
            <h2 class="text-3xl font-semibold text-gray-900">{{ $book->title }}</h2>
            <p class="text-gray-700 text-lg mt-4">{{ __('Author') }}: {{ $book->author }}</p>
            <p class="text-gray-700 text-lg mt-4">{{ $book->description }}</p>
        </div>
        <h3 class="text-3xl font-bold text-gray-800 mb-6">{{ __('Tree Structure') }}</h3>
        <form method="POST" action="{{ route('books.updateOrder') }}">
            @csrf
            <input type="hidden" name="book_id" value="{{ $book->id }}">
            <div class="bg-white p-8 shadow-xl rounded-lg" id="sortable-1">
                @foreach($bookTree as $node)
                    @include('books.partials.tree_node', ['node' => $node])
                @endforeach
            </div>
            <div id="orderInputs"></div> <!-- حقل إدخال مخفي لترتيب العناصر -->
            <button type="submit" class="bg-gradient-to-r from-blue-500 to-teal-400 hover:from-blue-600 hover:to-teal-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg mt-8">{{ __('Update Order') }}</button>
        </form>
    </div>
@endsection



