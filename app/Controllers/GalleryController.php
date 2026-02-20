<?php

namespace app\Controllers;

use app\Db;
use app\Models\Gallery;

class GalleryController
{
    /**
     * Получение галереи для товара
     */
    public function getProductGallery($productId)
    {
        // Получаем галерею для товара (type = 'product')
        $gallery = Gallery::findGallery('product', $productId);
        
        // Передаем в представление
        $this->view->gallery = $gallery;
        
        return $gallery;
    }
    
    /**
     * Получение галереи для категории
     */
    public function getCategoryGallery($categoryId)
    {
        // Получаем галерею для категории (type = 'category')
        $gallery = Gallery::findGallery('category', $categoryId, 10); // ограничим 10 фото
        
        $this->view->categoryGallery = $gallery;
        
        return $gallery;
    }
    
    /**
     * Добавление изображения в галерею
     */
    public function addImage($type, $ids, $imageData)
    {
        // Создаем новую запись
        $gallery = new Gallery();
        $gallery->type = $type;
        $gallery->ids = $ids;
        $gallery->image = $imageData['image'];
        $gallery->image_small = $imageData['image_small'] ?? '';
        $gallery->name = $imageData['name'] ?? '';
        $gallery->rate = $imageData['rate'] ?? 0;
        
        // Сохраняем в БД
        $gallery->insert();
        
        return $gallery->id;
    }
    
    /**
     * Удаление конкретного изображения
     */
    public function deleteImage($imageId)
    {
        // Находим изображение
        $gallery = Gallery::findById($imageId);
        
        if ($gallery) {
            // Удаляем файлы
            $this->deleteImageFiles($gallery);
            
            // Удаляем запись из БД
            $gallery->delete();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Удаление всей галереи для объекта
     */
    public function deleteAllImages($type, $ids)
    {
        Gallery::delAll($type, $ids);
        
        return true;
    }
    
    /**
     * Обновление рейтинга (порядка сортировки)
     */
    public function updateRate($imageId, $newRate)
    {
        $gallery = Gallery::findById($imageId);
        
        if ($gallery) {
            $gallery->rate = $newRate;
            $gallery->update();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Вспомогательный метод для удаления файлов
     */
    private function deleteImageFiles($gallery)
    {
        $filesToDelete = [
            $gallery->image,
            $gallery->image_small,
            $gallery->image_origin ?? null
        ];
        
        foreach ($filesToDelete as $file) {
            if ($file && file_exists(ROOT . $file)) {
                unlink(ROOT . $file);
            }
        }
    }
}