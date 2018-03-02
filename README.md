# VK audio url decoder [![Build Status](https://travis-ci.org/yuru-yuri/vk-audio-url-decoder-php.svg?branch=master)](https://travis-ci.org/yuru-yuri/vk-audio-url-decoder-php)

[![GitHub license](https://img.shields.io/github/license/yuru-yuri/vk-audio-url-decoder-php.svg)](https://github.com/yuru-yuri/vk-audio-url-decoder-php/blob/master/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/yuru-yuri/vk-audio-url-decoder-php.svg)](https://github.com/yuru-yuri/vk-audio-url-decoder-php/issues)
[![Packagist](https://img.shields.io/packagist/dt/yuru-yuri/vaud.svg)](https://packagist.org/packages/yuru-yuri/vaud)
[![Packagist](https://img.shields.io/packagist/v/yuru-yuri/vaud.svg)](https://packagist.org/packages/yuru-yuri/vaud)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/symfony/symfony.svg)](https://packagist.org/packages/yuru-yuri/vaud)


[![Maintainability](https://api.codeclimate.com/v1/badges/cec6b6ff469eed15b460/maintainability)](https://codeclimate.com/github/yuru-yuri/vk-audio-url-decoder-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/cec6b6ff469eed15b460/test_coverage)](https://codeclimate.com/github/yuru-yuri/vk-audio-url-decoder-php/test_coverage)


## Usage:

```
composer require yuru-yuri/vaud
```

```php
<?php
use YuruYuri\Vaud\Vaud;

$uid = 1;  // You vk uid
$url = 'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=CeHXAgfYufnZDhy3twvZEvfIuZy4Cu0...#ASS...'; 

$decoder = new Vaud($uid);
$decodedUrl = $decoder->decode($url);

$decodedUrl === 'https://cs1-23v1.vkuseraudio.net/p1/ae1240a98cf.mp3?extra=XZ...';

```
