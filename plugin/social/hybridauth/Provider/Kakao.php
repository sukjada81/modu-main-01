<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

/**
 * Hybrid_Providers_Kakao provider adapter based on OAuth2 protocol
 * Copyright (c) 2022더도매
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;
use Hybridauth\HttpClient;

/**
 * Kakao OAuth2 provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback' => Hybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys' => ['id' => '', 'secret' => ''], *       
 *   ];
 *
 *   $adapter = new Hybridauth\Provider\Kakao($config);
 *
 *   try {
 *       $adapter->authenticate();
 *
 *       $userProfile = $adapter->getUserProfile();
 *       $tokens = $adapter->getAccessToken(); *       
 *   } catch (\Exception $e) {
 *       echo $e->getMessage() ;
 *   }
 */
class Kakao extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    // phpcs:ignore
    protected $scope = '';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://kapi.kakao.com/v2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://kauth.kakao.com/oauth/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://kauth.kakao.com/oauth/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.kakao.com/docs/latest/ko/kakaologin/common';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();	
		
		if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ];
        }
    }

	protected function authenticateBegin() 
	{
		$token = $this->generate_state_token();
		$this->storeData('kakao_state_token', $token);
		
		$this->AuthorizeUrlParameters['state'] = $token;

        $authUrl = $this->getAuthorizeUrl();

        $this->logger->debug(sprintf('%s::authenticateBegin(), redirecting user to:', get_class($this)), [$authUrl]);

        HttpClient\Util::redirect($authUrl);
    }

	public function getUserProfile()
    {
        $response = $this->apiRequest('https://kapi.kakao.com/v2/user/me');

        $data = new Data\Collection($response);

		$data2	= $data->get('properties');
		$data3	= $data->get('kakao_account');

		if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->name = preg_replace ('/[ ]+/', ' ', htmlspecialchars ($data2->nickname));
        $userProfile->gender = $data3->gender;
        $userProfile->email = $data3->email;
		$userProfile->birthDay = $data3->birthday;
		$userProfile->phone = $data3->phone_number;

        return $userProfile;
    }

	protected function generate_state_token() {
        $mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand);
    }    
}
