<?php

namespace Test;

use YuruYuri\Vaud\AlAudio;
use YuruYuri\Vaud\Decoder;
use YuruYuri\Vaud\Vaud;


class MockAlAudio extends AlAudio
{
    public $sleepTime = 0;

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

    public function getPlaylist(): array
    {
        return $this->playlist;
    }
}


class AlAudioTest extends \PHPUnit\Framework\TestCase
{
    protected $uid = 253093876;
    protected $cookies = [
        'cookie_key=cookie_value',
        'cookie_key_a' => 'cookie_value_a',
    ];

    public function testVk()
    {
        $aa = new MockAlAudio($this->uid, $this->cookies);
        $items = $aa->main();

        $this->assertSame(\count($aa->getPlaylist()), \count($items) + \count($aa->getUnParsedTracks()));
        $this->assertInternalType('array', end($items));
        $this->assertNotFalse(strpos(end($items)[0], 'audio_api_unavailable'));
    }

    public function testDecodeItem()
    {
        $aa = new MockAlAudio($this->uid, $this->cookies);
        $aa->setLimitOffset(90, 0);
        $items = $aa->main();

        $decoder = new Decoder($this->uid);

        $this->assertFalse(strpos($decoder->decode(current($items)['url']), 'audio_api_unavailable'));
        $this->assertFalse(strpos($decoder->decode(end($items)['url']), 'audio_api_unavailable'));
    }

    public function testLimitOffset()
    {
        $aa = new MockAlAudio($this->uid, $this->cookies);
        $offset = 6;
        $aa->setLimitOffset(20, $offset);
        $items = $aa->main();

        $this->assertCount(20, $items);

        $aa = new MockAlAudio($this->uid, $this->cookies);
        $aa->setLimitOffset(30);
        $itemsWithoutOffset = $aa->main();

        $this->assertSame($items[0]['id'], $itemsWithoutOffset[$offset]['id']);
    }

    public function testInstanceVoid()
    {
        $vaud = new Vaud(1);
        $this->assertInstanceOf(Decoder::class, $vaud);
    }

    public function testCallback()
    {
        $data = [];
        $aa = new MockAlAudio($this->uid, $this->cookies);
        $aa->setDebugCallback(function (...$a) use (&$data)
        {
            $data[] = $a;
        });
        $aa->main();

        $this->assertTrue(\count($data) > 0);
    }

}
