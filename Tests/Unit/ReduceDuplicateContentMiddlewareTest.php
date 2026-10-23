<?php

declare(strict_types=1);

namespace AUS\ReduceDuplicateContent\Tests\Unit;

use AUS\ReduceDuplicateContent\Middleware\ReduceDuplicateContentMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

final class ReduceDuplicateContentMiddlewareTest extends TestCase
{
    public function testPassesThroughRequestsWithoutRouting(): void
    {
        $request = new ServerRequest('https://example.test/page');
        $response = new Response();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->with($request)->willReturn($response);
        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository->expects(self::never())->method('getPage');
        $middleware = new ReduceDuplicateContentMiddleware($this->configuration(), $pageRepository);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHonoursBypassAttribute(): void
    {
        $request = $this->request()->withAttribute('bypassCanonicalRedirect', true);
        $response = new Response();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->with($request)->willReturn($response);
        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository->expects(self::never())->method('getPage');
        $middleware = new ReduceDuplicateContentMiddleware($this->configuration(), $pageRepository);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testPassesThroughPostRequests(): void
    {
        $request = $this->request()->withMethod('POST');
        $response = new Response();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->with($request)->willReturn($response);
        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository->expects(self::never())->method('getPage');
        $middleware = new ReduceDuplicateContentMiddleware($this->configuration(), $pageRepository);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testRedirectsToCanonicalPath(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');
        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository->expects(self::once())->method('getPage')->with(123, true)->willReturn(['uid' => 123]);
        $middleware = new ReduceDuplicateContentMiddleware($this->configuration(), $pageRepository);

        $response = $middleware->process($this->request(), $handler);

        self::assertSame(308, $response->getStatusCode());
        self::assertSame('https://example.test/page/', $response->getHeaderLine('Location'));
        self::assertSame(ReduceDuplicateContentMiddleware::class, $response->getHeaderLine('X-Redirect-Reason'));
    }

    private function configuration(): ExtensionConfiguration
    {
        $configuration = $this->createMock(ExtensionConfiguration::class);
        $configuration->method('get')->willReturnMap([
            ['reduce_duplicate_content', 'bypassRequestAttributes', 'bypassCanonicalRedirect'],
            ['reduce_duplicate_content', 'statusCode', '308'],
        ]);

        return $configuration;
    }

    private function request(): ServerRequest
    {
        $router = $this->createMock(PageRouter::class);
        $router->method('generateUri')->willReturn(new Uri('https://example.test/page/'));
        $site = $this->createMock(Site::class);
        $site->method('getRouter')->willReturn($router);

        return (new ServerRequest('https://example.test/page'))
            ->withAttribute('site', $site)
            ->withAttribute('language', $this->createMock(SiteLanguage::class))
            ->withAttribute('routing', new PageArguments(123, '0', []))
            ->withAttribute('normalizedParams', $this->createMock(NormalizedParams::class));
    }
}
