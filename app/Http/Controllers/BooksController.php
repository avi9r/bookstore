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

    public function getBooks(Request $request)
    {
        $books = Books::paginate($request->itemPerPage)->all();
        return response()->json($books);
    }

    public function show($id)
    {
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        return response()->json($book);
    }

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

        return response()->json($book);
    }

    public function destroy($id)
    {
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }

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
    
        ]);
        $order = Order::create([
            'book_id' => $request->input('book_id'),
            'quantity' => $request->input('quantity'),
    
        ]);
    

        $book = Book::findOrFail($request->input('book_id'));
        $book->decrement('quantity', $request->input('quantity'));
        

        SendOrderConfirmationEmail::dispatch($order)->onQueue('emails');
        

        return response()->json(['message' => 'Order placed successfully']);

    }
}

