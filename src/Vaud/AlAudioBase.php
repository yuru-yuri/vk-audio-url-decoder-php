<?php

namespace YuruYuri\Vaud;


abstract class AlAudioBase
{
    public $sleep_time = 10;
    public $debug = false;

    protected $api_url = 'https://vk.com/al_audio.php';
    protected $cookies;
    protected $uid;
    protected $user_agent;
    protected $playlist = [];
    protected $decodedPlaylist = [];
    protected $playlist_id = -1;  # Default - all tracks
    protected $split_audio_size = 5;
    protected $limit = 0;
    protected $offset = 0;
    protected $unParsedTracks = [];

    protected function loadData($offset = 0): array
    {
        return [
            'access_hash' => '',
            'act' => 'load_section',
            'al' => 1,
            'claim' => '0',
            'offset' => $offset,
            'owner_id' => $this->uid,
            'playlist_id' => $this->playlist_id,
            'type' => 'playlist'
        ];
    }

    protected function reloadData(array $ids): array
    {
        return [
            'act' => 'reload_audio',
            'al' => 1,
            'ids' => implode(',', $ids),
        ];
    }

    private function headers(): array
    {
        $headers = [
            'User-Agent' => $this->user_agent,
            'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'X-Requested-With' => 'XMLHttpRequest',
            'Connection' => 'keep-alive',
            'Pragma' => 'no-cache',
            'accept-encoding' => 'gzip, deflate, br',
            'Cache-Control' => 'no-cache',
            'Referer' => sprintf('https://vk.com/audios%d', $this->uid),
        ];

        $_ = [];
        foreach ($headers as $key => $value)
        {
            $_[] = sprintf('%s: %s', $key, $value);
        }

        return $_;
    }

    protected function parseCookies(array $cookies): string
    {
        $_ = [];
        foreach ($cookies as $key => $value)
        {
            if (\is_int($key))
            {
                $_[] = $value;
            }
            else
            {
                $_[] = \sprintf('%s=%s', $key, $value);
            }
        }

        return implode('; ', $_);
    }

    protected function post(string $url, array $data = []): string
    {
        $ch = \curl_init($url);
        \curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());
        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_COOKIE, $this->parseCookies($this->cookies));
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        \curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = \curl_exec($ch);
        \curl_close($ch);

        return $result;
    }

    protected function parseResponse($response, $default = [])
    {
        try
        {
            \preg_match('~<!json>(.+?)<!>~', $response, $matches);
            $result = \json_decode($matches[1]);
            if (\json_last_error())
            {
                $result = \json_decode(iconv('windows-1251', 'utf-8', $matches[1]));
            }

            if (\json_last_error())
            {
                if ($this->debug)
                {
                    echo \json_last_error_msg() . PHP_EOL . PHP_EOL;
                    echo 'Matches: ' . count($matches) . PHP_EOL . PHP_EOL;

                    echo substr($response, 0, 300) . PHP_EOL . PHP_EOL;
                }

                $result = $default;
            }

            return $result;
        } catch (\Exception $e)
        {
            return $default;
        }
    }

    protected function prepareAudioItem($item)
    {
        return [
            0 => $item[2],
            1 => $item[3],
            2 => $item[4],
            3 => $item[0],

            'url' => $item[2],
            'track' => $item[3],
            'artist' => $item[4],
            'id' => $item[0],
        ];
    }

    protected function parseMoreAudio(array $items): void
    {
        $_ = [];
        foreach ($items as $key => $item)
        {
            $_[] = $item;

            if ($key && $key % $this->split_audio_size == 0)
            {
                $this->getHiddenItems($_);
                $_ = [];
            }
        }
    }

    private function fillUnparsedHiddenTracks(array $items, array $response): void
    {
        if(\count($response) < \count($items))
        {
            $map = array_map(function ($a) { return $a[0]; }, $response);

            foreach ($items as $item)
            {
                if(!in_array($item[0], $map))
                {
                    $this->unParsedTracks[] = $item;
                }
            }
        }
    }

    protected function getHiddenItems(array $items): void
    {
        $_ = $this->tracksIds($items);
        $data = $this->tryLoadElements($_, count($items));

        $this->fillUnparsedHiddenTracks($items, $data);

        foreach ($data as $item)
        {
            $this->decodedPlaylist[] = $this->prepareAudioItem($item);
        }
    }

    private function tracksIds(array $items): array
    {
        $_ = [];
        foreach ($items as $item)
        {
            $_[] = sprintf('%d_%d', $item[1], $item[0]);
        }
        return $_;
    }

    private function tryLoadElements($_, $count = 0)
    {
        $response = $this->post(
            $this->api_url,
            $this->reloadData($_)
        );

        $data = $this->parseResponse($response);

        if ($count === 0 && $this->debug && \defined('APP_ROOT'))
        {
            is_dir(APP_ROOT . '/VaudDebug/') or mkdir(APP_ROOT . '/VaudDebug/', 0777, true);
            file_put_contents(APP_ROOT . '/debug/' . \implode('_', $_) . '.txt', $response);
        }

        if (!\count($data) && $count)
        {
            $this->debug && print('Time ban. Sleep...' . PHP_EOL);

            sleep($this->sleep_time);
            return $this->tryLoadElements($_);
        }

        return $data;
    }

}
