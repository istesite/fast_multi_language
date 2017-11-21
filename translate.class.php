<?php 

class translate { 
    public static $lang; 
    public static $language = array(); 
    public static $defaultLang = 'tr'; // 'auto' || '' : browser language 
    public static $lang_dir = 'languages'; 
    public static $availableLanguages = array('tr', 'de', 'en', 'ru', 'be', 'uk', 'fr', 'fa', 'ar'); 

    public static $yandexTranslate = TRUE; 
    public static $yandexApiKey = 'trnsl.1.1.20150414T012912Z.76a3eaaf29fb8443.5a5eaf14b74ae29dc8e7df8ae117eeed47287c76'; 
    # generate new API key URL : https://translate.yandex.com/developers/keys 

    protected static $debug = false; 
    protected static $error_reporting; 
    public static $error_log = array(); 

    function __construct($lang = '') { 
        self::$error_reporting = error_reporting(); 
        error_reporting(self::$error_reporting & ~E_WARNING); 

        if ($lang != '' and in_array($lang, self::$availableLanguages)) { 
            self::setTranslate($lang); 
            $_SESSION['language'] = $lang; 
        } 
        else { 
            if (isset($_SESSION['language']) and $_SESSION['language'] != '') { 
                self::setTranslate($_SESSION['language']); 
            } 
            else { 
                $browserDetectedLang = self::detectBrowserLanguage(); 
                if (self::$defaultLang == 'auto' or self::$defaultLang == '' and in_array($browserDetectedLang, self::$availableLanguages)) { 
                    self::setTranslate($browserDetectedLang); 
                    $_SESSION['language'] = $browserDetectedLang; 
                } 
                else { 
                    self::setTranslate(self::$defaultLang); 
                    $_SESSION['language'] = self::$defaultLang; 
                } 
            } 
        } 
    } 

    function __toString() { 
        return self::getCurrentLanguage(); 
    } 

    function loadLang($langFile) { 
        $langArr = array(); 

        if (file_exists($langFile)) { 
            //echo "langFile : $langFile"; 
            $newLang = file_get_contents($langFile); 
            if (function_exists('eval')) { 
                $lng = array(); 
                eval($newLang); 
                $langArr = $lng; 
            } 
            else { 
                $newLang = str_replace("\r", "", $newLang); 
                $newLang = explode("\n", $newLang); 
                foreach ($newLang as $line) { 
                    if ($line != '') { 
                        $line = stripslashes($line); 
                        $xx = str_replace(array("\$lng['", "';"), '', $line); 
                        $vals = explode("']='", $xx); 
                        if (!isset($langArr[$vals[0]]) and isset($vals[1])) { 
                            $langArr[$vals[0]] = $vals[1]; 
                        } 
                    } 
                } 
            } 
        } 

        return $langArr; 
    } 

    function w($str) { 
        if (self::$lang == self::$defaultLang) { 
            return $str; 
        } 
        else { 
            $strHash = self::getHash($str); 
            if (isset(self::$language[self::$lang][$strHash])) { 
                return self::$language[self::$lang][$strHash]; 
            } 
            else { 
                return self::exept($str); 
            } 
        } 
    } 

    function exept($str) { 
        $strHash = self::getHash($str); 
        $cache = array(); 
        $cache[self::$defaultLang] = $str; 
        foreach (self::$availableLanguages as $lngx) { 
            if ($lngx != self::$defaultLang) { 
                if (self::$yandexTranslate and $trnsStr = self::yandexTranslator($str, $lngx)) { 
                    $str = $trnsStr; 
                } 
                $cache[$lngx] = $str; 
                $tempLangFile = self::getLangFile($lngx); 
                $tmpLang = self::loadLang($tempLangFile); 
                if (!isset($tmpLang[$strHash])) { 
                    $keyword = "\$lng['" . $strHash . "']='" . addslashes($str) . "';"; 
                    self::writeFile(self::getLangFile($lngx), $keyword); 
                } 
            } 
            $str = $cache[self::$defaultLang]; 
        } 

        return $cache[self::getCurrentLanguage()]; 
    } 

    function getLangFile($lang) { 
        if (trim($lang) != '') { 
            if ($lang != self::$defaultLang) { 
                $filePath = __DIR__ . '/' . self::$lang_dir . '/' . $lang . '.lng'; 
                if (!file_exists($filePath)) { 
                    file_put_contents($filePath, "# ".self::getLanguageByCode($lang)."\r\n"); 
                } 

                return $filePath; 
            } 
        } 

        return FALSE; 
    } 

    function getHash($str) { 
        return md5($str); 
    } 

    function writeFile($dosya, $str) { 
        $baglan = @fopen("$dosya", 'a'); 
        fputs($baglan, $str . "\n"); 
        fclose($baglan); 
    } 

    function setTranslate($lang) { 
        if ($lang != '') { 
            $lang = substr(strtolower($lang), 0, 2); 
            self::$lang = $lang; 
        } 
        else { 
            $lang = self::$defaultLang; 
        } 

        $langFile = self::getLangFile($lang); 

        if (self::$defaultLang != $lang and file_exists($langFile)) { 
            self::$language[$lang] = self::loadLang($langFile); 
        } 
    } 

    function setDefaultLang($lang) { 
        $lang = strtolower($lang); 
        self::$defaultLang = $lang; 
    } 

    function setLanguageDir($dir) { 
        self::$lang_dir = $dir; 
    } 

    function setNewKeywordFilePrefix($prefix) { 
        self::$newKeywordFilePrefix = $prefix; 
    } 

    function getLanguageData() { 
        return self::$language; 
    } 

    function getCurrentLanguage() { 
        return self::$lang; 
    } 

    function detectBrowserLanguage() { 
        $lang = explode("-", $_SERVER['HTTP_ACCEPT_LANGUAGE']); 
        return $lang[0]; 
        //return strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)); 
    } 

    function setError($error) { 
        self::$error_log[date('d-m-Y H:i:s').substr((string)microtime(), 1, 8)] = $error; 
    } 

    public 
    function __destruct() { 
        if (self::$debug and count(self::$error_log) > 0) { 
            echo "\r\n<pre>" . print_r(self::$error_log, TRUE) . "</pre>\r\n"; 
        } 
        error_reporting(self::$error_reporting); 
    } 

    function getLanguageByCode($langCode) { 
        $langs = array('af'=> 'Afrikanca', 'am'=> 'Amharca', 'ar'=> 'Arapça', 'az'=> 'Azerice', 'ba'=> 'Ba?kurtça', 
                       'be'=> 'Belarusça', 'bg'=> 'Bulgarca', 'bn'=> 'Bengalce', 'bs'=> 'Bo?nakça', 'ca'=> 'Katalanca', 
                       'ceb' => 'Sabuanca', 'cs'=> 'Çekçe', 'cy'=> 'Gal dili', 'da'=> 'Danca', 'de'=> 'Almanca', 
                       'el'=> 'Yunanca', 'en'=> '?ngilizce', 'eo'=> 'Esperanto', 'es'=> '?spanyolca', 'et'=> 'Estonca', 
                       'eu'=> 'Baskça', 'fa'=> 'Farsça', 'fi'=> 'Fince', 'fr'=> 'Frans?zca', 'ga'=> '?rlandaca', 
                       'gd'=> '?skoçça (Kelt dili)', 'gl'=> 'Galiçyaca', 'gu'=> 'Gucaratça', 'he'=> '?branice', 'hi'=> 'Hintçe', 
                       'hr'=> 'H?rvatça', 'ht'=> 'Haiti dili', 'hu'=> 'Macarca', 'hy'=> 'Ermenice', 'id'=> 'Endonezce', 
                       'is'=> '?zlandaca', 'it'=> '?talyanca', 'ja'=> 'Japonca', 'jv'=> 'Cava dili', 'ka'=> 'Gürcüce', 
                       'kk'=> 'Kazakça', 'km'=> 'Khmerce', 'kn'=> 'Kannada dili', 'ko'=> 'Korece', 'ky'=> 'K?rg?zca', 
                       'la'=> 'Latince', 'lb'=> 'Lüksemburgca', 'lo'=> 'Laoca', 'lt'=> 'Litvanca', 'lv'=> 'Letonca', 
                       'mg'=> 'Malga?ça', 'mhr' => 'Mari dili', 'mi'=> 'Maorice', 'mk'=> 'Makedonca', 'ml'=> 'Malayalamca', 
                       'mn'=> 'Mo?olca', 'mr'=> 'Marathi', 'mrj' => 'Bat? Mari dili', 'ms'=> 'Malayca', 'mt'=> 'Maltaca', 
                       'my'=> 'Birmanca', 'ne'=> 'Nepali', 'nl'=> 'Felemenkçe', 'no'=> 'Norveççe', 'pa'=> 'Pencapça', 
                       'pap' => 'Papiamento', 'pl'=> 'Lehçe', 'pt'=> 'Portekizce', 'ro'=> 'Romence', 'ru'=> 'Rusça', 
                       'si'=> 'Seylanca', 'sk'=> 'Slovakça', 'sl'=> 'Slovence', 'sq'=> 'Arnavutça', 'sr'=> 'S?rpça', 
                       'su'=> 'Sundaca', 'sv'=> '?sveçce', 'sw'=> 'Svahili', 'ta'=> 'Tamilce', 'te'=> 'Teluguca', 
                       'tg'=> 'Tacikçe', 'th'=> 'Taylandça', 'tl'=> 'Tagalogca', 'tr'=> 'Türkçe', 'tt'=> 'Tatarca', 
                       'udm' => 'Udmurtça', 'uk'=> 'Ukraynaca', 'ur'=> 'Urduca', 'uz'=> 'Özbekçe', 'vi'=> 'Vietnamca', 
                       'xh'=> 'Xhosa dili', 'yi'=> 'Yidi?', 'zh'=> 'Çince'); 
        if(isset($langs[$langCode])){ 
            return $langs[$langCode]." [".$langCode."]"; 
        } 
        else{ 
            return "[".$langCode."]"; 
        } 
    } 

    # Yandex Translate 
    function yandexLangDetect($text) { 
        $url = 'https://translate.yandex.net/api/v1.5/tr.json/detect?key=' . self::$yandexApiKey; 
        $url .= '&text=' . rawurlencode($text); 

        $result = json_decode(self::yandexPort($url)); 
        if ($result->code == '200') { 
            return $result->lang; 
        } 
        else { 
            return FALSE; 
        } 
    } 

    function yandexSupportedLangs() { 
        $url = 'https://translate.yandex.net/api/v1.5/tr.json/getLangs?key=' . self::$yandexApiKey; 
        $url .= '&ui=' . self::$defaultLang; 

        $result = json_decode(self::yandexPort($url)); 
        if (!isset($result->code)) { 
            //return $result->dirs; 
            return $result->langs; 
        } 
        else { 
            return array(); 
        } 
    } 

    function yandexTranslator($text, $toLang) { 
        $url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . self::$yandexApiKey; 
        $url .= '&lang=' . self::$defaultLang . '-' . $toLang; 
        $url .= '&text=' . rawurlencode($text); 
        $url .= '&format=plain'; 
        self::setError($url); 

        $result = json_decode(self::yandexPort($url)); 

        if ($result->code == '200') { 
            return $result->text[0]; 
        } 

        return FALSE; 
    } 

    function yandexPort($url) { 
        $ch = curl_init($url); 
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        $html = curl_exec($ch); 
        curl_close($ch); 

        return $html; 
    } 
} 