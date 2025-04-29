<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities;

use PrivatePackagist\OIDC\Identities\GitHubActions\GitHubActionsInvalidTokenException;

/**
 * @readonly
 */
final class Token
{
    /** @var string */
    public $token;
    /** @var string */
    public $header;
    /** @var string */
    public $payload;
    /** @var string */
    public $signature;

    /**
     * @param string $token
     * @param string $header
     * @param string $payload
     * @param string $signature
     */
    private function __construct($token, $header, $payload, $signature)
    {
        $this->signature = $signature;
        $this->payload = $payload;
        $this->header = $header;
        $this->token = $token;
    }

    public static function fromTokenString(string $token): self
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new GitHubActionsInvalidTokenException();
        }

        $header = base64_decode($parts[0], true);
        $payload = base64_decode($parts[1], true);
        if (false === $header || false === $payload) {
            throw new GitHubActionsInvalidTokenException($parts[0] . '==' . $parts[1]);
        }

        return new self($token, $header, $payload, $parts[2]);
    }
}
