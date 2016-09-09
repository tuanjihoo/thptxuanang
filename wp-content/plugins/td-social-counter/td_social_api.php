<?php
class td_social_api {


    private $log_buffer = '';
    private $log_last_get_url_page_data = '';




    //the name of the field in the table that holds the cache array
    private $cache_var_name_last_val = 'td_social_api_v3_last_val';

    private $do_transient_save = false;

    //the cache holding the last value > 0
    public $td_cache_last_val = array();


    function __construct() {
        $this->td_cache_last_val = get_option($this->cache_var_name_last_val, '');
        $this->log(__FUNCTION__, 'New class instance');
    }


    //check to see if we have the cache
    private function in_cache($service_id, $user_id) {
        //se poate sa dea worning aici cind nu exista array-ul cache in tabela
        //echo '<br>'.$service_id. ": ". (time() - $this->td_cache_last_val[$service_id]['timestamp']) .' > ' . $this->td_cache_last_val[$service_id]['expires'].'<br>';
        //e pus asa ( < )ca sa dea false la verificare
        if (is_array($this->td_cache_last_val) and isset($this->td_cache_last_val[$service_id]['uid']) and $this->td_cache_last_val[$service_id]['uid'] == $user_id and (time() - $this->td_cache_last_val[$service_id]['timestamp'] < $this->td_cache_last_val[$service_id]['expires']) ) {
            return true;
        } else {
            return false;
        }
    }

    //extract the value for a service_id, and return it to the service_id function
    private function get_cache($service_id, $user_id) {
        if (is_array($this->td_cache_last_val) and isset($this->td_cache_last_val[$service_id]['uid']) and $this->td_cache_last_val[$service_id]['uid'] == $user_id) {
            return $this->td_cache_last_val[$service_id];
        } else {
            return false;
        }
    }

    //save the cache array to the database
    private function save_cache($service_id, $data) {
        if($this->do_transient_save == true) {
            $this->td_cache_last_val[$service_id] = $data;

            //update the database
            update_option($this->cache_var_name_last_val, $this->td_cache_last_val);
        }

        $this->do_transient_save = false;
    }


    public function get_url_wordpress($url) {

        $this->log(__FUNCTION__, "Fatching url: $url");

        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'sslverify' => false,
            'user-agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0'
        ));

        if (is_wp_error($response)) {
            $this->log(__FUNCTION__, 'got wp_error, get_error_message: ' . $response->get_error_message());
            return false;
        }

        // do not kill at response code != 200, it may still work
        if (wp_remote_retrieve_response_code($response) != 200) {
            $this->log(__FUNCTION__, 'Response code != 200: ' . wp_remote_retrieve_response_code($response));
        }

        $td_request_result = wp_remote_retrieve_body($response);

        if ($td_request_result == '') {
            $this->log(__FUNCTION__, 'Empty response via wp_remote_retrieve_body, Quitting.');
            return false;
        }


        $this->log_last_get_url_page_data = $td_request_result;
        return $td_request_result;
    }



    public function get_url_file_get_contents($url) {
        $this->log(__FUNCTION__, "Fatching url: $url");

        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0\r\n"
            )
        );
        $context = stream_context_create($opts);

        $td_data = file_get_contents($url, false, $context);

        if (empty($td_data)) {
            $this->log(__FUNCTION__, 'file_get_contents returned empty result');
            return false;
        } else {
            return $td_data;
        }


    }



    private function get_url_curl($url) {
        $this->log(__FUNCTION__, "Fatching url: $url");


        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        //mergem dupa redirecturi
        curl_setopt ($ch,  CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ($ch,  CURLOPT_MAXREDIRS, 3); //max redirects
        curl_setopt ($ch,  CURLOPT_ENCODING, ''); //folosim compresia - daca e empty trimite toate formele de compresie suportate
        //timeout? - 300 sec = 5 min
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10); //Fail if a web server doesn’t respond to a connection within a time limit (seconds).
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //Fail if a web server doesn’t return the web page within a time limit (seconds).
        curl_setopt($ch, CURLOPT_HEADER, false);
        // misc
        curl_setopt($ch,CURLOPT_AUTOREFERER,true); //The referer is a URL for the web page that linked to the requested web page. When following redirects, set this to true and CURL automatically fills in the URL of the page being redirected away from.
        curl_setopt($ch,CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);



        $this->log(__FUNCTION__, 'curl_error: ' . curl_error($ch));
        $this->log(__FUNCTION__, 'curl_getinfo: ' . curl_getinfo($ch));


        /***************************************************************
         * html cache checks
         */
        $results['data'] = $data;
        curl_close($ch);
        return $data;
    }


    /*
     *
     */
    private function get_url($url) {

        /*
        if( ini_get('safe_mode') ){
            echo 'xxxxxxxxxxxx';
        }


        if( ini_get('open_basedir') ){
            echo 'xxxxxsadsdadsasadxxxxxxx';
        }
        die;
        */
        //return $this->get_url_curl($url);

        return $this->get_url_wordpress($url);
        //return $this->get_url_file_get_contents($url);





    }

    private function get_json($url) {
        $td_json = json_decode($this->get_url($url), true);
        return $td_json;
    }

    private function extract_numbers_from_string($td_string) {
        $buffy = '';
        foreach (str_split($td_string) as $td_char) {
            if (is_numeric($td_char)) {
                $buffy .= $td_char;
            }
        }
        return $buffy;
    }



    public function get_social_counter($service_id, $user_id, $disable_cache = false) {
        $buffy_array = 0;



        //use in g+
        $expl_maches = '';

        if ($this->in_cache($service_id, $user_id) === false or $disable_cache === true) { //disable cache here

            try {
                switch ($service_id) {
                    case 'facebook':

                        $td_data = $this->get_url("http://facebook.com/$user_id");

                        if ($td_data === false) {
                            $this->log(__FUNCTION__, 'The get_url method FAILED, returning 0');
                            $buffy_array = 0;
                            break;
                        }

                        $pattern = "/id=\"PagesLikesCountDOMID\">(.*?)<\/span><\/span>/i";

                        //first match - get the string with number of likes and the `likes` word
                        preg_match_all($pattern, $td_data, $matches);

                        //replace all the classes in the sting returned by first regular expression

                        $sub_string = preg_replace('/class=\"(.*?)\"/i', '', $matches[1][0]);

                        //extract only the numbers
                        $buffy_array =  $this->extract_numbers_from_string($sub_string);




                        break;

                    case 'twitter':

                        $twitter_worked = false;

                        //check 1 via https
                        $td_data = $this->get_url("https://twitter.com/$user_id");

                        if ($td_data === false) {
                            $this->log(__FUNCTION__, 'The get_url method FAILED, we are trying again via the api');
                        } else {
                            $pattern = "/title=\"(.*)\"(.*)data-nav=\"followers\"/i";
                            preg_match_all($pattern, $td_data, $matches);
                            if (!empty($matches[1][0])) {
                                //$td_buffer_counter_fix = str_replace(array('.', ',', ' ', '&nbsp;'), '', htmlentities($matches[1][0]));  //old radu hack to filter numbers 2/2/2015
                                $td_buffer_counter_fix = $this->extract_numbers_from_string(htmlentities($matches[1][0]));

                                // $td_buffer_counter_fix = str_replace(',','', $matches[1][0]);
                                $buffy_array = (int) $td_buffer_counter_fix;

                                if (!empty($buffy_array) and is_numeric($buffy_array)) {
                                    $twitter_worked = true; //skip twitter second check it worked!
                                }
                            }

                        }



                        //check 2 via twitter api client - we only get here if the first check did not returned anything
                        if ($twitter_worked === false) {
                            if (!class_exists('TwitterApiClient')) {
                                require_once 'twitter-client.php';
                                $Client = new TwitterApiClient;
                                $Client->set_oauth (YOUR_CONSUMER_KEY, YOUR_CONSUMER_SECRET, SOME_ACCESS_KEY, SOME_ACCESS_SECRET);
                                try {
                                    $path = 'users/show';
                                    $args = array ('screen_name' => $user_id);
                                    $data = @$Client->call( $path, $args, 'GET' );
                                    if (!empty($data['followers_count'])) {
                                        $buffy_array = (int) $data['followers_count'];  //set the buffer
                                    }
                                }
                                catch( TwitterApiException $Ex ){
                                    //twitter rate limit will show here
                                    //print_r($Ex);
                                }
                            }
                        }


                        break;

                    case 'vimeo':
                        //$td_data = @$this->get_json("http://vimeo.com/api/v2/channel/$user_id/info.json");
                        //if (!empty($td_data['total_subscribers'])) {
                        //    $buffy_array = (int) $td_data['total_subscribers'];
                        //} else {
                        $td_data = @$this->get_url("http://vimeo.com/$user_id");
                        $pattern = "/<b class=\"stat_list_count\">(.*?)<\/b>(\s+)<span class=\"stat_list_label\">likes<\/span>/";
                        preg_match($pattern, $td_data, $matches);
                        if (!empty($matches[1])) {
                            $buffy_array = (int) $matches[1];
                        }
                        //}
                        break;

                    case 'youtube':

//						// channel string, if it's present it is not necessary for getting data
//	                    $user_id = str_replace("channel/", "", $user_id);
//
//	                    $td_data = @$this->get_json("http://gdata.youtube.com/feeds/api/users/$user_id?alt=json");
//	                    if (!empty($td_data['entry']['yt$statistics']['subscriberCount'])) {
//		                    $buffy_array = (int) $td_data['entry']['yt$statistics']['subscriberCount'];
//	                    }


						$url = "https://www.googleapis.com/youtube/v3/channels?part=statistics&key=AIzaSyBneuqXGHEXQiJlWUOv23_FA4CzpsHaS6I";

		                $search_id = str_replace("channel/", "", $user_id);

						if (strpos($user_id, "channel/") === 0) {
							$url .= "&id=$search_id";
						} else {
							$url .= "&forUsername=$search_id";
						}

		                $td_data = @$this->get_json($url);

						$subscriberCount = @$td_data['items'][0]['statistics']['subscriberCount'];

						if (!empty($subscriberCount)) {
		                    $buffy_array = (int) $subscriberCount;
	                    }
                        break;

                    case 'googleplus':
                        $td_data = @$this->get_json("https://www.googleapis.com/plus/v1/people/$user_id?key=AIzaSyA1hsdPPNpkS3lvjohwLNkOnhgsJ9YCZWw");
                        if (!empty($td_data['plusOneCount'])) {
                            $buffy_array = (int) $td_data['plusOneCount'];
                        }else{
                            $td_data = @$this->get_url("https://plus.google.com/$user_id/posts");
                            $pattern = "/<span role=\"button\" class=\"d-s o5a\" tabindex=\"0\">(.*?)<\/span>/";
                            preg_match($pattern, $td_data, $matches);
                            if (!empty($matches[1])) {
                                $expl_maches = explode(' ', trim($matches[1]));
                                $buffy_array = str_replace(array('.', ','), array(''), $expl_maches[0]);
                            }
                        }
                        break;

                    case 'instagram':
                        $td_data = @$this->get_url("http://instagram.com/$user_id#");
                        //$pattern = "/followed_by\":(.*?),\"follows\":/";
	                    $pattern = "/followed_by\"\:\{\"count\"\:(.*?)\}\,\"/";
                        preg_match($pattern, $td_data, $matches);
                        if (!empty($matches[1])) {
                            $buffy_array = (int) $matches[1];
                        }
                        break;

                    case 'soundcloud':
                        $td_data = @$this->get_json("http://api.soundcloud.com/users/$user_id.json?client_id=97220fb34ad034b5d4b59b967fd1717e");
                        if (!empty($td_data['followers_count'])) {
                            $buffy_array = (int) $td_data['followers_count'];
                        }
                        break;

                    case 'rss':
                        $buffy_array = (int) $user_id;
                        break;
                }


            // if the cache is disable, do not run the cache things
            if ($disable_cache === true) {
                return $buffy_array;
            }

            //case 1; we have response from service; write the cache arrya with new values
            if ($buffy_array > 0) {

                $local_cash['count'] = $buffy_array;
                if($buffy_array > 0){
                    $local_cash['ok_count'] = $buffy_array;
                }
                $local_cash['timestamp'] = time();
                $local_cash['expires'] = 10800;
                $local_cash['uid'] = $user_id;

                //set to true the flag to save
                $this->do_transient_save = true;

                //send the service array to save the cache
                $this->save_cache($service_id, $local_cash);

                /*
                * case 2; we DON'T have response from service, then get the cache array for this service,
                * and rewrite the array in cache, with default values
                */
                } else {
                    $local_cash = $this->get_cache($service_id, $user_id);

                    if (is_array($local_cash) and isset($local_cash['ok_count']) > 0) {
                        $buffy_array = intval($local_cash['ok_count']);
                    }else{
                        $buffy_array = 0;
                    }

                    $local_cash['timestamp'] = time();
                    $local_cash['count'] = 0;
                    $local_cash['uid'] = $user_id;
                    $local_cash['expires'] = 10800;

                    //set to true the flag to save
                    $this->do_transient_save = true;

                    //send the service array to save the cache
                    $this->save_cache($service_id, $local_cash);
                }
            } catch (Exception $e) {

            }
            return $buffy_array;
            //case 3, the cache is true; time < expire
        } else {

            $local_cash = $this->get_cache($service_id, $user_id);



            if (is_array($local_cash) and array_key_exists('ok_count', $local_cash)) {
                $this->log(__FUNCTION__, "$service_id - $user_id found in cache (ok_count): " . intval($local_cash['ok_count']));
                return intval($local_cash['ok_count']);
            } else {
                $this->log(__FUNCTION__, "$service_id - $user_id found in cache but the cache is empty or something");
                return 0;
            }
        }
    }




    private function log($function_name, $message) {
        $this->log_buffer .=  str_pad($function_name, 30, '.') . " - $message <br>";
    }


    function get_log() {
        return $this->log_buffer;
    }

    function get_log_last_get_url_page_data() {
        return $this->log_last_get_url_page_data;
    }
}