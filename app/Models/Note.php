<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Override;

class Note extends Model
{
    use Searchable;


    protected $fillable = [
        'user_id',
        'title',
        'content',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'title' => 'string',
        'content' => 'string'
    ];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'content' => $this->content
        ];        
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

