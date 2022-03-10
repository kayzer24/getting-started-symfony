<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;

class PostVoter implements CacheableVoterInterface
{
    public const EDIT = 'POST_EDIT';

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === self::EDIT;
    }

    public function supportsType(string $subjectType): bool
    {
        return $subjectType === Post::class;
    }

    /**
     * @param Post $subject
     */
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        /** @var User|null $user */
        $user = $token->getUser();

        return $user !== null && $subject->getUser() === $user;
    }
}
