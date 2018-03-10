<?php

namespace Test;

use YuruYuri\Vaud\AlAudio;
use YuruYuri\Vaud\Vaud;


class MockAlAudio extends AlAudio
{
    public $sleep_time = 0;
    public $debug = false;

    protected function post(string $url, array $data = []): string
    {
        $_path = sprintf('%s/data/%s_%s.txt',
            dirname(__DIR__),
            $data['act'] ?? '---',
            $data['offset'] ?? $data['ids'] ?? '---'
        );

        if (is_file($_path))
        {
            return file_get_contents($_path);
        }

        return parent::post($url, $data);
    }
}


class AlAudioTest extends \TestCase
{
    protected $uid = 165962770;
    protected $cookies = [];

    public function testVk()
    {
        $aa = new MockAlAudio($this->uid, $this->cookies);
        $items = $aa->main();

        $this->assertTrue(count($items) > 100);
        $this->assertTrue(is_array(end($items)));
        $this->assertTrue(false !== strpos(end($items)[0], 'audio_api_unavailable'));
    }

    public function testDecodeItem()
    {
        $aa = new MockAlAudio($this->uid, $this->cookies);
        $items = $aa->main();

        $decoder = new Vaud($this->uid);

        $this->assertTrue(false === strpos($decoder->decode(current($items)[0]), 'audio_api_unavailable'));
        $this->assertTrue(false === strpos($decoder->decode(end($items)[0]), 'audio_api_unavailable'));
    }
}
