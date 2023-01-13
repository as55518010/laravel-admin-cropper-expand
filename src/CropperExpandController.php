<?php

namespace Encore\CropperExpand;

use Encore\Admin\Form\Field\ImageField;
use Encore\Admin\Form\Field\File;
use Encore\Admin\Admin;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CropperExpandController extends File
{
    use ImageField;

    private $ratioW = 100;

    private $ratioH = 100;

    private $cropper_options = [];

    private $buttons = ['crop','origin','clear'];

    protected $view = 'laravel-admin-cropper-expand::cropper';

    protected static $css = [
        '/vendor/laravel-admin-ext/cropper-expand/cropper.min.css',
    ];

    protected static $js = [
        '/vendor/laravel-admin-ext/cropper-expand/cropper.min.js',
        '/vendor/laravel-admin-ext/cropper-expand/layer/layer.js'
    ];

    protected function preview()
    {
        if (!is_null($this->value()))
            return $this->objectUrl($this->value());
    }

    /**
     * 將Base64圖片轉換為本地圖片並保存
     * @E-mial as55518010@yahoo.com.tw
     * @TIME   2023-01-13
     * @param  [Base64] $base64_image_content [要保存的Base64]
     * @param  [目錄] $path [要保存的路徑]
     */
    private function base64_image_content($base64_image_content, $path)
    {
        //匹配出圖片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            if (!file_exists($path)) {
                //檢查是否有該文件夾，如果沒有就創建，並給予755權限
                mkdir($path, 0755, true);
            }
            $filename = md5(microtime()) . ".{$type}";
            $all_path = $path . '/' . $filename;
            $content = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents($all_path, $content)) {
                return ['path' => $all_path, 'filename' => $filename];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * cropperjs options 自定義
     * @E-mial as55518010@yahoo.com.tw
     * @TIME   2023-01-13
     * @param  [options] set cropperjs options
     */
    public function setOptions($options)
    {
        $this->cropper_options = $options;
        return $this;
    }
    /**
     * buttons 自定義
     * @E-mial as55518010@yahoo.com.tw
     * @TIME   2023-01-13
     * @param  [options] set buttons
     */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
        return $this;
    }

    public function prepare($base64)
    {
        if (empty($base64)) {
            $this->destroy();
            return $base64;
        } else if (preg_match('/data:image\/.*?;base64/is', $base64)) {
            //檢查是否是base64編碼
            //base64轉圖片緩存 返回的是絕對路徑
            $image = $this->base64_image_content($base64, storage_path('app/public/images/base64img_cache'));
            if ($image !== false) {
                $image = new UploadedFile($image['path'], $image['filename']);
                $this->name = $this->getStoreName($image);
                $this->callInterventionMethods($image->getRealPath());
                $path = $this->uploadAndDeleteOriginal($image);
                return $path;
            } else {
                return 'lost';
            }
        } else {
            // 不是base64編碼
            return $base64;
        }
    }

    public function cRatio($width, $height)
    {
        if (!empty($width) and is_numeric($width)) {
            $this->attributes['data-w'] = $width;
        } else {
            $this->attributes['data-w'] = $this->ratioW;
        }
        if (!empty($height) and is_numeric($height)) {
            $this->attributes['data-h'] = $height;
        } else {
            $this->attributes['data-h'] = $this->ratioH;
        }
        return $this;
    }

    public function render()
    {
        $this->name = $this->formatName($this->column);
        $cTitle     = trans("admin_cropper.title");
        $crop       = trans("admin_cropper.done");
        $origin     = trans("admin_cropper.origin");
        $clear      = trans("admin_cropper.clear");

        if (!$this->display) {
            return '';
        }
        if (!$this->cropper_options) {
            $this->cropper_options = [
                'viewMode' => 2
            ];
        }
        $this->cropper_options['aspectRatio'] = $this->ratioW / $this->ratioH;
        $buttons = [];
        foreach ($this->buttons as $value) {
            $buttons[$value] = $$value;
        }
        return view($this->getView(), $this->variables(), [
            'preview'         => $this->preview(),
            'cropper_options' => $this->cropper_options,
            'buttons'         => $buttons,
            'cTitle'          => $cTitle,
        ]);
    }
}
