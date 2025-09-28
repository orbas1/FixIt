<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class CurrencyResource  extends BaseResource
{
  protected $showSensitiveAttributes = true;

  public static $wrap = null;

  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'code' => $this->code,
      'symbol' => $this->symbol,
      'symbol_position' => $this->symbol_position,
      'status' => $this->status,
      'media' => $this->media ? $this->media->map(function($media){
        return [
          'original_url' => $media->original_url
        ];
      }) : [],
    ];
  }
}
