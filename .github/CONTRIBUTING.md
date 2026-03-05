# Contributing

Contributions are welcome and will be fully credited.

## Pull Requests

- **[PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)** - The easiest way to apply the conventions is to run `vendor/bin/pint`.
- **Add tests** - Your patch won't be accepted if it doesn't have tests.
- **Document any change in behaviour** - Make sure the `README.md` is kept up-to-date.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Branch Strategy

| Branch | Filament | Package Version |
|--------|----------|-----------------|
| `1.x`  | ^3.0     | ^1.0            |
| `2.x`  | ^4.0     | ^2.0            |
| `3.x`  | ^5.0     | ^3.0            |

Please send your pull request to the correct branch based on the Filament version you are targeting.

## Running Tests

```bash
composer install
vendor/bin/pest
```
