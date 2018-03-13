<?php

namespace YuruYuri\Vaud;


abstract class AlAudioBase
{
    public $sleepTime = 15;

    protected $apiUrl = 'https://vk.com/al_audio.php';
    protected $cookies;
    protected $uid;
    protected $userAgent;
    protected $playlist = [];
    protected $decodedPlaylist = [];
    protected $playlistId = -1; # Default - all tracks
    protected $splitAudioSize = 6;
    protected $limit = 0;
    protected $offset = 0;
    protected $unParsedTracks = [];
    protected $debugCallback;

    /**
     * @param int $offset
     *
     * @return array
     */
    protected function loadData($offset = 0): array
    {
        return [
            'access_hash' => '',
            'act' => 'load_section',
            'al' => 1,
            'claim' => '0',
            'offset' => $offset,
            'owner_id' => $this->uid,
            'playlist_id' => $this->playlistId,
            'type' => 'playlist'
        ];
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    protected function reloadData(array $ids): array
    {
        return [
            'act' => 'reload_audio',
            'al' => 1,
            'ids' => implode(',', $ids),
        ];
    }

    /**
     * @return array
     */
    private function headers(): array
    {
        $headers = [
            'User-Agent' => $this->userAgent,
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

    /**
     * @param array $cookies
     *
     * @return string
     */
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

    /**
     * @param string $url
     * @param array $data
     *
     * @return string
     */
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

    /**
     * @param $response
     * @param mixed $default
     *
     * @return array|mixed
     */
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
                if (\is_callable($this->debugCallback))
                {
                $this->debugCallback(\json_last_error_msg());
                $this->debugCallback('Matches: ' . \count($matches));
                $this->debugCallback($response);
                }

                $result = $default;
            }

            return $result;
        } catch (\Exception $e)
        {
            return $default;
        }
    }

    /**
     * @param $item
     *
     * @return array
     */
    protected function prepareAudioItem($item): array
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

    /**
     * @param array $items
     */
    protected function parseMoreAudio(array $items): void
    {
        $_ = [];
        foreach ($items as $item)
        {
            $_[] = $item;

            if (\count($_) === $this->splitAudioSize)
            {
                $this->getHiddenItems($_);
                $_ = [];
            }
        }
        $this->getHiddenItems($_);
    }

    /**
     * @param array $items
     * @param array $response
     */
    private function fillUnparsedHiddenTracks(array $items, array $response): void
    {
        if(\count($response) < \count($items))
        {
            $map = [];
            foreach ($response as $item)
            {
                $map[] = $item[0];
            }

            foreach ($items as $item)
            {
                if(!in_array($item[0], $map))
                {
                    $this->unParsedTracks[] = $item;
                }
            }
        }
    }

    /**
     * @param array $items
     */
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

    /**
     * @param array $items
     *
     * @return array
     */
    private function tracksIds(array $items): array
    {
        $_ = [];
        foreach ($items as $item)
        {
            $_[] = sprintf('%d_%d', $item[1], $item[0]);
        }
        return $_;
    }

    /**
     * @param array $_
     * @param int $count
     *
     * @return array|mixed
     */
    protected function tryLoadElements(array $_, int $count = 0)
    {
        $response = $this->post(
            $this->apiUrl,
            $this->reloadData($_)
        );

        $data = $this->parseResponse($response);

        if (\is_callable($this->debugCallback))
        {
            $this->debugCallback($response, $_);
        }

        if (!\count($data) && $count)
        {
            \is_callable($this->debugCallback) && $this->debugCallback('Time ban. Sleep...');

            sleep($this->sleepTime);
            return $this->tryLoadElements($_);
        }

        return $data;
    }

}
