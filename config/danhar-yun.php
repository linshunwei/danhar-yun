<?php

return [
  /*
   |--------------------------------------------------------------------------
   | 公共会员 接口服务器
   |--------------------------------------------------------------------------
   |
   */

	'host' => env('DANHAR_YUN_HOST','https://demo-userapi.danhar.com'),

	/*
   |--------------------------------------------------------------------------
   | 公共管理员 接口服务器
   |--------------------------------------------------------------------------
   |
   */
	'admin_host' => env('DANHAR_YUN_ADMIN_HOST','https://demo-adminapi.danhar.com'),
	/*
   |--------------------------------------------------------------------------
   | 授权 接口服务器
   |--------------------------------------------------------------------------
   |
   */
	'token_url' => env('DANHAR_YUN_OAUTH_TOKEN_URL','https://demo-oauth.danhar.com/token'),
	'authorization_url' => env('DANHAR_YUN_OAUTH_AUTHORIZATIION_URL','https://demo-oauth.danhar.com/authorize'),

	/*
   |--------------------------------------------------------------------------
   | 客户端授权回调地址
   |--------------------------------------------------------------------------
   |
   */
	'callback_url' => env('DANHAR_YUN_CALLBACK_URL','https://demo-userapi.danhar.com/api/oauth2-callback'),
	'client_id' => env('DANHAR_YUN_CLIENT_ID','201905050435075442'),
	'client_secret' =>  env('DANHAR_YUN_CLIENT_SECRET','76765ea971fdcab222c28ca027378efb'),
];
