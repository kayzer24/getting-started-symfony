<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\UseCase\Post\Create\CreateCommand;
use App\UseCase\Post\Create\CreateHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;

class CreatePostTest extends TestCase
{
    public function testShouldReturnNewPost(): void
    {
        $imageFile = $this->createMock(UploadedFile::class);
        $imageFile->method('getClientOriginalExtension')->willReturn('png');

        $post = new Post();
        $post->setTitle('Title');
        $post->setContent('Content');
        $post->setImageFile($imageFile);
        $post->setCategory(new Category());
        $post->getTags()->add(new Tag());

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($post));

        $entityManager->expects($this->once())
            ->method('flush');

        $security = $this->createMock(Security::class);

        $user = new User();

        $security->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $handler = new CreateHandler($entityManager, '', $security);

        $command = new CreateCommand($post);

        $post = $handler($command);

        $this->assertMatchesRegularExpression('/^[a-z0-9-]+.png$/', $post->getImage());
        $this->assertEquals($user, $post->getUser());
    }
}
