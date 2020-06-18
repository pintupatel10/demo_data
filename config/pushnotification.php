<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false,
      'apiKey' => 'My_ApiKey',
  ],
  'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY',
  ],
  'apn' => [
      'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
      'passPhrase' => '1234', //Optional
      'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
      'dry_run' => true
  ]
];