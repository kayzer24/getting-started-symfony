<?php

namespace App\Tests\Functional;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class PostDeleteTest extends WebTestCase
{
    public function testDeletePost(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);

        /** @var User|null $user */
        $user = $userRepository->find(1);

        $client->loginUser($user);

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request('GET', $urlGenerator->generate('post_read', ['id' => 1]));

        $this->assertResponseIsSuccessful();

        $client->submitForm('Supprimer');

        $client->followRedirect();

        $this->assertRouteSame('post_list');

        /** @var PostRepository $postRepository */
        $postRepository = $client->getContainer()->get(PostRepository::class);

        /** @var Post|null $post */
        $post = $postRepository->find(1);

        $this->assertNull($post);
    }
}
