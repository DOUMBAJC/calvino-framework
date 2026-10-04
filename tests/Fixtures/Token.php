<?php

namespace Tests\Fixtures;

use Calvino\Core\Model;

class Token extends Model
{
    protected string $table = 'tokens';
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected array $fillable = ['label'];
}
