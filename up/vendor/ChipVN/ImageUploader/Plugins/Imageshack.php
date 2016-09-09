<?php
/**
 * Use "imageshack.com" rest API v2, insteadof "imageshack.us".
 * Register an API here: {@link https://imageshack.com/contact/api}.
 * You must login and have an API for uploading, transloading.
 *
 * @update Jul 10, 2014
 */

class ChipVN_ImageUploader_Plugins_Imageshack extends ChipVN_ImageUploader_Plugins_Abstract
{
    const LOGIN_ENDPOINT = 'https://imageshack.com/rest_api/v2/user/login';
    const UPLOAD_ENPOINT = 'https://imageshack.com/rest_api/v2/images';

    /**
     * Get API endpoint URL.
     *
     * @param  string $path
     * @return string
     */
    protected function getApiURL($path)
    {
        return self::API_ENDPOINT . $path;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLogin()
    {
        // session_login is array
        if (!$this->getCache()->get('session_login')) {
            $this->resetHttpClient();
            $this->client->setReferer('https://imageshack.com/');
            $this->client->execute(self::LOGIN_ENDPOINT, 'POST', array(
                'username'    => $this->username,
                'password'    => $this->password,
                'remember_me' => 'true',
                'set_cookies' => 'true',
            ));
            $result = json_decode($this->client->getResponseText(), true);

            $this->checkHttpClientErrors(__METHOD__);

            if (!empty($result['result']['userid'])) {
                $this->getCache()->set('session_login', $result['result']);

            } else {
                if (isset($result['error']['error_message'])) {
                    $message = $result['error']['error_message'];
                } else {
                    $message = 'Login failed.';
                }
                $this->getCache()->deleteGroup($this->getIdentifier());
                $this->throwException('%s: %s.', __METHOD__, $message); // $this->client->getResponseText()
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload()
    {
        return $this->sendRequest(array('file' => '@' . $this->file));
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransload()
    {
        return $this->sendRequest(array('url' => $this->url));
    }

    /**
     * Send request to API and get image URL.
     *
     * @param  array  $param
     * @return string
     *
     * @throws \Exception If have an error occured
     */
    private function sendRequest(array $param)
    {
        if (!$this->getCache()->get('session_login') || empty($this->apiKey)) {
            $this->throwException(
                'You must be loggedin and have an API key. Register API here: https://imageshack.com/contact/api'
            );
        }

        $session = $this->getCache()->get('session_login');

        $this->resetHttpClient();
        $this->client->setSubmitMultipart();
        $this->client->setParameters($param + array(
            'auth_token' => $session['auth_token'],
            'api_key'    => $this->apiKey,
        ));
        $this->client->execute(self::UPLOAD_ENPOINT, 'POST');

        $result = json_decode($this->client->getResponseText(), true);

        $this->checkHttpClientErrors(__METHOD__);

        if (isset($result['error']['error_message'])) {
            $this->throwException(__METHOD__ . ': ' . $result['error']['error_message']); // . $this->client->getResponseText()

        } elseif (isset($result['result']['images'][0])) {
            $image = $result['result']['images'][0];

            return 'http://imageshack.com/a/img' . $image['server'] . '/' . $image['bucket'] . '/' . $image['filename'];
        }

        return false;
    }
}
