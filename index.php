<?php

const CHANNELS = [
    'pt' => 'https://discord.com/api/webhooks/1109597187563864235/XWlpkgEPz2c4qd9G3T3mXg5f9kgb-ESHsiENAhCzgQlAhh8lnAWY-sGIZi3vNxsux4j2',
    'en' => 'https://discord.com/api/webhooks/1109886412142157854/VdcklVZSHhJ0SsSINN28adI_AI4HbWjdzZ4IPqRI-DjEQMz7wi39AFYUC-AzZ0V3jMQJ',
];

$data = json_decode(file_get_contents('php://input'), true);

foreach (CHANNELS as $language => $url) {

    $text = translateText($data['message']['text'], $language);
    $detailedMessage = translateText($data['detailedMessage']['text'], $language);

    $message = [
        'username' => 'Azure',
        'avatar_url' => $data['resource']['fields']['System.CreatedBy']['imageUrl'],
        'content' => $text,
        'embeds' => [
            [
                'author' => [
                    'name' => $data['resource']['fields']['System.CreatedBy']['displayName'],
                    'url' => $data['resource']['fields']['System.CreatedBy']['imageUrl'],
                    'icon_url' => $data['resource']['fields']['System.CreatedBy']['imageUrl'],
                ],
                'title' => $data['eventType'],
                'description' => $detailedMessage,
                'color' => 15258703,
                'thumbnail' => [
                    'url' => $data['resource']['fields']['System.CreatedBy']['imageUrl'],
                ],
                'image' => [
                    'url' => $data['resource']['fields']['System.CreatedBy']['imageUrl'],
                ],
                'footer' => [
                    'text' => $data['url'],
                ],
            ],
        ],
    ];

    $response = sendMessage($url, $message);
}


function debug($data)
{
    $file = fopen('debug', 'w');
    fwrite($file, $data);
    fclose($file);
}

function translateText($text, $language)
{
  $ch = curl_init(); 
  curl_setopt($ch, CURLOPT_URL, 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=' . $language . '&dt=t&q=' . urlencode($text));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = json_decode(curl_exec($ch));
  curl_close($ch);

  $message = '';
  foreach ($response[0] as $msg) {
    $message .= $msg[0];
  }

  return $message;
}

function sendMessage($url, $message)
{
    $header = [
      'Content-Type:application/json;charset=UTF-8',
      'Accept:application/json',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    return [
        'response' => $response,
        'httpcode' => $httpcode,
    ];
}