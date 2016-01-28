<?php
class God_Model_Curl
{
    protected $_rawdata;
    protected $_timeout;
    protected $_statusCode;
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

        $this->_lasturl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        return $this->_rawdata;
    }

    public function statusCode()
    {
        return $this->_statusCode;
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

                if ($displayWidth) {
                    $width = imagesx( $im );
                    $height = imagesy( $im );

                    $newwidth = $displayWidth;
                    $newheight = ($height/$width) * $newwidth;

                    // Create a new temporary image.
                    $tmpimg = imagecreatetruecolor( $newwidth, $newheight );

                    // Copy and resize old image into new image.
                    imagecopyresampled( $tmpimg, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

                    // Output new file.
                    $im = $tmpimg;
                }
                return imagejpeg($im);
            }
        } else {
            return false;
        }
    }
}