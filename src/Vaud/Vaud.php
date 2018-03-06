<?php

namespace YuruYuri\Vaud;


class Vaud
{
    protected $uid;
    private $n = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMN0PQRSTUVWXYZO123456789+/=';

    public function __construct(int $uid)
    {
        if (!$uid)
        {
            throw new \InvalidArgumentException('Not received uid (required > 0)');
        }

        $this->uid = $uid;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function decode(string $url): string
    {
        if (\strpos($url, 'audio_api_unavailable') !== false)
        {
            $t = \explode('#', \explode('?extra=', $url)[1]);
            $n = $t[1] === '' ? '' : $this->decode_r($t[1]);
            $t = $this->decode_r($t[0]);
            if (!$t || !\is_string($t))
            {
                return $url;
            }
            $n = $n ? \explode(\chr(9), $n) : [];

            $n_len = \count($n);
            while ($n_len)
            {
                --$n_len;
                $s = \explode(\chr(11), $n[$n_len]);
                list($a, $s) = $this->splice($s, 0, 1, $t);
                $a = $a[0];
                if (!\method_exists($this, $a))
                {
                    return $url;
                }
                $t = $this->{$a}(...$s);
            }

            if (0 === \strpos($t, 'http'))
            {
                return $t;
            }
        }

        return $url;
    }

    protected function splice($a, $b, $c, ...$d)
    {
        if (\is_array($b))
        {
            return $this->splice($a, $b[0], $b[1], array_merge([$c] + $d));
        }

        $cash = $a;
        $a = \array_slice($a, $b, $c);
        $cash = \array_merge(
            \array_slice($cash, 0, $b),
            $d,
            \array_slice($cash, $c + $b)
        );

        return [$a, $cash];
    }

    protected function decode_s($e, $t)
    {
        $e_len = \strlen($e);
        $i = [];
        if ($e_len)
        {
            $o = $e_len;
            $t = \abs($t);
            while ($o)
            {
                --$o;
                $t = ($e_len * ($o + 1) ^ (int)$t + $o) % $e_len;
                $i[$o] = $t;
            }
        }

        return $i;
    }

    protected function decode_r($e)
    {
        if (!$e || (\strlen($e) % 4) === 1)
        {
            return false;
        }
        $o = 0;
        $a = 0;
        $t = 0;
        $r = '';
        $e_len = \strlen($e);
        while ($a < $e_len)
        {
            $i = \strpos($this->n, $e[$a]);
            if (false !== $i)
            {
                $t = ($o % 4) ? 64 * $t + $i : $i;
                ++$o;
                if (($o - 1) % 4)
                {
                    $c = \chr(255 & $t >> (-2 * $o & 6));
                    if ($c !== "\x00")
                    {
                        $r .= $c;
                    }
                }
            }
            ++$a;
        }

        return $r;
    }

    protected function v($e)
    {
        $e = \array_reverse(\str_split($e));

        return \implode('', $e);
    }

    protected function r($e, $t)
    {
        $e = \str_split($e);
        $o = $this->n . $this->n;
        $a = \count($e);

        while ($a)
        {
            --$a;
            $i = \strpos($o, $e[$a]);
            if (false !== $i)
            {
                $e[$a] = $o[$i - $t];
            }
        }

        return \implode($e);
    }

    protected function s($e, $t)
    {
        $e_len = \strlen($e);
        if ($e_len)
        {
            $i = $this->decode_s($e, $t);
            $o = 1;
            $e = \str_split($e);
            while ($o < $e_len)
            {
                $_ = $this->splice($e, $i[$e_len - 1 - $o], 1, $e[$o]);
                $e = $_[1];
                $e[$o] = $_[0][0];
                ++$o;
            }
            $e = \implode('', $e);
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
        $t =\ord($t[0]);
        $e = \str_split($e);
        foreach ($e as $i)
        {
            $data .= \chr(\ord($i[0]) ^ $t);
        }

        return $data;
    }
}
