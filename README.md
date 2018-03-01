# VK audio url decoder

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
