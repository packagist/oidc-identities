# packagist/publish-artifact-github-action

GitHub Action to publish artifacts as package versions to Private Packagist.

## Requirements

PHP >= 7.2

## Install

Via Composer:
```bash
$ composer require private-packagist/oidc-identities
```

## Usage

Initiate a `TokenGenerator` instance and call the `generate` method with `$audience`.
The `TokenGenerator` will automatically try all supported platforms.

```php
// Configure a HttpMethodsClient instance
$oidcHttpClient = new HttpMethodsClient(
    Psr18ClientDiscovery::find(),
    Psr17FactoryDiscovery::findRequestFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
);

$tokenGenerator = new TokenGenerator(new NullLogger(), $oidcHttpClient);
$token = $tokenGenerator->generate($audience);
```

## Copyright and License

The  GitHub Action is licensed under the MIT License.
