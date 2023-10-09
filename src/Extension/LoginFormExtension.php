<?php

namespace Firesphere\OAuth2Mastodon\Extension;

use Bigfork\SilverStripeOAuth\Client\Authenticator\Authenticator;
use Bigfork\SilverStripeOAuth\Client\Control\Controller;
use Bigfork\SilverStripeOAuth\Client\Form\LoginForm;
use Firesphere\OAuth2Mastodon\Model\InstanceCredential;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\View\Requirements;

/**
 * Class \Firesphere\OAuth2Mastodon\Extension\LoginFormExtension
 *
 * @property LoginForm|LoginFormExtension $owner
 */
class LoginFormExtension extends Extension
{

    /**
     * @param FieldList $fields
     * @return void
     */
    public function updateFormFields($fields) {
        $providers = Config::inst()->get(Authenticator::class, 'providers');
        $total = count($providers);
        $push = 0;
        $offset = floor(12/$total);
        // What the fuck am I doing here?
        foreach ($providers as $provider => $config) {
            if ($provider === 'Mastodon') {
                $instanceList = InstanceCredential::get()->map('ID', 'InstanceUrl')->toArray();
                $instanceList[-1] = 'Other instance';
                ksort($instanceList);
                $dropdown = DropdownField::create('Instance', 'Instance', $instanceList);
                $dropdown->setEmptyString('-- Select or enter below --');
                $dropdown->addExtraClass('form-select');
                $composite = CompositeField::create();
                $composite->setTitle('Mastodon');
                $composite->push($dropdown);
                $composite->push(TextField::create('InstanceUrl', 'Instance URL'));
                $composite->addExtraClass('col-' . $offset . ' offset-' . $push);
                $fields->push($composite);
            }
            $push += $offset;
        }
    }

    /**
     * @param string $name
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function onBeforeHandleProvider($name)
    {
        if ($name === 'Mastodon') {
            $request = Controller::curr()->getRequest();
            $post = $request->postVars();
            $instanceSearch = !empty($post['Instance']) && (int)$post['Instance']  > 0 ? (int)$post['Instance'] : $post['InstanceUrl'];

            $instanceApp = InstanceCredential::getAppFor($instanceSearch);
            $session = $request->getSession();
            $session->set('MastodonInstance', $instanceApp->ID);
        }
    }
}