<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginTest extends WebTestCase
{
    public function testShouldLoginSuccess(): void
    {
        $client = static::createClient();

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('security_login'));

        $client->submitForm('Se Connecter', [
            'nickname' => 'user+1',
            'password' => 'password'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->enableProfiler();

        if (($profiler = $client->getProfile()) instanceof Profile) {
            /** @var SecurityDataCollector $securityCollector */
            $securityCollector = $profiler->getCollector('security');
            self::assertTrue($securityCollector->isAuthenticated());
        }

        $client->followRedirect();

        $this->assertRouteSame('post_list');
    }

    /**
     * @dataProvider provideBadData
     * @param array $formData
     * @return void
     */
    public function testShouldShowErrors(array $formData): void
    {
        $client = static::createClient();

        $urlGenerator = $this->getContainer()->get(UrlGeneratorInterface::class);

        $client->request(Request::METHOD_GET, $urlGenerator->generate('security_login'));

        $client->submitForm('Se Connecter', $formData);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->enableProfiler();

        if (($profiler = $client->getProfile()) instanceof Profile) {
            /** @var SecurityDataCollector $securityCollector */
            $securityCollector = $profiler->getCollector('security');
            self::assertFalse($securityCollector->isAuthenticated());
        }

        $client->followRedirect();

        $this->assertRouteSame('security_login');
    }

    public function provideBadData(): \Generator
    {
        yield 'bad nickname' => [self::createFormData(['nickname' => 'fail'])];
        yield 'bad password' => [self::createFormData(['password' => 'fail'])];
    }

    private static function createFormData(array $overrideData = []): array
    {
        return $overrideData + [
            'nickname' => 'user+1',
            'password' => 'password'
        ];
    }
}
