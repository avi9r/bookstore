<?php

namespace App\Http\Controllers;

use App\Models\Books;
use Illuminate\Http\Request;
use App\Jobs\SendOrderConfirmationEmail;
use App\Services\ElasticsearchService;
use Illuminate\Support\Facades\Storage;


class BooksController extends Controller
{
    private $elasticsearchService;
    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }
    // Create a new book
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'genre' => 'required|string',
            'description' => 'required|string',
            'isbn' => 'required|string|unique:books',
            'image'=> 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'published' => 'required|date',
            'publisher' => 'required|string',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            Storage::disk('public')->put($imageName, file_get_contents($image));

            $book = Books::create([
                'title' => $request->title,
                'author' => $request->author,
                'genre' => $request->genre,
                'description' => $request->description,
                'isbn' => $request->isbn,
                'published' => $request->published,
                'publisher' => $request->publisher,
                'image' => asset('storage/' . $imageName), 
            ]);

            return response()->json($book, 201); 
        }

        return response()->json(['error' => 'Image upload failed'], 500);
    }

    // Get a list of books
    public function getBooks()
    {
        $books = Books::all();
        return response()->json($books);
    }

    // Get a specific book by ID
    public function show($id)
    {
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        return response()->json($book);
    }

    // Update a book by ID
    public function update(Request $request, $id)
    {
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $data = $request->validate([
            'title' => 'string',
            'author' => 'string',
            'genre' => 'string',
            'description' => 'string',
            'isbn' => 'unique:books,isbn,' . $book->id,
            'published' => 'date',
            'publisher' => 'string',
        ]);
        $book->update($data);
        if ($request->hasFile('image')) {

            $request->validate([
            'image'=> 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('public')->put($imageName, file_get_contents($image));
            Books::where('id', $id)->update(['image' => asset('storage/' . $imageName)]);
            return response()->json($book);
        }
        // $book->update($data);
        return response()->json($book);
    }

    // Delete a book by ID
    public function destroy($id)
    {
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }

    // public function getBooksFilter(Request $request)
    // {
    //     $query = Book::query();

    //     // Filter by title
    //     if ($request->has('title')) {
    //         $query->where('title', 'like', '%' . $request->input('title') . '%');
    //     }

    //     // Filter by author
    //     if ($request->has('author')) {
    //         $query->where('author', 'like', '%' . $request->input('author') . '%');
    //     }

    //     // Filter by publication date
    //     if ($request->has('published')) {
    //         $query->whereDate('published', $request->input('published'));
    //     }

    //     // Filter by ISBN
    //     if ($request->has('isbn')) {
    //         $query->where('isbn', 'like', '%' . $request->input('isbn') . '%');
    //     }

    //     // Filter by genre
    //     if ($request->has('genre')) {
    //         $query->where('genre', 'like', '%' . $request->input('genre') . '%');
    //     }

    //     // Paginate the results
    //     $books = $query->paginate($request->input('per_page', 10));

    //     return response()->json($books);
    // }
    public function search(Request $request)
    {
        $params = [
            'query' => $request->input('query'),
            'filter' => $request->input('filter'),
        ];
        $results = $this->elasticsearchService->searchBooks($params);

        return response()->json($results);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            // Add more validation rules as needed
        ]);
        $order = Order::create([
            'book_id' => $request->input('book_id'),
            'quantity' => $request->input('quantity'),
            // Add more order details as needed
        ]);
    
        // Update book quantity (if applicable)
        $book = Book::findOrFail($request->input('book_id'));
        $book->decrement('quantity', $request->input('quantity'));
        
        // Dispatch the job to send the order confirmation email
        SendOrderConfirmationEmail::dispatch($order)->onQueue('emails');
        
        // Return response or redirect
        return response()->json(['message' => 'Order placed successfully']);

    }
}

