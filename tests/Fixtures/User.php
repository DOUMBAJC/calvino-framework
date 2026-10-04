<?php

namespace Tests\Fixtures;

use Calvino\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'role'];

    public function posts(): array
    {
        return $this->hasMany(Post::class);
    }
}
