<?php

namespace Encore\CropperExpand;

use Encore\Admin\Extension;

class CropperExpand extends Extension
{
    public $name = 'cropper-expand';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';

    public $menu = [
        'title' => 'Cropper-Expand',
        'path'  => 'cropper-expand',
        'icon'  => 'fa-gears',
    ];
}
