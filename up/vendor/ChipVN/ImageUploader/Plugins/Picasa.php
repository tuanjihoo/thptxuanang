<?php
/**
 * You must loggedin for uploading.
 * This plugin doesn't support transloading.
 *
 * @lastupdate May 27, 2014
 */

class ChipVN_ImageUploader_Plugins_Picasa extends ChipVN_ImageUploader_Plugins_Abstract
{
    /*
     * AlbumId to archive image.
     * Account upload limits:
     *
     * Maximum photo size: Each image can be no larger than 20 megabytes and are restricted to 50 megapixels or less.
     * Maximum video size: Each video uploaded can be no larger than 1GB in size.
     * Maximum number of web albums: 20,000
     * Maximum number of photos and videos per web album: 1,000
     * Total storage space: Picasa Web provides 1 GB for photos and videos. Files under
     *
     * @var	string
     */
    private $albumId = 'default';

    /**
     * {@inheritdoc}
     */
    protected function doLogin()
    {
        // normalize username
        $this->username = preg_replace('#@gmail\.com#i', '', $this->username);

        if (!$this->getCache()->get('session_login')) {
            $this->resetHttpClient();
            $this->client->setParameters(array(
                'accountType' => 'HOSTED_OR_GOOGLE',
                'Email'       => $this->username,
                'Passwd'      => $this->password,
                'source'      => 'Web Application',
                'service'     => 'lh2'
            ));
            $this->client->execute('https://www.google.com/accounts/ClientLogin', 'POST');

            $this->checkHttpClientErrors(__METHOD__);

            if ($cookie = $this->getMatch('#Auth=([a-z0-9_\-]+)#i', $this->client->getResponseText())) {
                $this->getCache()->set('session_login', $cookie, 300);
            } elseif (
                ($error = $this->getMatch('#Error=(.+)#i', $this->client->getResponseText()))
                && ($info = $this->getMatch('#Info=(.+)#i', $this->client->getResponseText()))
            ) {
                $this->getCache()->deleteGroup($this->getIdentifier());

                $this->throwException('%s: Error=%s. Info=%s', __METHOD__, $error, $info);
            } else {
                $this->getCache()->deleteGroup($this->getIdentifier());

                $this->throwException('%s: Login failed.', __METHOD__);
            }
        }

        return true;
    }

    /**
     * Set AlbumID.
     * You can set AlbumId by an array, then method will get random an id
     *
     * @param string|array
     */
    public function setAlbumId($albumIds)
    {
        if (empty($albumIds)) {
            $albumIds = 'default';
        }
        $albumIds = (array) $albumIds;
        $this->albumId = $albumIds[array_rand($albumIds, 1)];
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload()
    {
        $this->checkPermission(__METHOD__);

        $this->resetHttpClient();
        $this->client->setSubmitMultipart('related');
        $this->client->setHeaders(array(
            "Authorization: GoogleLogin auth=" . $this->getCache()->get('session_login'),
            "MIME-Version: 1.0",
        ));
        $this->client->setRawPost("Content-Type: application/atom+xml\r\n
            <entry xmlns='http://www.w3.org/2005/Atom'>
            <title>" . preg_replace('#\..*?$#i', '', basename($this->file)) . "</title>
            <category scheme=\"http://schemas.google.com/g/2005#kind\" term=\"http://schemas.google.com/photos/2007#photo\"/>
            </entry>");

        $this->client->setParameters(array(
            'data' => '@' . $this->file,
        ));

        $this->client->execute(
            'https://picasaweb.google.com/data/feed/api/user/' . $this->username . '/albumid/' . $this->albumId . '?alt=json'
        );

        $result = json_decode($this->client->getResponseText(), true);

        $this->checkHttpClientErrors(__METHOD__);

        if ($this->client->getResponseStatus() != 201 || empty($result['entry']['media$group']['media$content'][0]) )
        {
            $this->throwException('%s: Upload failed. %s', __METHOD__, $this->client->getResponseText());
        }

        // url, width, height, type
        extract($result['entry']['media$group']['media$content'][0]);

        $url = preg_replace('#/(s\d+/)?([^/]+)$#', '/s0/$2', $url);

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransload()
    {
        $this->throwException('%s: Currently, this plugin doesn\'t support transload image.', __METHOD__);
    }

    /**
     * Delete an album by albumid
     *
     * @param  string  $albumId
     * @return boolean True if album was deleted
     *
     * @throws \Exception
     */
    public function deleteAlbum($albumId)
    {
        $this->checkPermission(__METHOD__);

        $this->resetHttpClient();
        $this->client->setHeaders(array(
            "Authorization: GoogleLogin auth=" . $this->getCache()->get('session_login'),
            "MIME-Version: 1.0",
            "GData-Version: 3.0",
            "If-Match: *"
        ));
        $this->client->execute('https://picasaweb.google.com/data/entry/api/user/' . $this->username . '/albumid/' . $albumId, 'DELETE');

        $this->checkHttpClientErrors(__METHOD__);

        return ($this->client->getResponseHeaders('status') == 200);
    }

    /**
     * Create new album and return albumId was created.
     *
     * @param  string       $title
     * @param  string       $access
     * @param  string       $description
     * @return string|false
     *
     * @throws \Exception
     */
    public function addAlbum($title, $access = 'public', $description = '')
    {
        $this->checkPermission(__METHOD__);

        $this->resetHttpClient();
        $this->client->setHeaders(array(
            "Authorization: GoogleLogin auth=" . $this->getCache()->get('session_login'),
            "MIME-Version: 1.0",
        ));
        $this->client->setMimeContentType("application/atom+xml");
        $this->client->setRawPost("<entry xmlns='http://www.w3.org/2005/Atom' xmlns:media='http://search.yahoo.com/mrss/' xmlns:gphoto='http://schemas.google.com/photos/2007'>
            <title type='text'>" . $title . "</title>
            <summary type='text'>" . $description . "</summary>
            <gphoto:access>" . $access . "</gphoto:access>
            <category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/photos/2007#album'></category>
        </entry>");
        $this->client->execute('https://picasaweb.google.com/data/feed/api/user/' . $this->username, 'POST');

        $this->checkHttpClientErrors(__METHOD__);

        return $this->getMatch('#<id>.+?albumid/(.+?)</id>#i', $this->client->getResponseText(), 1, false);
    }

    /**
     * @param  string     $method
     * @throws \Exception if session_login is empty
     */
    private function checkPermission($method)
    {
        if (!$this->getCache()->get('session_login')) {
            $this->throwException('You must be logged in before call the method "%s"', __METHOD__);
        }
    }
}
