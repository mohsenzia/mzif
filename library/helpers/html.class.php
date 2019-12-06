<?php

class HTML
{
    function shortenUrls($data)
    {
        $data = preg_replace_callback('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', array(get_class($this), '_fetchTinyUrl'), $data);
        return $data;
    }

    private function _fetchTinyUrl($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url[0]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return '<a href="' . $data . '" target = "_blank" >' . $data . '</a>';
    }

    static function includeJs($fileName)
    {
        if (file_exists(ROOT . '/public/assets/js/' . $fileName . '.js')) {
            echo '<script src="' . BASE_PATH . '/public/assets/js/' . $fileName . '.js?v=' . time() . '"></script>';
        }

    }

    static function includeCss($fileName)
    {
        if (file_exists(ROOT . '/public/assets/css/' . $fileName . '.css')) {
            echo '<link rel="stylesheet" href="' . BASE_PATH . '/public/assets/css/' . $fileName . '.css?v=' . time() . '"/>';
        }
    }

    static function link($text, $path, $params = null)
    {
        $path = str_replace(' ', '%20', $path);
        $data = '<a href="' . BASE_PATH . '/' . $path . '" ';
        if (!is_null($params))
            foreach ($params as $key => $val) {
                $data .= $key . "='" . $val . "' ";
            }
        $data .= '>' . $text . '</a>';
        echo $data;
    }

}