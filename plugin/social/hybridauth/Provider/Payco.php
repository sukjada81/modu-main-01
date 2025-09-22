<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

/**
 * Hybrid_Providers_Payco provider adapter based on OAuth2 protocol
 * Copyright (c) 2022모두복지몰
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;
use Hybridauth\HttpClient;

/**
 * Payco OAuth2 provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback' => Hybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys' => ['id' => '', 'secret' => ''], *       
 *   ];
 *
 *   $adapter = new Hybridauth\Provider\Payco($config);
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
class Payco extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    // phpcs:ignore
    protected $scope = '';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://id.payco.com/oauth2.0/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://id.payco.com/oauth2.0/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://id.payco.com/oauth2.0/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.payco.com/guide/development/login';

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
		$this->storeData('payco_state_token', $token);
		
		$this->AuthorizeUrlParameters['state'] = $token;
		$this->AuthorizeUrlParameters['userLocale'] = "ko_KR";
		$this->AuthorizeUrlParameters['serviceProviderCode'] = "FRIENDS";

		$authUrl = $this->getAuthorizeUrl();

        $this->logger->debug(sprintf('%s::authenticateBegin(), redirecting user to:', get_class($this)), [$authUrl]);

        HttpClient\Util::redirect($authUrl);
    }

	protected function exchangeCodeForAccessToken($code)
    {
		
		$token = $this->getStoredData('payco_state_token');

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
		
		$this->apiRequestHeaders = array(
			'Content-Type' => 'application/json',
			'client_id' => $this->clientId,
            'access_token' => $this->getStoredData('access_token'),
        );

		$this->apiRequestParameters = array(
			'client_id' => $this->clientId,
            'access_token' => $this->getStoredData('access_token'),
        );
		
		$response = $this->apiRequest('https://apis-paycoid.krp.toastoven.net/payco/friends/find_member_v2.json', 'POST');
		
        $data = new Data\Collection($response);		
		
		$data2	= $data->get('data');
		$data2	= $data2->member;

		if (!$data2->idNo) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data2->idNod;
        $userProfile->displayName = $data2->name;
        $userProfile->gender = $data2->genderCode;
        $userProfile->email = $data2->email;
		$userProfile->birthDay = $data2->birthday;
		$userProfile->birthYear = $data2->birthday;
		$userProfile->phone	= $data2->mobile;
		
        return $userProfile;
    }

	protected function generate_state_token() {
		return md5(uniqid(mt_rand(), true));
    }   
	
}
