<?php
/**
 * You may upload to your account or without account.
 */

class ChipVN_ImageUploader_Plugins_Imgur extends ChipVN_ImageUploader_Plugins_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected function doLogin()
    {
        if (!$this->getCache()->get('session_login')) {
            $this->resetHttpClient();
            $this->client->execute('https://imgur.com/signin', 'POST', array(
                'username'    => $this->username,
                'password'    => $this->password,
                'submit_form' => 'Sign in',
            ));

            $this->checkHttpClientErrors(__METHOD__);

            if ($this->client->getResponseStatus() == 302
                || $this->client->getResponseArrayCookies('just_logged_in') == 1
                || (stripos($this->client->getResponseHeaders('location'), $this->username))
            ) {
                $this->getCache()->set('session_login', $this->client->getResponseArrayCookies());
            } else {
                $this->getCache()->deleteGroup($this->getIdentifier());
                $this->throwException('%s: Login failed.', __METHOD__); // $this->client->getResponseText()
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload()
    {
        if (!$this->useAccount) {
            return $this->doUploadFree();
        }

        $this->resetHttpClient();
        $this->client->setSubmitMultipart();
        $this->client->setCookies($this->getCache()->get('session_login'));
        $this->client->setParameters(array(
            'image' => '@' . $this->file,
        ));
        $this->client->execute('http://api.imgur.com/2/upload.json', 'POST');
        $result = json_decode($this->client->getResponseText(), true);

        $this->checkHttpClientErrors(__METHOD__);

        if (isset($result['error'])) {
            $this->throwException('%s: %s', __METHOD__ , $result['error']['message']);
        }

        return $this->getLinkFromUploadedResult($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransload()
    {
        if (!$this->useAccount) {
            return $this->doTransloadFree();
        }

        $this->resetHttpClient();
        $this->client->setCookies($this->getCache()->get('session_login'));
        $this->client->setParameters(array(
            'url' => $this->url,
        ));
        $this->client->execute('http://imgur.com/upload', 'POST');
        $result = json_decode($this->client->getResponseText(), true);

        $this->checkHttpClientErrors(__METHOD__);

        if (strpos($this->client->getResponseHeaders('location'), 'error')) {
            $this->throwException('%s: Image format not supported, or image is corrupt.', __METHOD__);
        }

        return 'http://i.imgur.com/' . $result['data']['hash'] . $this->getExtensionFormImage($this->url);
    }

    /**
     * Free upload also the image may remove after a period of time
     *
     * @return string    Image URL after upload
     * @throws Exception if upload failed
     */
    private function doUploadFree()
    {
        $this->getFreeSID();

        $this->resetHttpClient();
        $this->client->setSubmitMultipart();
        $this->client->setHeaders(array(
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => 'http://imgur.com/',
        ));
        $this->client->setCookies($this->getCache()->get('free_session'));
        $this->client->setParameters(array(
            'current_upload' => 1,
            'total_uploads'  => 1,
            'terms'          => 0,
            'album_title'    => self::POWERED_BY,
            'gallery_title'  => self::POWERED_BY,
            'sid'            => $this->getCache()->get('free_sid'),
            'Filedata'       => '@' . $this->file,
        ));
        $this->client->execute('http://imgur.com/upload', 'POST');
        $result = json_decode($this->client->getResponseText(), true);

        $this->checkHttpClientErrors(__METHOD__);

        if (isset($result['data']['hash']) AND isset($result['success']) AND $result['success']) {
            return 'http://i.imgur.com/' . $result['data']['hash'] . $this->getExtensionFormImage($this->file);
        } elseif (isset($result['data']['error']) && $error = $result['data']['error']) {
             $this->throwException('%s: %s (%d).', __METHOD__, $error['message'], $error['code']);
        } else {
            $this->throwException('%s: Free upload failed.', __METHOD__);
        }

        return false;
    }

    /**
     * Free transload also the image may remove after a period of time
     *
     * @return string    Image URL after transload
     * @throws Exception if upload failed
     */
    private function doTransloadFree()
    {
        $this->getFreeSID();

        $this->resetHttpClient();
        $this->client->setHeaders(array(
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer'          => 'http://imgur.com/',
        ));
        $this->client->setCookies($this->getCache()->get('free_session'));
        $this->client->setParameters(array(
            'current_upload' => 1,
            'total_uploads'  => 1,
            'terms'          => 0,
            'album_title'    => self::POWERED_BY,
            'gallery_title'  => self::POWERED_BY,
            'sid'            => $this->getCache()->get('free_sid'),
            'url'            => $this->url,
        ));
        $this->client->execute('http://imgur.com/upload', 'POST');
        $result = json_decode($this->client->getResponseText(), true);

        $this->checkHttpClientErrors(__METHOD__);

        if (isset($result['data']['hash']) AND isset($result['success']) AND $result['success']) {
            return 'http://i.imgur.com/' . $result['data']['hash'] . $this->getExtensionFormImage($this->url);
        } elseif (isset($result['data']['error']) && $error = $result['data']['error']) {
             $this->throwException('%s: %s (%d).', __METHOD__, $error['message'], $error['code']);
        } else {
            $this->throwException('%s: Free transload failed.', __METHOD__);
        }

        return false;
    }

    // [upload] => Array
    // (
    //     [image] => Array
    //     (
    //         [name]       =>
    //         [title]      =>
    //         [caption]    =>
    //         [hash]       => BP2HdFa
    //         [deletehash] => XXXXXXX
    //         [datetime]   => 2013-07-25 19:29:57
    //         [type]       => image/jpeg
    //         [animated]   => false
    //         [width]      => 420
    //         [height]     => 420
    //         [size]       => 34056
    //         [views]      => 0
    //         [bandwidth]  => 0
    //     )
    //     [links] => Array
    //     (
    //         [original]         => http://i.imgur.com/BP2HdFa.jpg
    //         [imgur_page]       => http://imgur.com/BP2HdFa
    //         [delete_page]      => http://imgur.com/delete/XXXXXXX
    //         [small_square]     => http://i.imgur.com/BP2HdFas.jpg
    //         [big_square]       => http://i.imgur.com/BP2HdFab.jpg
    //         [small_thumbnail]  => http://i.imgur.com/BP2HdFat.jpg
    //         [medium_thumbnail] => http://i.imgur.com/BP2HdFam.jpg
    //         [large_thumbnail]  => http://i.imgur.com/BP2HdFal.jpg
    //         [huge_thumbnail]   => http://i.imgur.com/BP2HdFah.jpg
    //     )
    // )
    /**
     * Get link from result.
     *
     * @param  array  $result
     * @return string
     */
    private function getLinkFromUploadedResult($result)
    {
        return $result['upload']['links']['original'];
    }

    private function getFreeSID()
    {
        if (!$this->getCache()->get('free_sid')) {
            $this->resetHttpClient();
            $this->client->execute('http://imgur.com/upload/start_session');
            $result = json_decode($this->client->getResponseText(), true);

            $this->checkHttpClientErrors(__METHOD__);

            if (isset($result['sid'])) {
                $this->getCache()->set('free_sid', $result['sid']);
                $this->getCache()->set('free_session', $this->client->getResponseCookies());
            } else {
                $this->throwException('%s: Cannot get free IMGURSESSION.', __METHOD__);
            }
        }

        return $this->getCache()->get('free_sid');
    }

    /**
     * Get extension for image url (free upload or transload)
     * This method help to don't need to read the page after upload completed to get extension for the image
     *
     * @param  string $fileName
     * @return string
     */
    private function getExtensionFormImage($fileName)
    {
        // .bmp -> .jpg
        return $this->getMatch('#\.(gif|jpg|jpeg|png)$#i', $fileName, 0, '.jpg');
    }
}
