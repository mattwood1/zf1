<?php
class God_Model_Curl
{
    public function Curl($referer, $url, $displayWidth = null)
    {
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        //curl_setopt($ch, CURLOPT_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_REFERER, $referer);

        $rawdata=curl_exec($ch);

        if(strpos($rawdata,"Not Found") === false) {
            $im = imagecreatefromstring($rawdata);
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
                header("Content-Type: image/jpeg");
                echo imagejpeg($im);
            }
        } else {
            echo("fail");
        }
        curl_close ($ch);

        echo $im;
    }
}