<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

/**
 * Hybrid_Providers_Naver provider adapter based on OAuth2 protocol
 * Copyright (c) 2022더도매
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;
use Hybridauth\HttpClient;

/**
 * Naver OAuth2 provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback' => Hybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys' => ['id' => '', 'secret' => ''], *       
 *   ];
 *
 *   $adapter = new Hybridauth\Provider\Naver($config);
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
class Naver extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    // phpcs:ignore
    protected $scope = '';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://apis.naver.com/nidlogin/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://nid.naver.com/oauth2.0/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://nid.naver.com/oauth2.0/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.naver.com/docs/login/api/api.md';

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
		$this->storeData('naver_state_token', $token);
		
		$this->AuthorizeUrlParameters['state'] = $token;

        $authUrl = $this->getAuthorizeUrl();

        $this->logger->debug(sprintf('%s::authenticateBegin(), redirecting user to:', get_class($this)), [$authUrl]);

        HttpClient\Util::redirect($authUrl);
    }

	protected function exchangeCodeForAccessToken($code)
    {
		
		$token = $this->getStoredData('naver_state_token');

        $this->tokenExchangeParameters['code']	= $code;
		$this->tokenExchangeParameters['state'] = $token;

        $response = $this->httpClient->request(
            $this->accessTokenUrl,
            $this->tokenExchangeMethod,
            $this->tokenExchangeParameters,
            $this->tokenExchangeHeaders
        );

        $this->validateApiResponse('Unable to exchange code for API access token');

        return $response;
    }

	public function getUserProfile()
    {
        $response = $this->apiRequest('https://openapi.naver.com/v1/nid/me');

        $data = new Data\Collection($response);
		
		$data2	= $data->get('response');

		if (!$data2->id) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data2->id;
        $userProfile->name = $data2->name;
        $userProfile->gender = $data2->gender;
        $userProfile->email = $data2->email;
		$userProfile->birthDay = $data2->birthday;
		$userProfile->birthYear = $data2->birthyear;
		$userProfile->phone = $data2->mobile;
		
        return $userProfile;
    }

	protected function generate_state_token() {
        $mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand);
    }    
}
