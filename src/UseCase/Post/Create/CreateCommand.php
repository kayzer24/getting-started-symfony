<?php

namespace App\UseCase\Post\Create;

use App\Entity\Post;

final class CreateCommand
{
    public function __construct(public Post $post)
    {
    }
}