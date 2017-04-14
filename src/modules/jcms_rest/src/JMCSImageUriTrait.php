<?php

namespace Drupal\jcms_rest;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Site\Settings;
use Drupal\crop\Entity\Crop;

trait JMCSImageUriTrait {

  protected $imageSizes = [
    'banner',
    'thumbnail',
  ];

  /**
   * Get the IIIF or web path to the image.
   *
   * @param string $image_uri
   * @param string $type
   * @param null|string $filemime
   * @return string
   */
  protected function processImageUri($image_uri, $type = 'source', $filemime = NULL) {
    $iiif = Settings::get('jcms_iiif_base_uri');
    if ($iiif) {
      $iiif_mount = Settings::get('jcms_iiif_mount', '/');
      $iiif_mount = trim($iiif_mount, '/');
      $iiif_mount .= (!empty($iiif_mount)) ? '/' : '';
      $image_uri = str_replace('public://' . $iiif_mount, '', $image_uri);
      if ($type == 'source') {
        switch ($filemime ?? \Drupal::service('file.mime_type.guesser')->guess($image_uri)) {
          case 'image/gif':
            $ext = 'gif';
            break;
          case 'image/png':
            $ext = 'png';
            break;
          default:
            $ext = 'jpg';
        }
        return $iiif . $image_uri . '/full/full/0/default.' . $ext;
      }
      else {
        return $iiif . $image_uri;
      }
    }
    else {
      return file_create_url($image_uri);
    }
  }

  /**
   * Process image field and return json string.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $data
   * @param bool $required
   * @param array|string $size_types
   * @param bool $bump
   * @return array
   */
  protected function processFieldImage(FieldItemListInterface $data, $required = FALSE, $size_types = ['banner', 'thumbnail'], $bump = FALSE) {
    if ($required || $data->count()) {
      $image = $this->getImageSizes($size_types);

      foreach ($image as $type => $array) {
        $image_uri = $data->first()->get('entity')->getTarget()->get('uri')->getString();
        $filemime = $data->first()->get('entity')->getTarget()->get('filemime')->getString();

        $image[$type]['uri'] = $this->processImageUri($image_uri, 'info');
        $image[$type]['alt'] = (string) $data->first()->getValue()['alt'];
        $image[$type]['source'] = [
          'mediaType' => $filemime,
          'uri' => $this->processImageUri($image_uri, 'source'),
          'filename' => basename($image_uri),
        ];
        $image[$type]['size'] = [
          'width' => (int) $data->first()->getValue()['width'],
          'height' => (int) $data->first()->getValue()['height'],
        ];

        // Focal point is optional.
        $crop_type = \Drupal::config('focal_point.settings')->get('crop_type');
        $crop = Crop::findCrop($image_uri, $crop_type);
        if ($crop) {
          $anchor = \Drupal::service('focal_point.manager')
            ->absoluteToRelative($crop->x->value, $crop->y->value, $image[$type]['size']['width'], $image[$type]['size']['height']);

          $image[$type]['focalPoint'] = $anchor;
        }
      }

      if ($bump && count($image) === 1) {
        $keys = array_keys($image);
        $image = $image[$keys[0]];
      }

      return $image;
    }

    return [];
  }

  /**
   * Get image sizes for the requested presets.
   *
   * @param array $size_types
   * @return array
   */
  protected function getImageSizes($size_types = ['banner', 'thumbnail']) {
    $sizes = [];
    $size_types = (array) $size_types;
    foreach ($size_types as $size_type) {
      if (in_array($size_type, $this->imageSizes)) {
        $sizes[$size_type] = [];
      }
    }

    return $sizes;
  }
}
