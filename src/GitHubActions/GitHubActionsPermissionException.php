<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities\GitHubActions;

class GitHubActionsPermissionException extends \RuntimeException
{
    public function __construct(string $missingEnvironmentVariable)
    {
        parent::__construct(sprintf("GitHub Actions: missing or insufficient OIDC token permissions, the '%s' environment variable was not found", $missingEnvironmentVariable));
    }
}
