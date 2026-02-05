<?php
/**
 * LeWaterMaker 图片处理类
 * 
 * @package LeWaterMaker
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeWaterMaker_Image_Processor {
    
    /**
     * 插件选项
     */
    private $options;
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->options = get_option('le_watermaker_options', array());
    }
    
    /**
     * 给图片添加水印
     */
    public function add_watermark($image_path) {
        // 检查文件是否存在
        if (!file_exists($image_path)) {
            return false;
        }
        
        // 获取图片信息
        $image_info = getimagesize($image_path);
        if (!$image_info) {
            return false;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        $type = $image_info[2];
        
        // 检查图片尺寸
        $min_width = (int) $this->get_option('min_width', 300);
        $min_height = (int) $this->get_option('min_height', 300);
        
        if ($width < $min_width || $height < $min_height) {
            return false;
        }
        
        // 创建图片资源
        $image = $this->create_image_resource($image_path, $type);
        if (!$image) {
            return false;
        }
        
        // 添加水印
        $this->apply_watermark($image, $width, $height);
        
        // 保存图片
        $result = $this->save_image($image, $image_path, $type);
        
        // 释放内存
        imagedestroy($image);
        
        return $result ? $image_path : false;
    }
    
    /**
     * 创建图片资源
     */
    private function create_image_resource($image_path, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($image_path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($image_path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($image_path);
            default:
                return false;
        }
    }
    
    /**
     * 应用水印
     */
    private function apply_watermark($image, $width, $height) {
        // 获取水印设置
        $text = $this->get_option('watermark_text', 'LeWaterMaker');
        $font_size = (int) $this->get_option('font_size', 16);
        $opacity = (int) $this->get_option('opacity', 50);
        $rotation = (int) $this->get_option('rotation', -45);
        $spacing = (int) $this->get_option('spacing', 100);
        $color = $this->get_option('text_color', '#000000');
        
        // 转换颜色
        $rgb = $this->hex_to_rgb($color);
        $alpha = (100 - $opacity) * 127 / 100; // 转换为GD的alpha值
        
        // 创建颜色
        $text_color = imagecolorallocatealpha($image, $rgb['r'], $rgb['g'], $rgb['b'], $alpha);
        
        // 获取水印模式
        $mode = $this->get_option('watermark_mode', 'tiling');
        
        // 根据模式应用水印
        if ($mode === 'single') {
            $this->single_watermark($image, $text, $font_size, $text_color, $rotation, $width, $height);
        } else {
            $this->tile_watermark($image, $text, $font_size, $text_color, $rotation, $spacing, $width, $height);
        }
    }
    
    /**
     * 平铺水印
     */
    private function tile_watermark($image, $text, $font_size, $color, $rotation, $spacing, $width, $height) {
        // 计算水印网格
        $text_width = strlen($text) * $font_size * 0.6;
        $text_height = $font_size;
        
        // 计算旋转后的边界框
        $rad = deg2rad($rotation);
        $cos = cos($rad);
        $sin = sin($rad);
        
        $rotated_width = abs($text_width * $cos) + abs($text_height * $sin);
        $rotated_height = abs($text_width * $sin) + abs($text_height * $cos);
        
        // 计算网格间距
        $grid_width = max($rotated_width, $spacing);
        $grid_height = max($rotated_height, $spacing);
        
        // 在图片上平铺水印
        for ($y = 0; $y < $height + $grid_height; $y += $grid_height) {
            for ($x = 0; $x < $width + $grid_width; $x += $grid_width) {
                $this->draw_rotated_text($image, $text, $font_size, $color, $x, $y, $rotation);
            }
        }
    }
    
    /**
     * 单个居中水印
     */
    private function single_watermark($image, $text, $font_size, $color, $rotation, $width, $height) {
        // 计算水印尺寸
        $text_width = strlen($text) * $font_size * 0.6;
        $text_height = $font_size;
        
        // 计算旋转后的边界框
        $rad = deg2rad($rotation);
        $cos = cos($rad);
        $sin = sin($rad);
        
        $rotated_width = abs($text_width * $cos) + abs($text_height * $sin);
        $rotated_height = abs($text_width * $sin) + abs($text_height * $cos);
        
        // 计算居中位置
        $x = ($width - $rotated_width) / 2;
        $y = ($height - $rotated_height) / 2;
        
        // 在图片中央绘制水印
        $this->draw_rotated_text($image, $text, $font_size, $color, $x, $y, $rotation);
    }
    
    /**
     * 绘制旋转文字
     */
    private function draw_rotated_text($image, $text, $font_size, $color, $x, $y, $rotation) {
        // 获取字体设置
        $font_family = $this->get_option('font_family', 'DingTalkJinBuTi-Regular');
        $font_path = $this->get_font_path($font_family);
        
        // 如果字体文件存在且支持TTF，使用TTF字体
        if ($font_path && function_exists('imagettftext')) {
            $this->draw_ttf_text($image, $text, $font_size, $color, $x, $y, $rotation, $font_path);
        } else {
            // 回退到内置字体
            $this->draw_builtin_text($image, $text, $font_size, $color, $x, $y, $rotation);
        }
    }
    
    /**
     * 使用TTF字体绘制文字
     */
    private function draw_ttf_text($image, $text, $font_size, $color, $x, $y, $rotation, $font_path) {
        // 获取文字边界框
        $bbox = imagettfbbox($font_size, $rotation, $font_path, $text);
        
        // 计算文字尺寸
        $text_width = $bbox[4] - $bbox[0];
        $text_height = $bbox[5] - $bbox[1];
        
        // 调整位置以居中
        $x = $x - $bbox[0];
        $y = $y - $bbox[1];
        
        // 直接绘制TTF文字
        imagettftext($image, $font_size, $rotation, $x, $y, $color, $font_path, $text);
    }
    
    /**
     * 使用内置字体绘制文字
     */
    private function draw_builtin_text($image, $text, $font_size, $color, $x, $y, $rotation) {
        // 创建临时图片用于旋转
        $temp_width = strlen($text) * $font_size * 0.6;
        $temp_height = $font_size;
        
        $temp_image = imagecreatetruecolor($temp_width, $temp_height);
        imagealphablending($temp_image, false);
        imagesavealpha($temp_image, true);
        
        // 设置透明背景
        $transparent = imagecolorallocatealpha($temp_image, 255, 255, 255, 127);
        imagefill($temp_image, 0, 0, $transparent);
        
        // 在临时图片上绘制文字
        imagestring($temp_image, 5, 0, 0, $text, $color);
        
        // 旋转临时图片
        $rotated = imagerotate($temp_image, $rotation, $transparent);
        
        // 获取旋转后的尺寸
        $rotated_width = imagesx($rotated);
        $rotated_height = imagesy($rotated);
        
        // 将旋转后的文字复制到原图
        imagecopy($image, $rotated, $x, $y, 0, 0, $rotated_width, $rotated_height);
        
        // 释放临时图片
        imagedestroy($temp_image);
        imagedestroy($rotated);
    }
    
    /**
     * 获取字体文件路径
     */
    private function get_font_path($font_family) {
        $font_path = LE_WATERMAKER_PLUGIN_PATH . 'assets/fonts/' . $font_family;
        
        // 检查文件是否存在
        if (file_exists($font_path)) {
            return $font_path;
        }
        
        // 如果文件不存在，返回false
        return false;
    }
    
    /**
     * 保存图片
     */
    private function save_image($image, $image_path, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $image_path, 90);
            case IMAGETYPE_PNG:
                return imagepng($image, $image_path, 9);
            case IMAGETYPE_GIF:
                return imagegif($image, $image_path);
            default:
                return false;
        }
    }
    
    /**
     * 十六进制颜色转RGB
     */
    private function hex_to_rgb($hex) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return array('r' => $r, 'g' => $g, 'b' => $b);
    }
    
    /**
     * 获取选项值
     */
    private function get_option($key, $default = '') {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
} 