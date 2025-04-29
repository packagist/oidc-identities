<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities\GitHubActions;

use Http\Client\Common\HttpMethodsClient;
use PrivatePackagist\OIDC\Identities\Environment;
use PrivatePackagist\OIDC\Identities\Token;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

class GitHubActionsIdentity
{
    /** @var LoggerInterface */
    private $logger;
    /** @var Environment */
    private $environment;
    /** @var HttpMethodsClient  */
    private $httpClient;

    public function __construct(
        LoggerInterface $logger,
        Environment $environment,
        HttpMethodsClient $httpClient
    ) {
        $this->logger = $logger;
        $this->environment = $environment;
        $this->httpClient = $httpClient;
    }

    public function supports(): bool
    {
        if (false === (bool) $this->environment->get('GITHUB_ACTIONS')) {
            $this->logger->debug("GitHub Actions: environment variable not found, skipping");
            return false;
        }

        return true;
    }

    /**
     * GitHub Actions require a GET request to a URL with a bearer token that are available as environment variables
     * if the workflow has sufficient permissions.
     */
    public function getToken(string $audience): Token
    {
        $requestToken = $this->environment->get("ACTIONS_ID_TOKEN_REQUEST_TOKEN");
        if (false === (bool) $requestToken) {
            throw new GitHubActionsPermissionException('ACTIONS_ID_TOKEN_REQUEST_TOKEN');
        }

        $requestUrl = $this->environment->get("ACTIONS_ID_TOKEN_REQUEST_URL");
        if (false === (bool) $requestUrl) {
            throw new GitHubActionsPermissionException('ACTIONS_ID_TOKEN_REQUEST_URL');
        }

        $this->logger->debug("GitHub Actions: requesting OIDC token");

        try {
            $response = $this->httpClient->get($requestUrl . '&audience=' . $audience, ['Authorization' => 'Bearer ' . $requestToken]);
        } catch (ClientExceptionInterface $e) {
            throw GitHubActionRequestException::fromClientException($e);
        }

        if ($response->getStatusCode() !== 200) {
            throw GitHubActionRequestException::fromResponse($response);
        }

        $decoded = json_decode((string) $response->getBody(), true);
        if (false === $decoded) {
            throw new GitHubActionsInvalidTokenException('GitHub Actions: invalid JSON received', 0);
        }

        if (!is_array($decoded) || !isset($decoded['value']) || !is_string($decoded['value'])) {
            throw new GitHubActionsInvalidTokenException('GitHub Actions: OIDC token is invalid');
        }

        $this->logger->debug("GitHub Actions: OIDC token request was successful");

        return Token::fromTokenString($decoded['value']);
    }
}
