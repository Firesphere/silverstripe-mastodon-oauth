<?php

namespace Firesphere\OAuth2Mastodon\Model;

use Firesphere\OAuth2Mastodon\Service\MastodonAppService;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\NotFoundExceptionInterface;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;

/**
 * Class \Firesphere\OAuth2Mastodon\Model\InstanceCredential
 *
 * @property string $Name
 * @property string $InstanceUrl
 * @property string $ClientId
 * @property string $ClientSecret
 * @property string $Vapid
 * @property int $AppId
 * @method DataList|Member[] Members()
 */
class InstanceCredential extends DataObject
{

    private static $table_name = 'InstanceCredential';

    private static $db = [
        'Name'         => 'Varchar(255)',
        'InstanceUrl'  => 'Varchar(255)',
        'ClientId'     => 'Varchar(255)',
        'ClientSecret' => 'Varchar(255)',
        'Vapid'        => 'Varchar(255)',
        'AppId'        => DBInt::class,
    ];

    private static $has_many = [
        'Members' => Member::class
    ];

    /**
     * @throws GuzzleException
     * @throws NotFoundExceptionInterface
     * @throws ValidationException
     */
    public static function getAppFor($instanceUrl, $params = [])
    {
        // If we get an ID, we know what instance we want.
        if (is_int($instanceUrl)) {
            return self::get()->byID($instanceUrl);
        }
        $instanceUrl = self::addhttp($instanceUrl);
        $instance = self::get()->filter(['InstanceUrl' => $instanceUrl])->first();


        if (!$instance) {
            $newInstanceData = array_merge(['InstanceUrl' => $instanceUrl], $params);
            $instance = self::create($newInstanceData);
            $instance->write();
        }
        if (!$instance->ClientId || !$instance->ClientSecret) {
            MastodonAppService::createInstanceApp($instance);
        }

        return $instance;
    }

    protected static function addhttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "https://" . $url;
        }
        return $url;
    }
}