<?php


namespace NobrainerWeb\Bilinfo\API;


use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class ListingsClient
{
    use Configurable;
    use Injectable;

    /**
     * @var string
     */
    protected $endpoint = 'https://gw.bilinfo.net/listingapi/api/export';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * API supports xml or json
     *
     * application/xml  or  application/json
     *
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $username
     * @return ListingsClient
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function get()
    {
        // TODO validate any errors from request etc
        $this->request();
        $formatter = DataFormatter::create($this->response->getBody());

        return $formatter->output();
    }

    protected function request()
    {
        // TODO do actual request to the API using Guzzle
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        if ($contentType !== 'application/xml' && $contentType !== 'application/json') {
            throw new InvalidArgumentException('format must be application/xml or application/json');
        }

        $this->contentType = $contentType;

        return $this;
    }

    public function getDealerListings(string $dealerId)
    {
        // TODO
    }
}