<?php

namespace Sludio\HelperBundle\Script\Twig;

class BeautifyExtension extends \Twig_Extension
{
    use TwigTrait;

    const INFO = 'info';
    const SUCCESS = 'success';
    const REDIRECT = 'redirect';
    const CLIENT = 'client_error';
    const SERVER = 'server_error';

    protected $appDir;
    private $paths = [];

    public function __construct($shortFunctions)
    {
        global $kernel;

        $this->shortFunctions = $shortFunctions;
        $this->appDir = $kernel->getRootDir();
    }

    public function getName()
    {
        return 'sludio_helper.twig.beautify_extension';
    }

    public function getFilters()
    {
        $input = [
            'beautify' => 'beautify',
            'urldecode' => 'url_decode',
            'parse' => 'parse',
            'file_exists' => 'file_exists',
            'html_entity_decode' => 'html_entity_decode',
            'strip_descr' => 'strip_descr',
            'pretty_print' => 'prettyPrint',
            'status_code_class' => 'statusCodeClass',
            'format_duration' => 'formatDuration',
            'short_uri' => 'shorthenUri',
            'is_ie' => 'isIE',
            'asset_version' => 'getAssetVersion',
            'usort' => 'usortFunction',
        ];

        return $this->makeArray($input);
    }

    public function getFunctions()
    {
        $input = [
            'detect_lang' => 'detectLang',
        ];

        return $this->makeArray($input, 'function');
    }

    public function url_decode($string)
    {
        return urldecode($string);
    }

    public function parse($string)
    {
        $str = parse_url($string);

        $argv = [];
        if (isset($str['query'])) {
            $args = explode('&', $str['query']);

            foreach ($args as $arg) {
                $tmp = explode('=', $arg, 2);
                $argv[$tmp[0]] = $tmp[1];
            }
        }

        return $argv;
    }

    public function file_exists($file)
    {
        return file_exists(getcwd().$file);
    }

    public function beautify($string)
    {
        $explode = explode('/', strip_tags($string));
        $string = implode(' / ', $explode);

        return $string;
    }

    public function html_entity_decode($str)
    {
        $str = html_entity_decode($str);
        $str = preg_replace('#\R+#', '', $str);

        return $str;
    }

    public function strip_descr($body, $fallback = null, $type = null, $lengthIn = 300)
    {
        if ($type && $fallback) {
            $length = isset($fallback[$type]) ? $fallback[$type] : null;
        }
        if (!isset($length)) {
            $length = $lengthIn;
        }

        if (strlen($body) > $length) {
            $body = substr($body, 0, strpos($body, ' ', $length)).'...';
        }

        return $body;
    }

    public function detectLang($body)
    {
        switch (true) {
            case 0 === strpos($body, '<?xml'):
                return 'xml';
            case 0 === strpos($body, '{'):
            case 0 === strpos($body, '['):
                return 'json';
            default:
                return 'markup';
        }
    }

    public function prettyPrint($code, $lang)
    {
        switch ($lang) {
            case 'json':
                return json_encode(json_decode($code), JSON_PRETTY_PRINT);
            case 'xml':
                $xml = new \DomDocument('1.0');
                $xml->preserveWhiteSpace = false;
                $xml->formatOutput = true;
                $xml->loadXml($code);

                return $xml->saveXml();
            default:
                return $code;
        }
    }

    public function statusCodeClass($statusCode)
    {
        $codes = [
            5 => self::SERVER,
            4 => self::CLIENT,
            3 => self::REDIRECT,
            2 => self::SUCCESS,
            1 => self::INFO,
        ];
        $code = (int)floor(intval($statusCode) / 100);

        return isset($codes[$code]) ? $codes[$code] : 'unknown';
    }

    public function formatDuration($seconds)
    {
        $formats = [
            '%.2f s',
            '%d ms',
            '%d µs',
        ];

        while ($format = array_shift($formats)) {
            if ($seconds > 1) {
                break;
            }

            $seconds *= 1000;
        }

        return sprintf($format, $seconds);
    }

    public function shortenUri($uri)
    {
        $parts = parse_url($uri);

        return sprintf('%s://%s%s', isset($parts['scheme']) ? $parts['scheme'] : 'http', $parts['host'], isset($parts['port']) ? (':'.$parts['port']) : '');
    }

    public function isIE()
    {
        $request = Request::createFromGlobals();
        $agent = $request->server->get('HTTP_USER_AGENT');
        if (strpos($agent, 'MSIE') || strpos($agent, 'Edge') || strpos($agent, 'Trident/7')) {
            return 1;
        }

        return 0;
    }

    public function getAssetVersion($filename)
    {
        if (count($this->paths) === 0) {
            $manifestPath = $this->appDir.'/Resources/assets/rev-manifest.json';
            if (!file_exists($manifestPath)) {
                return $filename;
            }
            $this->paths = json_decode(file_get_contents($manifestPath), true);
            if (!isset($this->paths[$filename])) {
                return $filename;
            }
        }

        return $this->paths[$filename];
    }

    public function cmpOrderBy($aVar, $bVar)
    {
        $aValue = $aVar->{'get'.ucfirst($this->param)}();
        $bValue = $bVar->{'get'.ucfirst($this->param)}();
        switch ($this->order) {
            case 'asc':
                return $aValue > $bValue;
            case 'desc':
                return $aValue < $bValue;
        }
    }

    public function usortFunction($objects, $parameter, $order = 'asc')
    {
        $this->param = $parameter;
        $this->order = strtolower($order);

        if (is_object($objects)) {
            $objects = $objects->toArray();
        }
        usort($objects, [
            __CLASS__,
            'cmpOrderBy',
        ]);

        return $objects;
    }
}
