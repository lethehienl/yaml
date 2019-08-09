<?php
require_once  "./vendor/lethehienl/yaml/src/Yaml.php";

use Symfony\Component\Yaml\Yaml;
class trans
{
    protected static $instance = null;
    public $langArr = ['en' => 'english', 'de' => 'german'];
    public $langDatetime = [
        'en' => 'Y-m-d H:i:s',
        'de' => 'd.m.Y H:i:s'
    ];
    public $langDate = [
        'en' => 'Y-m-d',
        'de' => 'd.m.Y'
    ];
    public $currentLanguage = 'de';
    public $dateFormatToSave = 'Y-m-d';
    public $dateTimeFormatToSave = 'Y-m-d H:i:s';

    private $data;
    private $defaultLoad = array('general');

    public function __construct($pages = null)
    {

        if (!empty($_GET['language']) && in_array($_GET['language'], $this->langArr)) {
            foreach ($this->langArr as $key => $lang) {
                if ($lang == $_GET['language']) {
                    setcookie('language', $key);
                    $this->currentLanguage = $key;
                }
            }
        } elseif (isset($_COOKIE['language']) && array_key_exists($_COOKIE['language'], $this->langArr)) {
            $this->currentLanguage = $_COOKIE['language'];

        } else {
            $this->currentLanguage = getenv('DEFAULT_LANGUAGE') ?? $this->currentLanguage;
        }

        $this->data = $this->loadLanguageFile($pages);
    }

    /**
     * Apply singleton
     * @return null
     */
    public static function getInstance($pages = null)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static($pages);
        }
        return static::$instance;
    }

    /**
     * load data to transalte
     * @param null $page
     * @return array
     */
    private function loadLanguageFile($page = null)
    {
        $data = array();
        $filesArr = $this->defaultLoad;

        if (is_string($page)) {
            $filesArr[] = $page;
        } elseif (is_array($page)) {
            $filesArr = array_merge($filesArr, $page);
        }

        foreach ($filesArr as $file) {
            $fileName = __DIR__ . '/../src/' . $this->currentLanguage . '/' . $file . '.yml';
            $fileData = Yaml::parseFile($fileName);
            if (is_array($fileData)) {
                $data = array_merge($data, $fileData);
            }
        }

        return $data;
    }

    /**
     * Translate text
     * @param $text
     * @return array|mixed
     */
    public function t($text, $replaces = array())
    {
        $data = $this->data;

        if (strpos($text, ' ') === false) {
            $textArr = explode('.', $text);
        } else {
            $textArr = array($text);
        }

        for ($i = 0; $i < count($textArr); $i++) {
            $key = $textArr[$i];

            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = $key;
                break;
            }
        }
        if (!empty($replaces)) {
            foreach ($replaces as $key => $value) {
                $data = str_replace($key, $value, $data);
            }
        }


        echo $data;
        return $data;
    }

    /**
     * Translate text
     * @param $text
     * @return array|mixed
     */
    public function tt($text, $replaces = array())
    {
        $data = $this->data;

        if (strpos($text, ' ') === false) {
            $textArr = explode('.', $text);
        } else {
            $textArr = array($text);
        }

        for ($i = 0; $i < count($textArr); $i++) {
            $key = $textArr[$i];

            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = $key;
                break;
            }
        }

        if (!empty($replaces)) {
            foreach ($replaces as $key => $value) {
                $data = str_replace($key, $value, $data);
            }
        }

        return $data;
    }

    /**
     * @param $value
     * @param $curFormat
     * @return bool|string
     */
    public function toDateTime($value, $curFormat = 'Y-m-d H:i:s')
    {
        $langFormat = $this->langDatetime[$this->currentLanguage];
        if ($value instanceof DateTime) {
            return $value->format($langFormat) . ($this->currentLanguage == 'de' ? ' Uhr' : '');
        }
//        if ($curFormat == $langFormat) {
//          return $value . ($this->currentLanguage == 'de' ? ' Uhr' : '');
//        }
        if ($curFormat) {
            $date = DateTime::createFromFormat($curFormat, $value);
            return date_format($date, $langFormat) . ($this->currentLanguage == 'de' ? ' Uhr' : '');
        }
        return false;
    }

    /**
     * @param $value
     * @param $curFormat
     * @return bool|string
     */
    public function toDate($value, $curFormat = 'Y-m-d H:i:s')
    {

        if (is_string($value)) {
            $value = new \DateTime($value);
        }

        $langFormat = $this->langDate[$this->currentLanguage];
        return $value->format($langFormat);

        return false;
    }

    /**
     * @param $value
     * @param $curFormat
     * @return bool|string
     */
    public function toMediumDateTime($value)
    {

        if (is_string($value)) {
            $value = new \DateTime($value);
        }
        if($this->currentLanguage == 'en'){
            $langFormat = 'Y-m-d H:i';
        }
        else{
            $langFormat = 'd.m.Y H:i';
        }
        return $value->format($langFormat);

    }

    /**
     * Change date format to save
     */
    public function formatDateToSave($value)
    {

        $langFormat = $this->langDate[$this->currentLanguage];

        if ($value instanceof DateTime) {
            return $value->format($this->dateFormatToSave);
        }

        //$date = DateTime::createFromFormat($langFormat, $value);
        $date = new \DateTime($value);
        return date_format($date, $this->dateFormatToSave);

    }

    /**
     * Change datetime format to save
     */
    public function formatDateTimeToSave($value)
    {
        $langFormat = $this->langDatetime[$this->currentLanguage];

        if ($value instanceof DateTime) {
            return $value->format($this->dateTimeFormatToSave);
        }
        $date = DateTime::createFromFormat($langFormat, $value);
        return date_format($date, $this->dateTimeFormatToSave);

    }
}