<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    use HasFactory;
    protected $table = 'books';

    protected $fillable = [
        'title', 'author', 'genre', 'description', 'isbn', 'image', 'published', 'publisher'
    ];
}
