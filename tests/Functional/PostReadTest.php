<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostReadTest extends WebTestCase
{
    public function testReadPost(): void
    {
        $client = static::createClient();

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request('GET', $urlGenerator->generate('post_read', ['id' => 1]));

        $this->assertResponseIsSuccessful();
    }
}
