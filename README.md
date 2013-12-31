# protoc-gen-php

protoc php generator plugin .

# Usage

````
composer install
# this will link composer/bin/protoc-gen-php to /usr/local/bin/protoc-gen-php
protoc --php_out=<output_directory> -I. person.proto
# you can also specify it..
# protoc --plugin=composer/bin/protoc-gen-php --php_out=<output_directory> -I. person.proto
````

## Features

### Yaml based insertion

protoc has comment based `insertion point` mechanism. it's very usefull to customize generated message.
but you need to write custom plugin when using that.
protoc-gen-php has pragmatic feature which will check `.protoc.php.yml` and insert contents when matched.

see https://github.com/chobie/protoc-gen-php/blob/master/proto/.protoc.php.yml

### Environments

you can override protoc-gen-php behavior with environemnts.

| key        | description                                                | example          |
+------------+------------------------------------------------------------+------------------+
| PACKAGE    | over ride package name. expects dot delimited package name | PACKAGE=chobie.io|
| PEAR_STYLE | don't use namespace.                                       | PEAR_STYLE=1     |

## requirements

* [php-protocolbuffers](https://github.com/chobie/php-protocolbuffers)

## License

new BSD License