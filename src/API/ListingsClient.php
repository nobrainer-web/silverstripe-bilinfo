<?php


namespace NobrainerWeb\Bilinfo\API;


use GuzzleHttp\Client;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class ListingsClient
{
    use Injectable;

    /**
     * @var string
     */
    protected $baseUrl = 'https://gw.bilinfo.net';

    /**
     * @var string
     */
    protected $endpoint = '/listingapi/api/export';

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

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

    /**
     * params set as GET variables on the request
     *
     * @var array
     */
    protected $params = [];

    public function __construct(string $username = '', string $password = '')
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

    /**
     * Gets entire response data as array
     *
     * @return array
     */
    public function get(): array
    {
        $this->request();
        $formatter = DataFormatter::create($this->response->getBody());
        $data = $formatter->output();

        return $data;
    }

    protected function request()
    {
        // TODO do actual request to the API using Guzzle
        $client = new Client(['base_uri' => $this->baseUrl]);
        $this->response = $client->request('GET', $this->endpoint, [
            'auth' => [
                $this->username,
                $this->password
            ],
            'query' => $this->params
        ]);

        return $this->response;
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

    /**
     * Set dealerId in request params
     *
     * @param $dealerId
     * @return ListingsClient
     */
    public function setDealerId($dealerId): self
    {
        $this->addParam('dealerId', $dealerId);

        return $this;
    }

    /**
     * Set GET param
     *
     * @param $key
     * @param $val
     * @return ListingsClient
     */
    public function addParam($key, $val): self
    {
        $this->params[$key] = $val;

        return $this;
    }
}