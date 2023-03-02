<?php

namespace Firesphere\OAuth2Mastodon\Extension;

use Firesphere\OAuth2Mastodon\Model\InstanceCredential;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\OAuth2Mastodon\Extension\MemberExtension
 *
 * @property int $MastodonInstanceID
 * @method InstanceCredential MastodonInstance()
 */
class MemberExtension extends DataExtension
{

    private static $has_one = [
        'MastodonInstance' => InstanceCredential::class
    ];
}