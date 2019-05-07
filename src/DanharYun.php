<?php

namespace Linshunwei\DanharYun;

use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 *  东合公共会员类
 *  author:linshunwei
 */
class DanharYun
{
	private $client_id = '';
	private $client_secret = '';
	private $host = '';
	private $authorization_url = '';
	private $token_url = '';
	private $admin_host = '';
	private $callback_url = '';
	private $log_debug = '';

	private $token = '';


	public function __construct()
	{
		$this->client_id = config('danhar-yun.client_id');
		$this->client_secret = config('danhar-yun.client_secret');
		$this->host = config('danhar-yun.host');
		$this->authorization_url = config('danhar-yun.authorization_url');
		$this->token_url = config('danhar-yun.token_url');
		$this->admin_host = config('danhar-yun.admin_host');
		$this->callback_url = config('danhar-yun.callback_url');
		$this->log_debug = config('danhar-yun.log_debug');
	}

	/**
	 * 日志记录
	 * @param $message
	 * @param array $content
	 * @throws \Exception
	 */
	private function writeLogger($message, $content = [])
	{
		$logger = new Logger('log');
		$logger->pushHandler(new StreamHandler(storage_path('logs/danhar_yun/' . date('Y-m-d') . '.log'), Logger::DEBUG));
		$logger->debug($message, $content);
	}

	public function logger($url = '', $data = [], $content = [])
	{
		if ($this->log_debug){
			$this->writeLogger('host', [$url]);
			$this->writeLogger('request', $data);
			$this->writeLogger('response', $content);
		}
	}

	/**
	 *  web 认证
	 * 单点登录授权地址
	 */
	public function getOauthUrl()
	{
		$data = [
			'client_id' => $this->client_id,
			'redirect_uri' => $this->callback_url,
			'response_type' => 'code',
			'scope' => '*',
		];
		$url = $this->authorization_url . '?' . http_build_query($data);
		return $url;
	}

	/**
	 * code 获取token
	 */
	public function getOauthToken($code)
	{
		$data = [
			'grant_type' => 'authorization_code',
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri' => $this->callback_url,
			'code' => $code,
		];
		$result = $this->request_post($this->token_url, $data);
		return $result;

	}

	protected function setOauthToken($code)
	{
		$result = $this->getOauthToken($code);
		if (array_key_exists('access_token', $result)) {
			$this->setToken($result['access_token']);
		}
	}

	/**
	 *  获取授权令牌
	 */

	/**************************** 客户端认证 ***********************************************************/
	/**
	 * 注册
	 * @param string $mobile
	 * @param string $password
	 * @param string $smsvcode
	 * @param string $realname
	 * @return mixed
	 */
	public function postRegister($mobile = '', $password = '', $smsvcode = '', $realname = '')
	{
		$this->setClientToken();
		$data = [
			'mobile' => $mobile,
			'password' => $password,
			'smsvcode' => $smsvcode,
			'realname' => $realname,
		];
		$result = $this->request_post($this->host . '/api/auth/register', $data);
		return $result;
	}

	/**
	 * 重置密码
	 * @param string $mobile
	 * @param string $password
	 * @param string $smsvcode
	 */
	public function postPasswordReset($mobile = '', $password = '', $smsvcode = '')
	{
		$this->setClientToken();
		$data = [
			'mobile' => $mobile,
			'password' => $password,
			'smsvcode' => $smsvcode,
		];
		$result = $this->request_post($this->host . '/api/auth/password_reset', $data);

		return $result;
	}


	/**
	 * 获取参数
	 * @param string $pid
	 * @return mixed
	 */
	public function getParameterItem($pid = '')
	{
		$this->setClientToken();
		$data = [
			'pid' => $pid,
		];
		$result = $this->request_get($this->host . '/api/parameter/item', $data);
		if (array_key_exists('code', $result) && $result['code'] == 200) {
			return $result['data'];
		}
	}

	/**
	 *  发送短信
	 * @param $mobile
	 * @param $type
	 * @return mixed
	 */
	public function postSms($mobile, $type)
	{
		$this->setClientToken();
		$data = [
			'mobile' => $mobile,
			'type' => $type
		];
		$result = $this->request_post($this->host . '/api/sms/send', $data);
		return $result;
	}

	/**
	 * 获取名片信息
	 */
	public function getCardInfo($real_path)
	{
		$this->setClientToken();
		$file_id = '';
		if ($real_path) {
			$file = $this->postFile($real_path);

			if (!is_null($file)) {
				$file_id = $file['id'];
			}
		}
		$data = [
			'picture_id' => $file_id
		];
		return $this->request_get($this->host . '/api/card/info', $data);
	}

	/**
	 *  上传文件
	 * @param string $file_id
	 */
	public function postFile($real_path = '')
	{
		if (!$real_path) {
			return;
		}

		$data = [
			[
				'name' => 'file',
				'contents' => fopen($real_path, 'r')
			],
		];
		$result = $this->request_put($this->host . '/api/file', $data);
		return $result;
	}


	public function getClientToken()
	{
		$data = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'scope' => '*',
		];
		$result = $this->request_post($this->token_url, $data);
		return $result;

	}

	protected function setClientToken()
	{
		$result = $this->getClientToken();
		if (array_key_exists('access_token', $result)) {
			$this->setToken($result['access_token']);
		}
	}

	protected function setUserToken($username, $password)
	{
		$result = $this->getUserToken($username, $password);
		if (array_key_exists('access_token', $result)) {
			$this->setToken($result['access_token']);
		}
	}

	private function setToken($token)
	{
		$this->token = $token;
	}

	/***************************** 用户信息授权 ***************************/
	public function getUserToken($username, $password)
	{
		$data = [
			'grant_type' => 'password',
			'username' => $username,
			'password' => $password,
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'scope' => '*',
		];
		$result = $this->request_post($this->token_url, $data);
		return $result;
	}


	/**
	 *  获取token
	 */
	public function getUser($user_token)
	{
		$this->setToken($user_token);
		$result = $this->request_get($this->host . '/api/user/detail');
		if (array_key_exists('code', $result) && $result['code'] == 200) {
			return $result['data'];
		}
	}

	/**
	 * 修改基础信息
	 */
	public function postUserProfile($user_token, $avatar_url = '', $gender = '', $birthday = '', $nation = '', $address = '')
	{
		$this->setToken($user_token);
		$_avatar_id = '';
		if ($avatar_url) {
			$file = $this->postFile($avatar_url);

			if (!is_null($file)) {
				$_avatar_id = $file['id'];
			}
		}
		$data = [
			'avatar_id' => $_avatar_id,
			'gender' => $gender,
			'birthday' => $birthday,
			'nation' => $nation,
			'address' => $address,
		];
		$result = $this->request_post($this->host . '/api/user/profile', $data);

		return $result;

	}


	/**
	 * 修改密码
	 */
	public function postPassword($user_token, $smsvcode, $password)
	{
		$this->setToken($user_token);
		$data = [
			'smsvcode' => $smsvcode,
			'password' => $password,
			'password_confirmation' => $password,
		];
		$result = $this->request_post($this->host . '/api/user/password', $data);
		return $result;
	}

	/**
	 * 修改手机
	 */
	public function postMobile($user_token, $mobile, $smsvcode)
	{
		$this->setToken($user_token);
		$data = [
			'smsvcode' => $smsvcode,
			'mobile' => $mobile,
		];
		$result = $this->request_post($this->host . '/api/user/mobile', $data);
		return $result;
	}

	/**
	 * 实名
	 */
	public function postVerifyFace($user_token, $realname, $card_no, $motions, $anti_hack, $complexity, $video_path)
	{
		$this->setToken($user_token);

		$file_id = '';
		if ($video_path) {
			$file = $this->postFile($video_path);

			if (!is_null($file)) {
				$file_id = $file['id'];
			}
		}
		$data = [
			'realname' => $realname,
			'card_no' => $card_no,
			'motions' => $motions,
			'anti_hack' => $anti_hack,
			'complexity' => $complexity,
			'video_id' => $file_id,
		];

		$result = $this->request_post($this->host . '/api/user/verify_face', $data);
		return $result;
	}

	/**
	 * 实名 app
	 */
	public function postVerifyFaceApp($user_token, $realname, $card_no, $video_path)
	{
		$this->setToken($user_token);

		$file_id = '';
		if ($video_path) {
			$file = $this->postFile($video_path);

			if (!is_null($file)) {
				$file_id = $file['id'];
			}
		}
		$data = [
			'realname' => $realname,
			'card_no' => $card_no,
			'file_id' => $file_id,
		];
		$result = $this->request_post($this->host . '/api/user/verify_face_app', $data);
		return $result;
	}

	/**
	 *  获取身份证信息
	 */
	public function getCard($user_token)
	{
		$this->setToken($user_token);
		$result = $this->request_get($this->host . '/api/user/card');
		return $result;
	}

	/**
	 * 实名 app
	 */
	public function postCard($token, $card_front_url, $card_back_path)
	{
		$this->setToken($token);

		$file_id = '';
		if ($card_front_url) {
			$file = $this->postFile($card_front_url);

			if (!is_null($file)) {
				$file_id = $file['id'];
			}
		}
		$back_id = '';
		if ($card_back_path) {
			$file = $this->postFile($card_back_path);

			if (!is_null($file)) {
				$back_id = $file['id'];
			}
		}
		$data = [
			'card_front_id' => $file_id,
			'card_back_id' => $back_id,
		];
		$result = $this->request_post($this->host . '/api/user/card', $data);
		return $result;
	}

	/***************************** 管理员 ******************************/

	/**
	 * 获取管理员信息
	 */
	public function getAdmin($admin_token)
	{
		$this->setAdminToken($admin_token);
		$result = $this->request_get($this->admin_host . '/api/auth/detail');

		return $result;
	}


	/**
	 * 获取授权管理员列表
	 */
	public function getAdminList($admin_token)
	{
		$this->setAdminToken($admin_token);
		$result = $this->request_get($this->admin_host . '/api/admin/site_list');
		return $result;
	}

	protected function setAdminToken($admin_token)
	{
		$this->token = $admin_token;
	}

	/**
	 * @param $url
	 * @param null $data
	 * @return array|void
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	private function request_post($url, $data = null)
	{
		$_data = [
			'form_params' => $data,
		];

		$header['accept'] = 'application/json';

		if ($this->token) {
			$header['Authorization'] = "Bearer " . $this->token;
		}
		if ($header) {
			$_data['headers'] = $header;
		}
		$client = new Client();
		$res = $client->request('Post', $url, $_data);

		$content = $this->object_to_array(json_decode($res->getBody()->getContents()));
		//todo 日志
		$this->logger($url,$_data,$content);

		return $content;
	}

	/**
	 *  上传
	 * @param $url
	 * @param $data
	 * @return array|void
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	private function request_put($url, $data)
	{
		$_data = [
			'multipart' => $data
		];

		$client = new Client();
		$res = $client->request('Post', $url, $_data);

		$content = $this->object_to_array(json_decode($res->getBody()->getContents()));
		//todo 日志
		$this->logger($url,$_data,$content);

		return $content;
	}

	private function request_get($url, $data = null)
	{
		$_data = [
			'query' => $data,
		];

		$header['accept'] = 'application/json';
		if ($this->token) {
			$header['Authorization'] = "Bearer " . $this->token;
		}
		if ($header) {
			$_data['headers'] = $header;
		}
		$client = new Client();
		$res = $client->request('Get', $url, $_data);

		$content = $this->object_to_array(json_decode($res->getBody()->getContents()));
		//todo 日志
		$this->logger($url,$_data,$content);

		return $content;
	}

	private function object_to_array($obj)
	{
		$obj = (array)$obj;
		foreach ($obj as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array') {
				$obj[$k] = (array)$this->object_to_array($v);
			}
		}

		return $obj;
	}

}