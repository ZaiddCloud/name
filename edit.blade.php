<!-- resources/views/books/edit.blade.php -->
@extends('layouts.app')

@section('content')
    <h1>تعديل الكتاب</h1>
    <form action="{{ route('books.update', $book->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="title">عنوان الكتاب:</label>
        <input type="text" name="title" id="title" value="{{ $book->title }}" required>

        <label for="science_id">العلم المرتبط:</label>
        <select name="science_id" id="science_id" required>
            @foreach($sciences as $science)
                <option value="{{ $science->id }}" {{ $book->science_id == $science->id ? 'selected' : '' }}>{{ $science->name }}</option>
            @endforeach
        </select>

        <button type="submit">تحديث</button>
    </form>
@endsection
