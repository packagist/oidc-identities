<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities\GitHubActions;

use Http\Client\Common\HttpMethodsClient;
use Http\Mock\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PrivatePackagist\OIDC\Identities\Environment;
use Psr\Log\NullLogger;

class GitHubActionsIdentityTest extends TestCase
{
    /** @var GitHubActionsIdentity */
    private $gitHubActions;
    /** @var Client  */
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gitHubActions = new GitHubActionsIdentity(
            new NullLogger(),
            new Environment(),
            new HttpMethodsClient($this->httpClient = new Client(), new Psr17Factory())
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (['GITHUB_ACTIONS', 'ACTIONS_ID_TOKEN_REQUEST_TOKEN', 'ACTIONS_ID_TOKEN_REQUEST_URL'] as $env) {
            unset($_SERVER[$env]);
        }
    }

    /**
     * @dataProvider supportedProvider
     */
    public function testSupported(?string $env, bool $expected): void
    {
        $_SERVER['GITHUB_ACTIONS'] = $env;

        self::assertSame($expected, $this->gitHubActions->supports());
    }

    /**
     * @return list<array{0: string, 1: bool}>
     */
    public static function supportedProvider(): array
    {
        return [
            ['1', true],
            ['0', false],
            ['', false],
        ];
    }

    public function testGetToken(): void
    {
        $_SERVER['ACTIONS_ID_TOKEN_REQUEST_TOKEN'] = 'token';
        $_SERVER['ACTIONS_ID_TOKEN_REQUEST_URL'] = 'https://example.org';

        $this->httpClient->addResponse(new Response(200, [], (string) json_encode(['value' => implode('.', [
            base64_encode('header'),
            base64_encode('payload'),
            'signature'
        ])])));

        $token = $this->gitHubActions->getToken('audience');

        self::assertSame('header', $token->header);
        self::assertSame('payload', $token->payload);
        self::assertSame('signature', $token->signature);
    }

    public function testRequestTokenMissing(): void
    {
        self::expectException(GitHubActionsPermissionException::class);
        self::expectExceptionMessageMatches('{ACTIONS_ID_TOKEN_REQUEST_TOKEN}');

        $this->gitHubActions->getToken('audience');
    }

    public function testRequestUrlMissing(): void
    {
        $_SERVER['ACTIONS_ID_TOKEN_REQUEST_TOKEN'] = 'token';

        self::expectException(GitHubActionsPermissionException::class);
        self::expectExceptionMessageMatches('{ACTIONS_ID_TOKEN_REQUEST_URL}');

        $this->gitHubActions->getToken('audience');
    }
}
