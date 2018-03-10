<?php

namespace YuruYuri\Vaud;

class AlAudio extends AlAudioBase
{
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

    public function setLimitOffset(int $limit = 0, int $offset = 0): void
    {
        $limit > 0 and $this->limit = $limit;
        $offset > 0 and $this->offset = $offset;
    }

    /**
     * @return array
     */
    public function main(): array
    {
        $this->fillPlaylist();
        $this->parsePlaylist();

        if($this->limit > 0)
        {
            return array_slice($this->decodedPlaylist, 0, $this->limit);
        }

        return $this->decodedPlaylist;
    }

    /**
     * @param int $id
     */
    public function setPlaylistId(int $id): void
    {
        $this->playlist_id = $id;
    }

    public function getUnParsedTracks(): array
    {
        return $this->unParsedTracks;
    }

    protected function fillPlaylist(int $offset = 0): void
    {
        if(!$offset && $this->offset)
        {
            $offset = $this->offset;
        }

        $response = $this->parseResponse($this->post(
            $this->api_url,
            $this->loadData($offset)
        ));

        if($this->limit > 0 && count($this->playlist) >= $this->limit)
        {
            return;
        }

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

    protected function parsePlaylist(): void
    {
        $_ = [];
        foreach ($this->playlist as $item)
        {
            if (empty($item[2]))
            {
                $_[] = $item;
            }
            else
            {
                $this->decodedPlaylist[] = $this->prepareAudioItem($item);
            }
        }

        $this->parseMoreAudio($_);
    }

}
