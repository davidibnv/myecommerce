<?php

namespace Tests;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Image;
use App\Models\Product;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

trait CreateData
{
    protected function generateData($createAdminUser = false, $createCategory = false, $createSubcategory = false, $subcategoryHasColor = false,
                                    $subcategoryHasSize = false, $createBrand = false, $attachCategoryToBrand = false, $createProduct = false,
                                    $productStatus = 2, $createColor = false, $attachColorToProduct = false, $createSize = false,
                                    $attachColorsToSize = false)
    {
        $user = null;
        $category = null;
        $subcategory = null;
        $brand = null;
        $product = null;
        $color = null;
        $size = null;

        if($createAdminUser) {
            $user = $this->createAdminUserData();
        }

        if ($createCategory) {
            $category = $this->createCategoryData();
        }

        if ($createSubcategory) {
            $subcategory = $this->createSubcategoryData($category->id, $subcategoryHasColor, $subcategoryHasSize);
        }

        if ($createBrand) {
            $brand = $this->createBrandData();
        }

        if($attachCategoryToBrand) {
            $this->attachBrandToCategoryData($brand, $category);
        }

        if ($createProduct) {
            $product = $this->createProductData($subcategory->id, $brand->id, $productStatus);
        }

        if ($createColor) {
            $color = $this->createColorData();
        }

        if ($attachColorToProduct) {
            $this->attachColorsToProductData($product, $attachColorToProduct);
        }

        if ($createSize) {
            $size = $this->createSizeData($product->id);
        }

        if ($attachColorsToSize) {
            $this->attachColorsToSizeData($size, $attachColorsToSize);
        }

        return [
            'user' => $user,
            'category' => $category,
            'subcategory' => $subcategory,
            'brand' => $brand,
            'product' => $product,
            'color' => $color,
            'size' => $size,
        ];
    }

    protected function createCategoryData()
    {
        return Category::factory()->create();
    }

    protected function createSubcategoryData($categoryId, $hasColor = false, $hasSize = false)
    {
        return Subcategory::factory()->create([
            'category_id' => $categoryId,
            'color' => $hasColor,
            'size' => $hasSize
        ]);
    }

    protected function createColorData()
    {
        return Color::create([
            'name' => Factory::create()->colorName()
        ]);
    }

    protected function attachColorsToProductData($product, $colors)
    {
        foreach ($colors as $color) {
            $product->colors()->attach([
                $color->id => ['quantity' => '50']
            ]);
        }
    }

    protected function createImageData($imageableId, $imageableType)
    {
        return Image::factory(4)->create([
            'imageable_id' => $imageableId,
            'imageable_type' => $imageableType
        ]);
    }

    protected function createBrandData()
    {
        $brand = Brand::factory()->create();

        return $brand;
    }

    protected function attachBrandToCategoryData($brand, $category)
    {
        $category->brands()->attach($brand);
    }

    protected function createSizeData($productId)
    {
        $size = Size::factory()->create([
            'product_id' => $productId
        ]);

        return $size;
    }

    protected function attachColorsToSizeData($size, $colors)
    {
        foreach ($colors as $color) {
            $size->colors()
                ->attach([
                    $color->id => ['quantity' => 12]
                ]);
        }
    }

    protected function createProductData($subcategoryId, $brandId, $status = Product::PUBLICADO)
    {
        $subcategory = Subcategory::find($subcategoryId);

        $product = Product::factory()->create([
            'subcategory_id' => $subcategoryId,
            'brand_id' => $brandId,
            'quantity' => $subcategory->color ? null : 15,
            'status' => $status
        ]);

        $this->createImage($product->id, Product::class);

        return $product;
    }

    protected function createAdminUserData()
    {
        $adminRole = Role::create(['name' => 'admin']);

        $user = User::factory()->create();
        $user->assignRole($adminRole);

        return $user;
    }

}
