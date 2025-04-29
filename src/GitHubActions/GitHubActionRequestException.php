<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities\GitHubActions;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class GitHubActionRequestException extends \RuntimeException
{
    public static function fromClientException(ClientExceptionInterface $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(sprintf('GitHub Actions: OIDC token request failed with status code %s and body %s', $response->getStatusCode(), $response->getBody()), $response->getStatusCode());
    }
}
