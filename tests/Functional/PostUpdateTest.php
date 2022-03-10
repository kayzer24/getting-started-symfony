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

class PostUpdateTest extends WebTestCase
{
    public function testUpdatePost(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);

        /** @var User|null $user */
        $user = $userRepository->find(1);

        $client->loginUser($user);

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('post_update', ['id' => 1]));

        $client->submitForm('Modifier', self::updateFormData());

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('post_read', ['id' => 1]);

        /** @var PostRepository $postRepository */
        $postRepository = $client->getContainer()->get(PostRepository::class);

        /** @var Post|null $post */
        $post = $postRepository->find(1);

        $this->assertNotNull($post);
        $this->assertEquals('Title', $post->getTitle());
        $this->assertEquals('Content', $post->getContent());
        $this->assertCount(2, $post->getTags());
    }

    public function testRedirectToLogin(): void
    {
        $client = static::createClient();

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('post_update', ['id' => 1]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('security_login');
    }

    public function testShouldRaise403(): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);

        /** @var User|null $user */
        $user = $userRepository->find(1);

        $client->loginUser($user);

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('post_update', ['id' => 125]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    /**
     * @dataProvider provideBadData
     * @param array $formData
     * @return void
     */
    public function testShowShowErrors(array $formData): void
    {
        $client = static::createClient();

        /** @var UserRepository $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);

        /** @var User|null $user */
        $user = $userRepository->find(1);

        $client->loginUser($user);

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('post_update', ['id' => 1]));

        $client->submitForm('Modifier', $formData);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    private static function updateFormData(array $overrideData = []): array
    {
        $originalFile = __DIR__ . '/../../public/uploads/image.png';

        $filename = sprintf('%s.png', Uuid::v4());

        $finalFile = sprintf('%s/../../public/uploads/%s', __DIR__, $filename);

        copy($originalFile, $finalFile);

        return $overrideData + [
                'post[title]' => 'Title',
                'post[category]' => 1,
                'post[content]' => 'Content',
                'post[tags]' => 'foo, bar',
                'post[imageFile]' => new UploadedFile($finalFile, $filename, 'image/png', null, true)
            ];
    }

    public function provideBadData(): \Generator
    {
        yield 'empty title' => [self::updateFormData(['post[title]' => ''])];
        yield 'empty content' => [self::updateFormData(['post[content]' => ''])];
        yield 'empty tags' => [self::updateFormData(['post[tags]' => ''])];
    }
}
