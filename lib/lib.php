<?php
require __DIR__ . '/../vendor/autoload.php';

$o_vk_api_client = new VK\Client\VKApiClient();
$s_vk_api_token = trim(file_get_contents('private/vk_api_token'));
