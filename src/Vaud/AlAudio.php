<?php

namespace YuruYuri\Vaud;

class AlAudio
{
    protected $api_url = 'https://vk.com/al_audio.php';
    protected $cookies;
    protected $uid;
    protected $user_agent;
    protected $playlist = [];
    protected $playlist_id = -1;  # Default - all tracks
    protected $sleep_time = 1;
    protected $split_audio_size = 5;

    /**
     * AlAudio constructor.
     *
     * @param int $uid
     * @param array $cookies
     * @param string $userAgent
     */
    public function __construct(int $uid, array $cookies, ?string $userAgent = null)
    {
        $this->uid = $uid;
        $this->cookies = $cookies;
        $this->user_agent = $userAgent ?? \sprintf('%s %s %s %s',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'AppleWebKit/537.36 (KHTML, like Gecko)',
            'Chrome/60.0.3112.101',
            'Safari/537.36');
    }

    /**
     * @return array
     */
    public function main(): array
    {
        $this->fillPlaylist();
        return $this->parsePlaylist();
    }

    /**
     * @param int $id
     */
    public function set_playlist_id(int $id): void
    {
        $this->playlist_id = $id;
    }

    protected function load_data($offset = 0): array
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

    protected function headers(): array
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

    protected function reloadData(array $ids): array
    {
        return [
            'act' => 'reload_audio',
            'al' => 1,
            'ids' => implode(',', $ids),
        ];

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

    protected function fillPlaylist(int $offset = 0): void
    {
        $response = $this->parseResponse($this->post(
            $this->api_url,
            $this->load_data($offset)
        ));

        if (!isset($response->type) or $response->type !== 'playlist')
        {
            return;
        }

        $this->playlist = \array_merge($this->playlist, $response->list);

        if (!empty($response->hasMore))
        {
            sleep($this->sleep_time);
            $this->fillPlaylist($response->nextOffset);
        }
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

            return $result;
        } catch (\Exception $e)
        {
            return $default;
        }
    }

    protected function parseAudioItem($item) {
        return [$item[2], $item[3], $item[4]];
    }

    protected function parseMoreAudio(array $items): array
    {
        $response = [];
        $_ = [];
        foreach ($items as $key => $item)
        {
            $_[] = $item;

            if ($key && $key % $this->split_audio_size == 0)
            {
                $this->getHiddenItems($_, $response);
            }
        }

        return $response;
    }
    
    protected function parsePlaylist(): array
    {
        $response = [];
        $_= [];
        foreach ($this->playlist as $item)
        {
            if (empty($item[2]))
            {
                $_[] = $item;
            }
            else
            {
                $response[] = $this->parseAudioItem($item);
            }
        }

        return array_merge($response, $this->parseMoreAudio($_));
    }

    protected function getHiddenItems(array $items, array &$response): void
    {
        $_ = [];
        foreach ($items as $item)
        {
            $_[] = sprintf('%d_%d', $item[1], $item[0]);
        }

        $data = $this->parseResponse($this->post(
            $this->api_url,
            $this->reloadData($_)
        ));

        foreach ($data as $item)
        {
            $response[] = $this->parseAudioItem($item);
        }
    }

}
