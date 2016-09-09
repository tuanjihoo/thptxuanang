<?php
/**
 * Plugin for http://postimage.org
 *
 * @release Jun 19, 2014
 */
class ChipVN_ImageUploader_Plugins_Postimage extends ChipVN_ImageUploader_Plugins_Abstract
{
    const FREE_UPLOAD_ENPOINT    = 'http://postimage.org/';
    const ACCOUNT_UPLOAD_ENPOINT = 'http://postimg.org/';

    /**
     * Gets upload url endpoint
     *
     * @return string
     */
    private function getUrlEnpoint()
    {
        return $this->getCache()->get('session_login')
            ? self::ACCOUNT_UPLOAD_ENPOINT
            : self::FREE_UPLOAD_ENPOINT;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLogin()
    {
        if (!$this->getCache()->get('session_login')) {
            $this->resetHttpClient();
            $this->client->execute('http://postimage.org/profile.php', 'POST', array(
                'login'    => $this->username,
                'password' => $this->password,
                'submit'   => '',
            ));

            $this->checkHttpClientErrors(__METHOD__);

            if ($this->client->getResponseStatus() == 302
                && $this->client->getResponseArrayCookies('userlogin') != 'deleted'
            ) {
                $this->getCache()->set('session_login', $this->client->getResponseArrayCookies());
            } else {
                $this->getCache()->deleteGroup($this->getIdentifier());
                $this->throwException('%s: Login failed.', __METHOD__);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload()
    {
        $endpoint = $this->getUrlEnpoint();
        $time     = time();

        $this->resetHttpClient();
        $this->client->setSubmitMultipart();
        if ($this->useAccount) {
            $this->client->setCookies($this->getCache()->get('session_login'));
        }
        $this->client->setReferer($endpoint);
        $this->client->setParameters(array(
            'upload'         => '@' . $this->file,
            'mode'           => 'local',
            'areaid'         => '',
            'hash'           => '',
            'code'           => '',
            'content'        => '',
            'tpl'            => '.',
            'ver'            => '',
            'addform'        => '',
            'mforum'         => '',
            'session_upload' => $time,
            'um'             => 'computer',
            'forumurl'       => $endpoint,
            'upload_error'   => '',
            'ui'             => '24__1440__900__true__?__?__' .date('d/m/Y H:i:s'). '__Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:30.0) Gecko/20100101 Firefox/30.0__',
            'optsize'        => '0',
            'adult'          => 'no',
        ));
        $this->client->execute($endpoint);

        $galleryId = (string) $this->client;

        $this->resetHttpClient();
        if ($this->useAccount) {
            $this->client->setCookies($this->getCache()->get('session_login'));
        }
        $this->client->setReferer($endpoint);
        $this->client->setParameters(array(
            'upload[]'       => '',
            'mode'           => 'local',
            'areaid'         => '',
            'hash'           => '',
            'code'           => '',
            'content'        => '',
            'tpl'            => '.',
            'ver'            => '',
            'addform'        => '',
            'mforum'         => '',
            'session_upload' => $time,
            'um'             => 'computer',
            'forumurl'       => $endpoint,
            'gallery_id'     => $galleryId,
            'upload_error'   => '',
            'ui'             => '24__1440__900__true__?__?__' .date('d/m/Y H:i:s'). '__Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:30.0) Gecko/20100101 Firefox/30.0__',
            'optsize'        => '0',
            'adult'          => 'no',
        ));
        $this->client->execute($endpoint, 'POST');

        $this->checkHttpClientErrors(__METHOD__);

        return $this->getImageFromResult($this->client->getResponseHeaders('location'));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransload()
    {
        $endpoint    = $this->getUrlEnpoint();
        $webEndpoint = $endpoint . 'index.php?um=web';
        $time        = time();

        $this->resetHttpClient();
        if ($this->useAccount) {
            $this->client->setCookies($this->getCache()->get('session_login'));
        }
        $this->client->setReferer($endpoint);
        $this->client->setParameters(array(
            'addform'  => '',
            'adult'    => 'no',
            'areaid'   => '',
            'code'     => '',
            'content'  => '',
            'forumurl' => $endpoint,
            'hash'     => '',
            'mforum'   => '',
            'mode'     => 'local',
            'optsize'  => '0',
            'submit'   => 'Upload It!',
            'tpl'      => '.',
            'ui'       => '24__1440__900__true__?__?__' .date('d/m/Y H : i : s'). '__Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv : 30.0) Gecko/20100101 Firefox/30.0__',
            'um'       => 'web',
            'url_list' => $this->url,
            'ver'      => '',
        ));
        $this->client->execute($webEndpoint, 'POST');

        $this->checkHttpClientErrors(__METHOD__);

        return $this->getImageFromResult($this->client->getResponseHeaders('location'));
    }

    /**
     * Parse and get image url from result page.
     * Eg: http://postimg.org/image/wvznrbllz/d5a5b291/
     *
     * @param  string $url
     * @return string
     */
    private function getImageFromResult($url)
    {
        $endpoint = $this->getUrlEnpoint();
        $imageId  = $this->getMatch('#^http://postimg\.org/\w+/([^/]+)/.*?#', $url);

        if (! $imageId) {
            $this->throwException('%s: Image ID not found.', __METHOD__);
        }

        $this->resetHttpClient();
        $this->client->setFollowRedirect(true, 1);
        $this->client->execute($url);

        $imageUrl = $this->getMatch('#id="code_2".*?>(https?://\w+\.postimg\.org/\w+/\w+\.\w+)#i', $this->client);

        if (! $imageUrl) {
            $this->throwException('%s: Image URL not found.', __METHOD__);
            // $this->resetHttpClient();
            // $this->client->setReferer($url);
            // $this->client->execute('http://postimg.org/image/' . $imageId . '/');
            // print_r($this->client);
            // exit;
        }

        return $imageUrl;
    }
}
