<?php

namespace YuruYuri\Vaud;

/**
 * Class AlAudio
 * @package YuruYuri\Vaud
 */
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
        $this->uid       = $uid;
        $this->cookies   = $cookies;
        $this->userAgent = $userAgent ?? \sprintf('%s %s %s %s',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'AppleWebKit/537.36 (KHTML, like Gecko)',
                'Chrome/60.0.3112.101',
                'Safari/537.36');
    }

    /**
     * @param int $limit
     * @param int $offset
     */
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
        $this->playlistId = $id;
    }

    /**
     * @return array
     */
    public function getUnParsedTracks(): array
    {
        return $this->unParsedTracks;
    }

    /**
     * @param array $ids
     * @return array|mixed
     */
    public function getItemsByIds(array $ids)
    {
        return $this->tryLoadElements($ids, count($ids));
    }

    /**
     * @param callable $callback
     */
    public function setDebugCallback(callable $callback): void
    {
        $this->debugCallback = $callback;
    }

    /**
     * @param int $offset
     */
    protected function fillPlaylist(int $offset = 0): void
    {
        while (true)
        {
            if (!$offset && $this->offset)
            {
                $offset = $this->offset;
            }

            $response = $this->parseResponse($this->post(
                $this->apiUrl,
                $this->loadData($offset)
            ));

            $check_type = !isset($response->type) or $response->type !== 'playlist';

            if ($check_type || ($this->limit > 0 && \count($this->playlist) >= $this->limit))
            {
                return;
            }

            $this->playlist = \array_merge($this->playlist, $response->list);

            if(empty($response->hasMore))
            {
                break;
            }

            $offset = $response->nextOffset;
        }
    }

    /**
     *
     */
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
