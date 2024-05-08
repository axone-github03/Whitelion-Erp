<?php

return [

	/*
		    |--------------------------------------------------------------------------
		    | Default Filesystem Disk
		    |--------------------------------------------------------------------------
		    |
		    | Here you may specify the default filesystem disk that should be used
		    | by the framework. The "local" disk, as well as a variety of cloud
		    | based disks are available to your application. Just store away!
		    |
	*/

	'default' => env('FILESYSTEM_DRIVER', 'local'),

	/*
		    |--------------------------------------------------------------------------
		    | Filesystem Disks
		    |--------------------------------------------------------------------------
		    |
		    | Here you may configure as many filesystem "disks" as you wish, and you
		    | may even configure multiple disks of the same driver. Defaults have
		    | been setup for each driver as an example of the required options.
		    |
		    | Supported Drivers: "local", "ftp", "sftp", "s3"
		    |
	*/

	'disks' => [

		'local' => [
			'driver' => 'local',
			'root' => storage_path('app'),
		],

		'public' => [
			'driver' => 'local',
			'root' => storage_path('app/public'),
			'url' => env('APP_URL') . '/storage',
			'visibility' => 'public',
		],

		's3' => [
			'driver' => 's3',
			'key' => env('AWS_ACCESS_KEY_ID'),
			'secret' => env('AWS_SECRET_ACCESS_KEY'),
			'region' => env('AWS_DEFAULT_REGION'),
			'bucket' => env('AWS_BUCKET'),
			'url' => env('AWS_URL'),
			'endpoint' => env('AWS_ENDPOINT'),
		],
		'spaces' => [
			'driver' => 's3',
			'key' => env('SPACES_ACCESS_KEY_ID', '===AAACESS KEY==='),
			'secret' => env('SPACES_SECRET_ACCESS_KEY', '===BBBSECRECT KEY==='),
			'region' => env('SPACES_DEFAULT_REGION', 'sgp1'),
			'bucket' => env('SPACES_BUCKET', 'laravel-spaces'),
			'url' => env('SPACES_URL', 'https://laravel-spaces.sgp1.cdn.digitaloceanspaces.com'),
			'endpoint' => env('SPACES_ENDPOINT', 'https://sgp1.digitaloceanspaces.com'),
			'visibility' => 'public',
		],

	],

	/*
		    |--------------------------------------------------------------------------
		    | Symbolic Links
		    |--------------------------------------------------------------------------
		    |
		    | Here you may configure the symbolic links that will be created when the
		    | `storage:link` Artisan command is executed. The array keys should be
		    | the locations of the links and the values should be their targets.
		    |
	*/

	'links' => [
		public_path('storage') => storage_path('app/public'),
	],

];
