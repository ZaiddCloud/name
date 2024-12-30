<?php

namespace App\Http\Controllers;

use App\Models\Head;
use App\Models\Science;
use App\Services\BookService;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function index()
    {
        $books = $this->bookService->getAllBooks();
        return view('books.index', compact('books'));
    }

    public function create()
    {
        $sciences = Science::all();
        return view('books.create', compact('sciences'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'science_id' => 'required|exists:sciences,id',
        ]);

        $this->bookService->createBook($request->all());
        return redirect()->route('books.index')->with('success', 'Book created successfully!');
    }

    public function show(Book $book)
    {
        $bookDetails = $this->bookService->getBookDetailsWithTree($book);
        $book = $bookDetails['book'];
        $bookTree = $bookDetails['tree'];
        return view('books.show', compact('book', 'bookTree'));
    }

    public function edit($id)
    {
        $book = Book::with('science')->findOrFail($id);
        $sciences = Science::all();
        return view('books.edit', compact('book', 'sciences'));
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $this->bookService->updateBook($book, $request->all());
        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $this->bookService->deleteBook($book);
        return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
    }


    public function updateOrder(Request $request)
    {
        $orders = $request->input('orders');
    
        // تسجيل البيانات المرسلة
        Log::info('Orders:', $orders);
    
        // التحقق من أن $orders ليس فارغًا وأنه مصفوفة
        if (!is_array($orders)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
        }
    
        foreach ($orders as $order) {
            Head::where('id', $order['id'])->update(['order' => $order['order']]);
        }
    
        return response()->json(['status' => 'success']);
    }
    


     
    
    // public function updateOrder(Request $request)
    
     // {
    
    //     $orders = $request->input('orders');
    
        
    
    //     foreach ($orders as $order) {
    
    //         Head::where('id', $order['id'])->update(['order' => $order['order']]);
    
    //     }
    
        
    
    //     return response()->json(['status' => 'success']);
    
    //     }
    
}
