# stackoverflow-api-client

PHP library to interact with StackOverflow API

## Install

The library depends on an [HTTPlug HTTP client abstraction](http://docs.php-http.org/en/latest/httplug/users.html) If you don't want to get into details, simply requiring the following packages before requiring the library will do:

```bash
composer require php-http/curl-client guzzlehttp/psr7 php-http/message
```

Finally you should end up with the composer.json having something like this:

```json
"require": {
  "php-http/curl-client": "^1.6",
  "guzzlehttp/psr7": "^1.3",
  "php-http/message": "^1.3",
  "andrewkharook/stackoverflow-api-client": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
    	"url": "git@github.com:andrewkharook/stackoverflow-api-client.git"
	}
]
```
