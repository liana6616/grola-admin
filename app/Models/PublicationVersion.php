<?php

namespace app;

class PublicationVersion
{
    /**
     * Получить все версии для записи
     */
    public static function getVersionsForRecord(string $tableName, int $recordId): array
    {
        $db = Db::getInstance();
        
        $sql = "SELECT 
                    pv.*,
                    a.name as admin_name,
                    a.login as admin_login
                FROM publication_versions pv
                LEFT JOIN admins a ON pv.admin_id = a.id
                WHERE pv.table_name = :table_name 
                AND pv.record_id = :record_id
                ORDER BY pv.created_at DESC";
        
        return $db->query($sql, [
            ':table_name' => $tableName,
            ':record_id' => $recordId
        ]);
    }
    
    /**
     * Получить статистику по версиям
     */
    public static function getVersionStats(string $tableName, int $recordId): array
    {
        $db = Db::getInstance();
        
        $sql = "SELECT 
                    COUNT(*) as total_versions,
                    MIN(created_at) as first_version_date,
                    MAX(created_at) as last_version_date,
                    COUNT(DISTINCT admin_id) as unique_admins
                FROM publication_versions
                WHERE table_name = :table_name 
                AND record_id = :record_id";
        
        $result = $db->query($sql, [
            ':table_name' => $tableName,
            ':record_id' => $recordId
        ]);
        
        return $result[0] ?? [];
    }
    
    /**
     * Сравнить две версии
     */
    public static function compareVersions(int $versionId1, int $versionId2): array
    {
        $db = Db::getInstance();
        
        // Получаем обе версии
        $sql = "SELECT * FROM publication_versions WHERE id IN (:id1, :id2)";
        $versions = $db->query($sql, [
            ':id1' => $versionId1,
            ':id2' => $versionId2
        ]);
        
        if (count($versions) !== 2) {
            return [];
        }
        
        // Декодируем данные
        $data1 = json_decode($versions[0]['version_data'] ?? '{}', true);
        $data2 = json_decode($versions[1]['version_data'] ?? '{}', true);
        
        // Сравниваем
        $differences = [];
        $allFields = array_unique(array_merge(array_keys($data1), array_keys($data2)));
        
        foreach ($allFields as $field) {
            $value1 = $data1[$field] ?? null;
            $value2 = $data2[$field] ?? null;
            
            if ($value1 !== $value2) {
                $differences[$field] = [
                    'from' => $value1,
                    'to' => $value2,
                    'changed' => true
                ];
            }
        }
        
        return [
            'version1' => $versions[0],
            'version2' => $versions[1],
            'differences' => $differences
        ];
    }
    
    /**
     * Удалить старые версии (автоочистка)
     */
    public static function cleanupOldVersions(int $keepLast = 50, int $olderThanDays = 365): int
    {
        $db = Db::getInstance();
        
        // Удаляем старые версии, оставляя только последние $keepLast для каждой записи
        $sql = "DELETE pv FROM publication_versions pv
                JOIN (
                    SELECT id, table_name, record_id,
                           ROW_NUMBER() OVER (PARTITION BY table_name, record_id ORDER BY created_at DESC) as rn
                    FROM publication_versions
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                ) as old
                ON pv.id = old.id
                WHERE old.rn > :keep_last";
        
        $result = $db->execute($sql, [
            ':days' => $olderThanDays,
            ':keep_last' => $keepLast
        ]);
        
        return $result->rowCount();
    }
}