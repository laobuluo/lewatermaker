/**
 * LeWaterMaker 管理界面 JavaScript
 * 
 * @package LeWaterMaker
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    
    // 初始化颜色选择器
    $('.color-picker').wpColorPicker();
    
    // 透明度滑块实时更新
    $('input[name="le_watermaker_options[opacity]"]').on('input', function() {
        var value = $(this).val();
        $('#opacity-value').text(value + '%');
    });
    
    // 表单验证
    $('form').on('submit', function(e) {
        var enabled = $('input[name="le_watermaker_options[enabled]"]').is(':checked');
        
        if (enabled) {
            var text = $('input[name="le_watermaker_options[watermark_text]"]').val();
            if (!text || text.trim() === '') {
                alert('请填写水印文字！');
                e.preventDefault();
                return false;
            }
            
            var minWidth = parseInt($('input[name="le_watermaker_options[min_width]"]').val());
            var minHeight = parseInt($('input[name="le_watermaker_options[min_height]"]').val());
            
            if (minWidth < 100 || minHeight < 100) {
                alert('最小图片尺寸不能小于100px！');
                e.preventDefault();
                return false;
            }
        }
    });
    
    // 启用/禁用相关字段
    $('input[name="le_watermaker_options[enabled]"]').on('change', function() {
        var enabled = $(this).is(':checked');
        var fields = $('input[name^="le_watermaker_options["], select[name^="le_watermaker_options["]');
        
        if (!enabled) {
            fields.not('input[name="le_watermaker_options[enabled]"]').prop('disabled', true);
        } else {
            fields.prop('disabled', false);
        }
    });
    
    // 页面加载时检查状态
    if (!$('input[name="le_watermaker_options[enabled]"]').is(':checked')) {
        $('input[name^="le_watermaker_options["], select[name^="le_watermaker_options["]').not('input[name="le_watermaker_options[enabled]"]').prop('disabled', true);
    }
    
    // 添加帮助提示
    $('.form-table th').each(function() {
        var label = $(this).text();
        var helpText = '';
        
        switch(label) {
            case '启用水印':
                helpText = '启用后，上传的图片将自动添加水印';
                break;
            case '水印文字':
                helpText = '设置要显示的水印文字内容';
                break;
            case '水印模式':
                helpText = '选择水印应用模式：平铺模式将在整个图片上重复水印，单个居中模式将在图片中央添加一个水印';
                break;
            case '字体':
                helpText = '选择水印文字的字体样式';
                break;
            case '字体大小':
                helpText = '设置水印文字的大小（8-72px）';
                break;
            case '透明度':
                helpText = '设置水印文字的透明度（10%-100%）';
                break;
            case '旋转角度':
                helpText = '设置水印文字的旋转角度（-180°到180°）';
                break;
            case '文本间距':
                helpText = '设置水印文字之间的间距（50-300px）';
                break;
            case '文字颜色':
                helpText = '选择水印文字的颜色';
                break;
            case '最小图片宽度':
                helpText = '只有超过此宽度的图片才会添加水印';
                break;
            case '最小图片高度':
                helpText = '只有超过此高度的图片才会添加水印';
                break;
        }
        
        if (helpText) {
            $(this).append('<span class="dashicons dashicons-editor-help" title="' + helpText + '" style="margin-left: 5px; cursor: help;"></span>');
        }
    });
}); 