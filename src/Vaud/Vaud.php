<?php

namespace YuruYuri\Vaud;


class Vaud
{
    protected $uid = null;
    private $n = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMN0PQRSTUVWXYZO123456789+/=';

    public function __construct(int $uid)
    {
        $this->uid = $uid;
    }

    public function decode(string $url): string
    {
        if (!$this->uid)
        {
            throw new \InvalidArgumentException('Not received (int)uid');
        }

        if (strpos($url, 'audio_api_unavailable') !== false)
        {
            $t = explode('#', explode('?extra=', $url)[1]);
            $n = $t[1] == '' ? '' : $this->decode_r($t[1]);
            $t = $this->decode_r($t[0]);
            if (!$t || !is_string($t)) {
                var_dump(__LINE__);
                return $url;
            }
            $n = $n ? explode(chr(9), $n) : [];

            $n_len = count($n);
            while ($n_len) {
                $n_len -= 1;
                $s = explode(chr(11), $n[$n_len]);
                $s = $this->splice($s, 0, 1, $t);
                $a = $s[0][0];
                $s = $s[1];
                if (!method_exists($this, $a)) {
                    var_dump(__LINE__);
                    return $url;
                }
                $t = $this->{$a}(...$s);
            }

            if(substr($t, 0, 4) == 'http')
            {
                return $t;
            }
            var_dump(__LINE__);
        }
        var_dump(__LINE__);

        return $url;
    }

    protected function splice($a, $b, $c, ...$d)
    {
        if (is_array($b)) {
            return $this->splice($a, $b[0], $b[1], array_merge([$c] + $d));
        }

        $cash = $a;
        $a = array_slice($a, $b, $c);
        if (count($d))
        {
            $cash = array_merge(
                array_slice($cash, 0, $b),
                $d,
                array_slice($cash, $c + $b)
            );
        }
        else
        {
            $cash = array_merge(
                array_slice($cash, 0, $b),
                array_slice($cash, $c + $b)
            );
        }

        return [$a, $cash];
    }

    protected function decode_s($e, $t)
    {
        $e_len = strlen($e);
        $i = [];
        if ($e_len) {
            $o = $e_len;
            $t = abs($t);
            while ($o) {
                $o -= 1;
                $t = ($e_len * ($o + 1) ^ (int)$t + $o) % $e_len;
                $i[$o] = $t;
            }
        }

        return $i;
    }

    protected function decode_r($e)
    {
        if (!$e or (!strlen($e) % 4) == 1) {
            return false;
        }
        $o = 0;
        $a = 0;
        $t = 0;
        $r = '';
        $e_len = strlen($e);
        while ($a < $e_len)
        {
            $i = strpos($this->n, $e[$a]);
            if (false !== $i) {
                $t = $o % 4 ? 64 * $t + $i : $i;
                $o += 1;
                if (($o - 1) % 4) {
                    $c = chr(255 & $t >> (-2 * $o & 6));
                    if ($c != "\x00") {
                        $r .= $c;
                    }
                }
            }
            $a += 1;
        }
        return $r;
    }

    protected function v($e)
    {
        $e = array_reverse(str_split($e));

        return implode('', $e);
    }

    protected function r($e, $t)
    {
        $e = str_split($e);
        $o = $this->n . $this->n;
        $a = count($e) - 1;

        while ($a)
        {
            $i = strpos($o, $e[$a]);
            if(false !== $i) {
                $e[$a] = substr($o, $i - $t, 1);
            }
            $a -= 1;
        }

        return implode($e);
    }

    protected function s($e, $t)
    {
        $e_len = strlen($e);
        if ($e_len) {
            $i = $this->decode_s($e, $t);
            $o = 1;
            $e = str_split($e);
            while ($o < $e_len)
            {
                $_ = $this->splice($e, $i[$e_len - 1 - $o], 1, $e[$o]);
                $e = $_[1];
                $e[$o] = $_[0][0];
                $o += 1;
            }
            $e = implode('', $e);
        }

        return $e;
    }

    protected function i($e, $t)
    {
        return $this->s($e, (int)$t ^ $this->uid);
    }

    protected function x($e, $t)
    {
        $data = '';
        foreach ($e as $i) {
            $data .= chr(ord($i[0]) ^ ord($t[0]));
        }
        return $data;
    }
}










































