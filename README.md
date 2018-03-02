# VK audio url decoder [![Build Status](https://travis-ci.org/yuru-yuri/vk-audio-url-decoder-php.svg?branch=master)](https://travis-ci.org/yuru-yuri/vk-audio-url-decoder-php)

[![GitHub license](https://img.shields.io/github/license/yuru-yuri/vk-audio-url-decoder-php.svg)](https://github.com/yuru-yuri/vk-audio-url-decoder-php/blob/master/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/yuru-yuri/vk-audio-url-decoder-php.svg)](https://github.com/yuru-yuri/vk-audio-url-decoder-php/issues)
[![Packagist](https://img.shields.io/packagist/dt/yuru-yuri/vaud.svg)](https://packagist.org/packages/yuru-yuri/vaud)

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
