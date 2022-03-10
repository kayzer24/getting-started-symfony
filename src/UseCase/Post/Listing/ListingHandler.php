<?php

namespace App\UseCase\Post\Listing;

use App\Messenger\QueryHandlerInterface;
use App\Repository\PostRepository;

class ListingHandler implements QueryHandlerInterface
{
    public function __construct(private PostRepository $postRepository)
    {
    }

    public function __invoke(ListingQuery $query): Listing
    {
        return new Listing(
            $this->postRepository->getPaginatedPosts($query->page),
            $query->page
        );
    }
}