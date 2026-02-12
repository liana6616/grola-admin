<?php

namespace app\Models;

use app\Model;

class City extends Model
{
    // Добавляем константу TABLE как в других моделях
    public const TABLE = 'cities';
    
    public static function findByCode(string $code): ?self
    {
        return self::where('WHERE code = ? AND `show` = 1 LIMIT 1', [$code], true);
    }
    
    public static function getActiveCityCodes(): array
    {
        $cities = self::where('WHERE `show` = 1 ORDER BY name');
        $codes = [];
        
        foreach ($cities as $city) {
            $codes[] = $city->code;
        }
        
        return $codes;
    }
    
    public static function getDefaultCity(): ?self
    {
        return self::where('WHERE `default` = 1 AND `show` = 1 LIMIT 1', [], true);
    }
    
    /**
     * Получает код города по умолчанию
     */
    public static function getDefaultCityCode(): string
    {
        $city = self::getDefaultCity();
        return $city ? $city->code : 'default';
    }
    
    /**
     * Получает все активные города
     */
    public static function getAllActive(): array
    {
        return self::where('WHERE `show` = 1 ORDER BY name');
    }
    
    /**
     * Получает город по ID
     */
    public static function getById(int $id): ?self
    {
        return self::findById($id);
    }
    
    /**
     * Проверяет, существует ли город с таким кодом
     */
    public static function exists(string $code): bool
    {
        $city = self::findByCode($code);
        return $city !== null;
    }
    
    /**
     * Получает полный список городов для select
     */
    public static function getForSelect(): array
    {
        $cities = self::getAllActive();
        $result = [];
        
        foreach ($cities as $city) {
            $result[$city->id] = $city->name . ' (' . $city->code . ')';
        }
        
        return $result;
    }
    
    /**
     * Получает массив городов в формате code => name
     */
    public static function getCodeNameMap(): array
    {
        $cities = self::getAllActive();
        $result = [];
        
        foreach ($cities as $city) {
            $result[$city->code] = $city->name;
        }
        
        return $result;
    }
}