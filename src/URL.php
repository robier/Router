<?php

namespace Robier\Router;

class URL
{
    const ABSOLUTE_URL = 1;
    const ABSOLUTE_PATH = 2;
    const NETWORK_URL = 3;

    protected static $default = self::ABSOLUTE_URL;

    protected $host;
    protected $path;
    protected $query;
    protected $secured;

    protected $fragment;
    protected $username;
    protected $password;
    protected $port;

    public function __construct($host, $path = null, array $query = [])
    {
        $this->setHost($host);
        if (!empty($path)) {
            $this->setPath($path);
        }
        $this->query = $query;
    }

    public static function setDefault($type)
    {
        static::$default = $type;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setFragment($data)
    {
        $this->fragment = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function secured($bool)
    {
        $this->secured = (bool)$bool;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = trim($path, '/');
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addToQuery(array $data)
    {
        $this->query = array_merge($this->query, $data);
        return $this;
    }

    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSecured()
    {
        return $this->secured;
    }

    public function toArray()
    {
        $return = [];

        if ($this->isSecured()) {
            $return['scheme'] = 'https';
        } else {
            $return['scheme'] = 'http';
        }

        $return['host'] = $this->getHost();

        if (!empty($this->path)) {
            $return['path'] = $this->getPath();
        }

        if (!empty($this->query)) {
            $return['query'] = http_build_query($this->getQuery());
        }

        if (!empty($this->fragment)) {
            $return['fragment'] = $this->getFragment();
        }

        return $return;
    }

    public function getProtocol()
    {
        return $this->isSecured() ? 'https' : 'http';
    }

    protected function getUrl($type = null)
    {
        if (null === $type) {
            $type = static::$default;
        }

        switch ($type) {
            case self::ABSOLUTE_URL:
                return $this->getProtocol() . '://' . $this->getCredentials() . $this->host;
            case self::NETWORK_URL:
                return '//' . $this->getCredentials() . $this->host;
            case self::ABSOLUTE_PATH:
                return '';
        }
        return '';
    }

    protected function getCredentials()
    {
        if ($this->username && $this->password) {
            return $this->username . ':' . $this->password . '@';
        }
        return '';
    }

    public function toString($type = null)
    {
        $url = $this->getUrl($type);

        if (!empty($this->path)) {
            $url .= '/' . $this->path;
        }

        if (!empty($this->query)) {
            $url .= '?' . http_build_query($this->query);
        }

        if (!empty($this->fragment)) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toLink($type = null)
    {
        return htmlspecialchars($this->toString($type));
    }
}