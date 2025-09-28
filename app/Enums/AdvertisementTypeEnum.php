<?php

namespace App\Enums;

enum AdvertisementTypeEnum: string
{
    const ADVERTISEMENTTYPE = [
        'banner' => 'Banner',
        'service' => 'Service',
    ];

    const ADVERTISEMENTSCREEN = [
      'home' => 'Home',
      'category' => 'Category',
  ];

  const ADVERTISEMENTBANNERTYPE = [
    'image' => 'Image',
    'video' => 'Video',
  ];

  const BANNER = 'banner';

  const IMAGE = 'image';
  const VIDEO = 'video';
}
