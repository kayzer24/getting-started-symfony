<?php
namespace App\UseCase\Post\Listing;

final class ListingQuery
{
    public function __construct(public int $page)
    {
    }
}