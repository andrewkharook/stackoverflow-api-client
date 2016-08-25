<?php

namespace Stackoverflow;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
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
     * @var \GuzzleHttp\ClientInterface
     */
    protected $http;

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
            $this->response = $this->getHttpClient()->request('get', static::API_URL, array('query'=>$this->options));
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
            'site' => 'stackoverflow',
            'order' => 'desc',
            'sort' => 'activity',
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
     * @param \GuzzleHttp\ClientInterface $http
     */
    public function setHttpClient(ClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * Get the Http Client object
     *
     * @return \GuzzleHttp\ClientInterface implementation
     */
    public function getHttpClient()
    {
        if (is_null($this->http)) {
            $this->http = $this->createDefaultHttpClient();
        }

        return $this->http;
    }

    /**
     * Initialize and return default Http Client
     *
     * @return \GuzzleHttp\Client
     */
    protected function createDefaultHttpClient()
    {
        $options = array();

        return new Client($options);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
