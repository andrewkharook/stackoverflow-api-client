<?php

namespace Stackoverflow;


use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Stackoverflow\Exception\StackoverflowException;

/**
 * Class Search
 *
 * @docs https://api.stackexchange.com/docs/search
 * @package Stackoverflow
 */
class Search
{
    /**
     * API base URI
     */
    const API_URL = 'https://api.stackexchange.com/2.2/search';

    /**
     * @var \Http\Client\HttpClient
     */
    protected $http;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Set of options used to build the API query
     *
     * @var array
     */
    protected $options = array();

    /**
     * Search constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        try {
            $this->options = $resolver->resolve($options);
        } catch (UndefinedOptionsException $e) {
            throw new Exception\StackoverflowException($e->getMessage());
        }
    }

    /**
     * Run search
     *
     * @return string
     */
    public function run()
    {
        try {
            $this->response = $this->getHttpClient()->sendRequest($this->getRequest());
        } catch (\Exception $e) {
            throw new Exception\StackoverflowException($e->getMessage());
        }

        return $this->response->getBody()->getContents();
    }

    /**
     * Define required and available options, set default values
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'site'  => 'stackoverflow',
            'order' => 'desc',
            'sort'  => 'activity',
        ));
        $resolver->setDefined(array(
            'tagged',
            'intitle',
            'nottagged',
            'page',
            'pagesize',
            'fromdate',
            'todate',
            'min',
            'max',
        ));

        $resolver->setAllowedTypes('site', 'string');
        $resolver->setAllowedTypes('order', 'string');
        $resolver->setAllowedTypes('sort', 'string');
        $resolver->setAllowedTypes('nottagged', array('null', 'string'));
        $resolver->setAllowedTypes('tagged', array('null', 'string'));
        $resolver->setAllowedTypes('intitle', array('null', 'string'));
        $resolver->setAllowedTypes('page', array('null', 'int'));
        $resolver->setAllowedTypes('pagesize', array('null', 'int'));
        $resolver->setAllowedTypes('fromdate', array('null', 'int'));
        $resolver->setAllowedTypes('todate', array('null', 'int'));
        $resolver->setAllowedTypes('min', array('null', 'int'));
        $resolver->setAllowedTypes('max', array('null', 'int'));

        $resolver->setAllowedValues('order', array('asc', 'desc'));
        $resolver->setAllowedValues('sort', array('activity', 'creation', 'votes', 'relevance'));

        // "nottagged" is applicable only if "tagged" is set
        $resolver->setNormalizer('nottagged', function (Options $options, $value) {
            if (!$options['tagged']) {
                $value = null;
            }

            return $value;
        });

        // If "sort" is set to "relevance", "min" and "max" options are not accepted
        $resolver->setNormalizer('min', function (Options $options, $value) {
            if ('relevance' === $options['sort']) {
                $value = null;
            }

            return $value;
        });

        // If "sort" is set to "relevance", "min" and "max" options are not accepted
        $resolver->setNormalizer('max', function (Options $options, $value) {
            if ('relevance' === $options['sort']) {
                $value = null;
            }

            return $value;
        });
    }

    /**
     * Set the Http Client attribute
     *
     * @param \Http\Client\HttpClient $http
     */
    public function setHttpClient(HttpClient $http = null)
    {
        if (is_null($http)) {
            $http = $this->createDefaultHttpClient();
        }

        $this->http = $http;
    }

    /**
     * Get the Http Client object
     *
     * @return \Http\Client\HttpClient implementation
     */
    public function getHttpClient()
    {
        if (is_null($this->http)) {
            $this->setHttpClient();
        }

        return $this->http;
    }

    /**
     * Initialize and return default Http Client
     *
     * @return \Http\Client\HttpClient
     */
    protected function createDefaultHttpClient()
    {
        return HttpClientDiscovery::find();
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = $this->createRequest('GET', static::API_URL, $this->options);
        }

        return $this->request;
    }

    /**
     * @param string $method
     * @param string $baseUrl
     * @param array  $opts
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function createRequest($method, $baseUrl, array $opts)
    {
        $requestFactory = MessageFactoryDiscovery::find();

        $req = $requestFactory->createRequest($method, $baseUrl);

        return $req->withUri($req->getUri()->withQuery(http_build_query($opts)));
    }
}
