<div class="{{ $viewClass['form-group'] }} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">
    <label for="{{ $id }}" class="{{ $viewClass['label'] }} control-label">{{ $label }}</label>
    <div class="{{ $viewClass['field'] }}">

        @include('admin::form.error')
        <div class="btn btn-info pull-left cropper-btn">{{ trans('admin_cropper.choose') }}</div>
        @include('admin::form.help-block')
        <input class="cropper-file" type="file" accept="image/*" {!! $attributes !!} />
        <!-- <img class="cropper-img" {!! empty($value) ? '' : 'src="' . old($column, $value) . '"' !!}> -->
        <img class="cropper-img" {!! empty($value) ? '' : 'src="' . $preview . '"' !!}>
        <input class="cropper-input" name="{{ $name }}" value="{{ old($column, $value) }}" />
    </div>
</div>
<script>
    //圖片類型預存
    function getMIME(url) {
        var preg = new RegExp('data:(.*);base64', 'i');
        var result = preg.exec(url);
        console.log(result)
        if (result != null) {
            return result[1];
        } else {
            var ext = url.substring(url.lastIndexOf(".") + 1).toLowerCase();
            return 'image/' + ext
        }
    }

    function cropper(imgSrc, cropperFileE) {
        var w = $(cropperFileE).attr('data-w');
        var h = $(cropperFileE).attr('data-h');
        var cropperImg = '<div id="cropping-div"><img id="cropping-img" src="' + imgSrc + '"><\/div>';
        //生成彈層模塊
        layer.open({
            zIndex: 3000,
            type: 1,
            skin: 'layui-layer-demo', //樣式類名
            area: ['800px', '600px'],
            closeBtn: 2, //第二種關閉按鈕
            anim: 2,
            resize: false,
            shadeClose: false, //關閉遮罩關閉
            title: '{{ $cTitle }}',
            content: cropperImg,
            btn: @json(array_values($buttons)),
            @if (!empty($buttons['crop']))
                btn{{ array_search('crop', array_keys($buttons)) + 1 }}: function() {
                    var cas = cropper.getCroppedCanvas({
                        width: w,
                        height: h
                    });
                    //剪裁數據轉換base64
                    console.log(imgSrc)
                    var base64url = cas.toDataURL(getMIME(imgSrc));
                    //替換預覽圖
                    cropperFileE.nextAll('.cropper-img').attr('src', base64url);
                    //替換提交數據
                    cropperFileE.nextAll('.cropper-input').val(base64url);
                    //銷毀剪裁器實例
                    cropper.destroy();
                    layer.closeAll('page');
                },
            @endif
            @if (!empty($buttons['origin']))
                btn{{ array_search('origin', array_keys($buttons)) + 1 }}: function() {
                    //默認關閉框
                    cropperFileE.nextAll('.cropper-img').attr('src', imgSrc);
                    //替換提交數據
                    cropperFileE.nextAll('.cropper-input').val(imgSrc);
                    //銷毀剪裁器實例
                    cropper.destroy();
                },
            @endif
            @if (!empty($buttons['clear']))
                btn{{ array_search('clear', array_keys($buttons)) + 1 }}: function() {
                    //清空表單和選項
                    //銷毀剪裁器實例
                    cropper.destroy();
                    layer.closeAll('page');
                    //清空預覽圖
                    cropperFileE.nextAll('.cropper-img').removeAttr('src');
                    //清空提交數據
                    cropperFileE.nextAll('.cropper-input').val('');
                    //清空文件選擇器
                    cropperFileE.val('');
                }
            @endif
        });

        var image = document.getElementById('cropping-img');
        var cropper = new Cropper(image, @json($cropper_options));
    }

    //選擇按鈕
    $('form div').on('click', '.cropper-btn', function() {
        $(this).nextAll('.cropper-file').click()
        return false;
    });

    //在input file內容改變的時候觸發事件
    $('form').on('change', '.cropper-file', function(fileE) {
        //獲取input file的files文件數組;
        //這邊默認只能選一個，但是存放形式仍然是數組，所以取第一個元素使用[0];
        var file = $(this)[0].files[0];
        //創建用來讀取此文件的對象
        var reader = new FileReader();
        //使用該對象讀取file文件
        reader.readAsDataURL(file);
        //讀取文件成功後執行的方法函數
        reader.onload = function(e) {
            //選擇所要顯示圖片的img，要賦值給img的src就是e中target下result裡面的base64編碼格式的地址
            $(this).nextAll('.cropper-img').attr('src', e.target.result);
            //調取剪切函數（內部包含了一個彈出框）
            cropper(e.target.result, $(fileE.target));
            //向後兩輪定位到隱藏起來的輸入框體
            $(this).nextAll('.cropper-input').val(e.target.result);
        };
        return false;
    });

    //點擊圖片觸發彈層
    $('form').on('click', '.cropper-img', function() {
        cropper($(this).attr('src'), $(this).prevAll('.cropper-file'));
        return false;
    });
</script>
