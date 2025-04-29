<?php declare(strict_types=1);

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\OIDC\Identities;

class Environment
{
    /**
     * @return string|false
     */
    public function get(string $name)
    {
        if (array_key_exists($name, $_SERVER) && is_scalar($_SERVER[$name])) {
            return (string) $_SERVER[$name];
        }

        if (array_key_exists($name, $_ENV) && is_scalar($_ENV[$name])) {
            return (string) $_ENV[$name];
        }

        return getenv($name);
    }
}
