<?php
class God_Model_Curl
{
    protected $_rawdata;
    protected $_timeout;
    protected $_statusCode;
    protected $_contentType;
    protected $_contentLength;
    protected $_lasturl;

    public function Curl($url, $referer = null, $binary = false, $timeout = 30, $followredir = false)
    {
        $this->_timeout = $timeout;
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.114 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $binary ? curl_setopt($ch, CURLOPT_BINARYTRANSFER,1): '';
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        //curl_setopt($ch, CURLOPT_VERIFYHOST, 0);
        $referer ? curl_setopt($ch, CURLOPT_REFERER, $referer): '';
        $followredir? curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1): '';

        $this->_rawdata = curl_exec($ch);

        $this->_statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->_contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $this->_contentLength = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

        $this->_lasturl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        return $this->_rawdata;
    }

    public function statusCode()
    {
        return $this->_statusCode;
    }

    public function contentType()
    {
        return $this->_contentType;
    }

    public function contentLength()
    {
        return $this->_contentLength;
    }
    
    public function rawdata()
    {
        return $this->_rawdata;
    }

    public function lastUrl()
    {
        return $this->_lasturl;
    }

    public function image($displayWidth = null) {
        if(strpos($this->_rawdata,"Not Found") === false) {
            $im = imagecreatefromstring($this->_rawdata);
            if ($im !== null) {

                    $width = imagesx($im);
                    $height = imagesy($im);

                    if ($displayWidth) {
                        $newwidth = $displayWidth;
                        $newheight = ($height / $width) * $newwidth;
                    } else {
                        $newwidth = $width;
                        $newheight = $height;
                    }

                    // Create a new temporary image.
                    $tmpimg = imagecreatetruecolor($newwidth, $newheight);

                    // Copy and resize old image into new image.
                    imagecopyresampled($tmpimg, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                    // Output new file.
                    $im = $tmpimg;
                return imagejpeg($im);
            }
        } else {
            return false;
        }
    }

    public function normalizeURL($url, $root='')
    {
//        _d(array('original_url' => $url));

        $p_url = parse_url($url);

        $r_url = array(
            'scheme' => "http",
            'host' => "",
            'path' => "",
            'query' => ""
        );

        if ($root) {
            $r_url = array_merge($r_url, parse_url($root));
        }

//        _d(array('r_url' => $r_url, 'p_url' => $p_url));

        if (array_key_exists('path', $p_url) && !array_key_exists('host', $p_url) && !$root) {

            $p_path = explode("/", $p_url['path']);

            $p_url['host'] = $p_path[0];
            unset($p_path[0]);
            $p_url['path'] = implode("/", $p_path);
        }

        if ($root && !array_key_exists('host', $p_url)) {
            @$p_url['path'] = $r_url['path'] . $p_url['path'];
        }

//        _d(array('p_url_clean' => $p_url));

        if (array_key_exists('path', $p_url)) {
            $p_url['path'] = implode('/', array_filter(explode('/', $p_url['path'])));
        }

        $p_url = array_merge($r_url, $p_url);

//        _d(array('p_url_merged' => $p_url));

        $url = $p_url['scheme'] . '://' . $p_url['host'];
        $url .= $p_url['path'] ? '/' . $p_url['path'] : '';
        $url .= $p_url['query'] && !$root ? '?' . $p_url['query'] : '';

        return $url;
    }
}