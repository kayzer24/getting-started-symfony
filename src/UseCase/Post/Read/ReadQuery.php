<?php
namespace App\UseCase\Post\Read;

final class ReadQuery
{
    public function __construct(public int $id)
    {
    }
}