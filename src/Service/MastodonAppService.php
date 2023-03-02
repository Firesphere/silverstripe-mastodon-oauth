<?php

namespace Firesphere\OAuth2Mastodon\Service;

use Bigfork\SilverStripeOAuth\Client\Authenticator\Authenticator;
use Bigfork\SilverStripeOAuth\Client\Control\Controller;
use Firesphere\OAuth2Mastodon\Model\InstanceCredential;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\NotFoundExceptionInterface;
use SilverStripe\Control\Controller as SilverStripeController;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;

class MastodonAppService
{
    use Configurable;

    private static $uri = '/api/v1/apps';

    /**
     * @param InstanceCredential $instance
     * @return InstanceCredential
     * @throws NotFoundExceptionInterface
     * @throws GuzzleException
     * @throws ValidationException
     */
    public static function createInstanceApp(InstanceCredential $instance): InstanceCredential
    {
        $name = self::config()->get('app_name');
        $scope = Config::inst()->get(Authenticator::class, 'providers');
        $data = [
            'client_name'   => $name ?? 'TestApp',
            'redirect_uris' => self::getRedirectUri(),
            'scopes'        => $scope['Mastodon']['scopes'] ?? 'read', // Assuming the provider name is Mastodon here!
            'website'       => Director::absoluteBaseURL()
        ];

        $client = new Client([
            'base_uri' => rtrim($instance->InstanceUrl, '/')
        ]);

        $response = $client->post(self::$uri, ['json' => $data]);

        $data = json_decode($response->getBody()->getContents(), 1);

        $instance->update([
            'ClientId'     => $data['client_id'],
            'ClientSecret' => $data['client_secret'],
            'VapId'        => $data['vapid_key'],
            'AppId'        => $data['id'],
        ]);

        $instance->write();

        return $instance;
    }

    /**
     * @return string
     * @throws NotFoundExceptionInterface
     */
    protected static function getRedirectUri()
    {
        $configUri = Config::inst()->get(self::class, 'default_redirect_uri');
        if ($configUri) {
            return $configUri;
        }

        $controller = Injector::inst()->get(Controller::class);

        return SilverStripeController::join_links($controller->AbsoluteLink(), 'callback/');
    }
}