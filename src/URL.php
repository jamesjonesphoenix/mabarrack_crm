<?php


namespace Phoenix;

/**
 * Class URL
 *
 * @author James Jones
 * @package Phoenix
 *
 */
class URL
{
    /**
     * @var array
     */
    private array $queryArgs;

    /**
     * @var array|false|int|string|null
     */
    private $urlComponents;

    /**
     * @var string
     */
    private string $hash = '';

    /**
     * URL constructor.
     *
     * @param string $actualLink
     */
    public function __construct(string $actualLink = '')
    {
        if ( empty( $actualLink ) ) {
            $actualLink = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
                ((!empty( $_SERVER['HTTP_HOST'] ) && !empty( $_SERVER['REQUEST_URI'] )) ? "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" : '');
        }

        $this->urlComponents = parse_url( $actualLink );
        $queryArray = [];
        parse_str( $this->urlComponents['query'] ?? '', $queryArray );
        $this->queryArgs = $queryArray;
    }

    /**
     * @return $this
     */
    public function reset(): self
    {
        $this->queryArgs = [];
        $this->hash = '';
        return $this;
    }

    /**
     * @return string
     */
    public function write(): string
    {
        $query = http_build_query( $this->queryArgs );
        return ($this->urlComponents['scheme'] ?? 'https')
            . '://'
            . ($this->urlComponents['host'] ?? $_SERVER['HTTP_HOST'] . '/')
            . $this->urlComponents['path']
            . (!empty( $query ) ? '?' . $query : '')
            . (!empty( $this->hash ) ? '#' . $this->hash : '');
    }

    /**
     * @return array
     */
    public function getQueryArgs(): array
    {
        return $this->queryArgs;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function setQueryArgs(array $args = []): self
    {
        foreach ( $args as $name => $value ) {
            $this->setQueryArg( $name, $value );
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setQueryArg(string $name, $value = ''): self
    {
        if ( $value === false ) {
            return $this->removeQueryArg( $name );
        }
        $this->queryArgs[$name] = $value;
        return $this;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash = ''): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function removeQueryArgs(array $args = []): self
    {
        foreach ( $args as $name ) {
            $this->removeQueryArg( $name );
        }
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeQueryArg(string $name): self
    {
        unset( $this->queryArgs[$name] );
        return $this;
    }
}