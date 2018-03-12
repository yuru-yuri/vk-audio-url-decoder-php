<?php

namespace Test;

use YuruYuri\Vaud\Decoder;


class Protect2Public extends Decoder
{
    public function v($e)
    { 
        return parent::v($e);
    }

    public function r($e, $t)
    {
        return parent::r($e, $t);
    }

    public function x($e, $t)
    {
        return parent::x($e, $t);
    }
}


class DecoderTest extends \TestCase
{
    protected $urls = [
        'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=C1LOz3q4mxHUrZHjte5xn3zzmO1hzMGXyxzeqZjPCZjdt3uXlwn6nhn5lvLJm3m9yJHMAhrYDLffvLnkof9boc9Tyu5LAej4yMrQtf9kwv8Vv29ozenVyuCOAOXPnI1Owc54lM9kl2iYl3runZuYvNzIqY12q3PJDgfbwgP6A2vFng9JoNfWtNjHwLHKzwuWn3vbyvHtEKu/z3fvDhbmsfvPAc5NmxvLngLeq2vSwNjlowfYtJbMyMjiyvDnr3jUDvLWwNLvwMDVsxvNnG#AqSZnZe',
        'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=AhC2mKffANvTl3vvswS3vdDjsZ1ftY9tCvPymNP0thPNC1HOmMTUy3PJugWYywe3p3rWugv4n3bjDOXRCO5ZmOrUngLOr2DfAffMlxHXz3jRzhGYlMWXsNziyKTfAvmUne9NC2Xdy3D5oMTmntzHzM1RyNnNDei1CennqMfKyuH6zg9WmOuYywLLouzZAgjVmJz5v2LMnvn3n2iVzwHyzxqWCJbxuhnbn1LSv3zbBNnhDNaVwwnsmwLRlMvLt3bTDerSvtjqqZHeluzVCa#AqS3nJG',
        'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=CeHXAgfYufnZDhy3twvZEvfIuZy4Cu03CeDYC1bUovbRrdPNstH1D2SVwhuWzLzyChy5AI94AKSYnZeZy2HRwK4ZDgjTvha3mZflB1yTmuXIlMHmvMqTCZyVtJzRrtfqq3nIDgSZyLHOmevNsgPxlLjywwHtl25boveYouX1uMK/A29wm3rYuJeYowLHngnQrgLKs3nKD2vdzu9hsJLsvNzHlY42t3i4swXineP6mZzbmJjfrhu4zuDvutrVDuv1CfHFtZOVB3vWmtnwyOK3DuCWD2fLnLrJng5cnuK#AqS3odC',
    ];
    protected $uid = 165962770;

    public function testUrls()
    {
        $decoder = new Decoder($this->uid);
        foreach ($this->urls as $url)
        {
            $_url = $decoder->decode($url);
            $this->assertFalse($url === $_url);
        }
    }

    public function test_url1()
    {
        $decoder = new Decoder($this->uid);
        $decoded_url = $decoder->decode($this->urls[0] . 'abcdef');  # O_o
        $this->assertFalse(false === \strpos($decoded_url, 'audio_api_unavailable'));
    }

    public function test_url2()
    {
        $decoder = new Decoder($this->uid);

        $result = false;
        try
        {
            $decoder->decode(\substr($this->urls[0], 0, -5));
        }
        catch (\ArgumentCountError $e) {
            $result = true;
        }

        $this->assertTrue($result);
    }

    public function test_attr1()
    {
        $result = false;
        try
        {
            new Decoder(0);
        }
        catch (\InvalidArgumentException $e) {
            $result = true;
        }

        $this->assertTrue($result);
    }

    public function test_r()
    {
        $decoder = new Protect2Public(1);
        $this->assertTrue('Y++69:PP6R9+VSZ4.T53P' === $decoder->r('https://pastebin.com/', 22));
    }

    public function test_v()
    {
        $decoder = new Protect2Public(1);
        $this->assertTrue('abc' === $decoder->v('cba'));
    }

    public function test_x()
    {
        $decoder = new Protect2Public(1);
        $p = $decoder->x('https://pastebin.com/', '22');
        $e = base64_decode('WkZGQkEIHR1CU0FGV1BbXBxRXV8d');
        $this->assertTrue($p === $e);
    }
}
