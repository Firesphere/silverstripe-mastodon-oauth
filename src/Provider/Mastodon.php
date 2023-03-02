<?php

namespace Firesphere\OAuth2Mastodon\Provider;

use Bigfork\SilverStripeOAuth\Client\Control\Controller;
use Firesphere\OAuth2Mastodon\Model\InstanceCredential;
use GuzzleHttp\Exception\GuzzleException;
use Lrf141\OAuth2\Client\Provider\Mastodon as BaseMastodon;
use Psr\Container\NotFoundExceptionInterface;
use SilverStripe\ORM\ValidationException;

class Mastodon extends BaseMastodon
{

    /**
     * @throws NotFoundExceptionInterface
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $instanceId = Controller::curr()->getRequest()->getSession()->get('MastodonInstance');

        $instanceApp = InstanceCredential::getAppFor($instanceId);
        $options = array_merge(
            $options,
            [
                'clientId'     => $instanceApp->ClientId,
                'clientSecret' => $instanceApp->ClientSecret,
                'instance'     => $instanceApp->InstanceUrl,
            ]
        );

        parent::__construct($options, $collaborators);
    }
}