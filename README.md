# WordPress Test Suite

[![Build Status](https://travis-ci.org/wpup/test-suite.svg?branch=master)](https://travis-ci.org/wpup/test-suite)

Instead of adding a `boostrap.php` to every plugin you do you can just install this package and point to the `bootstrap.php`.
For small plugins this should be enough. If you require something more advanced this isn't the package for your project.

# Install

```
$ composer require frozzare/wp-test-suite
```

## Example

Example `.travis.yml`:

```yaml
language: php

php:
  - 5.4
  - 5.5
  - 5.6

env:
  - WP_VERSION=latest WP_MULTISITE=0

install:
  - travis_retry composer install --no-interaction --prefer-source

before_script:
  - bash vendor/frozzare/wp-test-suite/bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 $WP_VERSION

script: vendor/bin/phpunit
```

Example `phpunit.xml.dist`:

```xml
<phpunit
  bootstrap="vendor/frozzare/wp-test-suite/bootstrap.php"
  backupGlobals="false"
  colors="true"
  convertErrorsToExceptions="true"
  convertNoticesToExceptions="true"
  convertWarningsToExceptions="true"
  >
  <php>
    <const name="WTS_PLUGIN_FILE_NAME" value="simple-gtm.php" />
  </php>
  <testsuites>
    <testsuite name="Simple GTM Test Suite">
      <directory suffix=".php">./tests/</directory>
    </testsuite>
  </testsuites>
  <logging>
    <log type="coverage-clover" target="./tmp/clover.xml" charset="UTF-8" />
  </logging>
</phpunit>
```

Example of using your own `tests/boostrap.php` with WordPress Test Suite:

```php
require __DIR__ . '/../vendor/autoload.php';

WP_Test_Suite::load_plugins( [
  __DIR__ . '/../simple-gtm.php'
] );

WP_Test_Suite::run();
```
## Documentation

#### Without a bootstrap.php

#### install-wp-tests.sh

This package include `install-wp-tests.sh` and the path is:

```
vendor/frozzare/wp-test-suite/bin/install-wp-tests.sh
```

##### WTS_PLUGIN_FILE_NAME

With this constant you don't have to create your own `boostrap.php`. Just include
`WTS_PLUGIN_FILE_NAME` const in your `phpunit.xml.dist` with the plugin file name
that should be loaded.

#### With a bootstrap.php

##### WP_Test_Suite::load_plugins

A array of plugin paths or a string of a plugin path to load.

##### WP_Test_Suite::load_files

A array of file paths or a string of a file path to load.

##### WP_Test_Suite::set_test_root

Set a new test root path. It will try to autoload from:
- `WP_DEVELOP_DIR/tests/phpunit`
- `/tmp/wordpress-develop/tests/phpunit`
- `/tmp/wordpress-tests-lib`
- `/srv/www/wordpress-develop/tests/phpunit`
- `/srv/www/wordpress-develop/public_html/tests/phpunit`

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)
