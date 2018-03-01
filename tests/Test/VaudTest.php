<?php

namespace Test;

use YuruYuri\Vaud\Vaud;


class VaudTest extends \TestCase
{
    protected $urls = [
        'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=C1LOz3q4mxHUrZHjte5xn3zzmO1hzMGXyxzeqZjPCZjdt3uXlwn6nhn5lvLJm3m9yJHMAhrYDLffvLnkof9boc9Tyu5LAej4yMrQtf9kwv8Vv29ozenVyuCOAOXPnI1Owc54lM9kl2iYl3runZuYvNzIqY12q3PJDgfbwgP6A2vFng9JoNfWtNjHwLHKzwuWn3vbyvHtEKu/z3fvDhbmsfvPAc5NmxvLngLeq2vSwNjlowfYtJbMyMjiyvDnr3jUDvLWwNLvwMDVsxvNnG#AqSZnZe',
        'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=AhC2mKffANvTl3vvswS3vdDjsZ1ftY9tCvPymNP0thPNC1HOmMTUy3PJugWYywe3p3rWugv4n3bjDOXRCO5ZmOrUngLOr2DfAffMlxHXz3jRzhGYlMWXsNziyKTfAvmUne9NC2Xdy3D5oMTmntzHzM1RyNnNDei1CennqMfKyuH6zg9WmOuYywLLouzZAgjVmJz5v2LMnvn3n2iVzwHyzxqWCJbxuhnbn1LSv3zbBNnhDNaVwwnsmwLRlMvLt3bTDerSvtjqqZHeluzVCa#AqS3nJG',
        'https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=CeHXAgfYufnZDhy3twvZEvfIuZy4Cu03CeDYC1bUovbRrdPNstH1D2SVwhuWzLzyChy5AI94AKSYnZeZy2HRwK4ZDgjTvha3mZflB1yTmuXIlMHmvMqTCZyVtJzRrtfqq3nIDgSZyLHOmevNsgPxlLjywwHtl25boveYouX1uMK/A29wm3rYuJeYowLHngnQrgLKs3nKD2vdzu9hsJLsvNzHlY42t3i4swXineP6mZzbmJjfrhu4zuDvutrVDuv1CfHFtZOVB3vWmtnwyOK3DuCWD2fLnLrJng5cnuK#AqS3odC',
    ];
    protected $uid = 165962770;

    public function testUrls()
    {
        $decoder = new Vaud($this->uid);
        foreach ($this->urls as $url)
        {
            $_url = $decoder->decode($url);
            $this->assertTrue($url, $_url);
        }
    }
}
