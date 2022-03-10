<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostListTest extends WebTestCase
{
    public function testListPaginatedPost(): void
    {
        $client = static::createClient();

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $crawler = $client->request('GET', $urlGenerator->generate('post_list'));

        $this->assertResponseIsSuccessful();

        $this->assertCount(18, $crawler->filter('article'));

        $client->clickLink(2, $client->getRequest()->query->getInt('page'));

        $this->assertResponseIsSuccessful();

        $this->assertCount(18, $crawler->filter('article'));
    }
}
