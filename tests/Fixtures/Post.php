<?php

namespace Tests\Fixtures;

use Calvino\Core\Model;

class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['user_id', 'title'];

    public function user(): ?User
    {
        return $this->belongsTo(User::class);
    }
}
