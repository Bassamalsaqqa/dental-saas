<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryItemTemplateModel extends Model
{
    protected $table = 'inventory_item_templates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'name',
        'category',
        'description',
        'unit',
        'default_min_quantity',
        'default_unit_price',
        'search_keywords',
        'is_active',
        'sort_order',
    ];

    public function getCatalog(?string $category = null, ?string $q = null): array
    {
        $builder = $this->where('is_active', 1);

        if ($category !== null && $category !== '') {
            $builder->where('category', $category);
        }

        if ($q !== null && trim($q) !== '') {
            $term = trim($q);
            $builder->groupStart()
                ->like('name', $term)
                ->orLike('description', $term)
                ->orLike('search_keywords', $term)
                ->groupEnd();
        }

        return $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}

