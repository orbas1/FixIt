<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class BookingStatusResource  extends BaseResource
{
  protected $showSensitiveAttributes = true;



  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'slug' => $this->slug,
      'hexa_code' => $this->hexa_code,
    ];
  }
}
