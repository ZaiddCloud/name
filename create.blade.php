<!-- resources/views/books/create.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>إضافة كتاب جديد</h1>
    <form action="{{ route('books.store') }}" method="POST">
        @csrf
        <label for="title">عنوان الكتاب:</label>
        <input type="text" name="title" id="title" required>

        <label for="science_id">العلم المرتبط:</label>
        <select name="science_id" id="science_id" required>
            @foreach($sciences as $science)
                <option value="{{ $science->id }}">{{ $science->name }}</option>
            @endforeach
        </select>

        <button type="submit">إضافة</button>
    </form>
@endsection
