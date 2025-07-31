<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities;

use Http\Client\Common\HttpMethodsClient;
use PrivatePackagist\OIDC\Identities\GitHubActions\GitHubActionsIdentity;
use Psr\Log\LoggerInterface;

final class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @var list<GitHubActionsIdentity>
     */
    private $identities = [];

    public function __construct(LoggerInterface $logger, HttpMethodsClient $httpClient)
    {
        $environment = new Environment();
        $this->identities[] = new GitHubActionsIdentity($logger, $environment, $httpClient);
    }

    public function generate(string $audience): ?Token
    {
        foreach ($this->identities as $identity) {
            if ($identity->supports()) {
                return $identity->getToken($audience);
            }
        }

        return null;
    }
}
