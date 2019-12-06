<?php

class Functions
{
    static function logging($logs, $filename = null)
    {

        if (is_null($filename) || strlen($filename) <= 0)
            $filename = ROOT . DS . "tmp" . DS . "logs" . DS . "log.log";
        else
            $filename = ROOT . DS . "tmp" . DS . "logs" . DS . $filename . ".log";

        ob_start();
        var_dump($logs);
        $data = ob_get_clean();
        $fp = fopen($filename, "a");
        fwrite($fp, date('j F Y - H:i', time()) . " # " . $data . "\r\n");
        fclose($fp);
    }

    static function getRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
                    else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    function png2jpg($originalFile, $outputFile, $quality)
    {
        $file_parts = pathinfo($originalFile);

        if ($file_parts['extension'] == 'gif') {
            $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($file_parts['extension'] == 'jpeg' || $file_parts['extension'] == 'jpg') {
            $imgcreatefrom = "ImageCreateFromJPEG";
        }
        if ($file_parts['extension'] == 'png') {
            $imgcreatefrom = "ImageCreateFromPNG";
        }

        $image = $imgcreatefrom($originalFile);
        imagejpeg($image, $outputFile, $quality);
        imagedestroy($image);
    }


    static function resizeImage($src, $des, $maxEdge = 600)
    {

        $arr_image_details = getimagesize($src);
        $original_width = $arr_image_details[0];
        $original_height = $arr_image_details[1];

        if ($original_width < $maxEdge && $original_height < $maxEdge) {
            copy($src, $des);
            return;
        }

        if ($original_width > $original_height) {
            $new_width = $maxEdge;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $maxEdge;
            $new_width = intval($original_width * $new_height / $original_height);
        }

        $file_parts = pathinfo($src);

        if ($file_parts['extension'] == 'gif') {
            $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($file_parts['extension'] == 'png') {
            $imgcreatefrom = "ImageCreateFromPNG";
        } else {
            //if ($file_parts['extension'] == 'jpeg' || $file_parts['extension'] == 'jpg') {
            $imgcreatefrom = "ImageCreateFromJPEG";
        }

        $old_image = $imgcreatefrom($src);
        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresized($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        ImageJPEG($new_image, $des);
    }

    static function uploadImage($type, $path, $createThumbnail = false)
    {

        $ext = pathinfo($_FILES[$type]["name"], PATHINFO_EXTENSION);
        $target_dir = ROOT . DS . "public" . DS . "uploads" . DS . $path;
        $target_name = time() . "_" . Functions::getRandomString() . "." . $ext;
        $target_file = $target_dir . $target_name;
        move_uploaded_file($_FILES[$type]["tmp_name"], $target_file);

        if ($createThumbnail) {
            // Make Thumbnail
            $thumb_dir = $target_dir . 'thumb' . DS . $target_name;
            Functions::resizeImage($target_file, $thumb_dir, 300);
        }

        return $target_name;
    }

    static function En2Fa_number($str, $mod = 'en', $mf = '٫')
    {
        $num_a = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.');
        $key_a = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', $mf);
        return ($mod == 'fa') ? str_replace($num_a, $key_a, $str) : str_replace($key_a, $num_a, $str);
    }

    static function sendEmail($to, $message, $subject = "", $name = "")
    {

        $mail = new PHPMailer;
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = "";
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = "tls";
        $mail->Port = 26;
        $mail->CharSet = 'utf-8';

        $mail->From = '';
        $mail->FromName = '';

        $mail->addAddress($to);

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $text = "<div style='direction:rtl'>";
        $text .= $name;
        $text .= "<br>";
        $text .= $message;
        $text .= "<br>";
        $text .= "</div>";

        $mail->Body = $text;

        $mail->AltBody = $message;

        $result = $mail->send();
        if ($result) {
            return true;
        } else {
            Mzif::logging($mail);
            return false;
        }
    }

    static function VerifyRecaptcha($g_recaptcha_response)
    {
        $ch = curl_init();
        $curlConfig = array(
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => array(
                'secret' => 'SECRET_KEY',
                'response' => $g_recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        curl_setopt_array($ch, $curlConfig);
        if ($result = curl_exec($ch)) {
            curl_close($ch);
            $response = json_decode($result);
            return $response->success;
        } else {
            return false;
        }
    }

    static function jalaliToMiladi($jalaliDate, $delimiter = '-')
    {
        $jDatetime = explode(' ', $jalaliDate);
        $jDate = explode($delimiter, $jDatetime[0]);
        $gDate = jalali_to_gregorian($jDate[0], $jDate[1], $jDate[2], '-');
        $gDate = $gDate . " " . $jDatetime[1];
        return $gDate;
    }

    static function CallAPI($method, $url, $data = false, $headers = array(), $setXforwarded = false)
    {
        if ($setXforwarded) {
            $clientIP = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            array_push($headers, array('X-FORWARDED-FOR:' . $clientIP));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POST, true);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, true);
                break;

            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}