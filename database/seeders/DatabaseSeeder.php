<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Books;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $response = Http::get('https://fakerapi.it/api/v1/books?_quantity=100')->json();

        foreach ($response['data'] as $bookData) {
            Books::create([
                'id' =>$bookData['id'],
                'title' => $bookData['title'],
                'author' => $bookData['author'],
                'genre' => $bookData['genre'],
                'description' =>$bookData['description'],
                'isbn' => $bookData['isbn'],
                'image'=>$bookData['image'], 
                'published' => $bookData['published'],
                'publisher' =>$bookData['publisher'],
            ]);
        }
    }
}
