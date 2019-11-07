<?php
require __DIR__ . '/../vendor/autoload.php';

$vk_api_client = new VK\Client\VKApiClient();
$vk_api_token = trim(file_get_contents('private/vk_api_token'));
