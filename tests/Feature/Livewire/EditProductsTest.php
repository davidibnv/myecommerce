<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Admin\EditProduct;
use App\Http\Livewire\Admin\StatusProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class EditProductsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_updates_a_simple_product()
    {
        $categoryA = $this->createCategory();
        $categoryB = $this->createCategory();

        $subcategoryA = $this->createSubcategory($categoryA->id);
        $subcategoryB = $this->createSubcategory($categoryB->id);

        $brandA = $this->createBrand($categoryA->id);
        $brandB = $this->createBrand($categoryB->id);

        $product = $this->createProduct($subcategoryA->id, $brandA->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('category_id', $categoryB)
            ->set('product.subcategory_id', $subcategoryB->id)
            ->set('product.brand_id', $brandB->id)
            ->set('product.name', 'Nombre nuevo')
            ->set('product.slug', 'nombre-nuevo')
            ->set('product.description', 'Descripción nueva')
            ->set('product.price', 5)
            ->set('product.quantity', 1)
            ->call('save');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nombre nuevo',
            'slug' => 'nombre-nuevo',
            'description' => 'Descripción nueva',
            'price' => 5,
            'subcategory_id' => $subcategoryB->id,
            'brand_id' => $brandB->id,
            'quantity' => 1,
        ]);
    }

    /** @test */
    public function it_deletes_a_simple_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('images', 4);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->call('delete');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('images', 0);
    }

    /** @test  */
    public function it_updates_the_product_status()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id, Product::BORRADOR);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(StatusProduct::class, [
            'product' => $product
        ])->set('status', '2')
            ->call('save');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 2
        ]);
    }

    /** @test  */
    public function the_category_id_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('category_id', '')
            ->call('save')
            ->assertHasErrors(['category_id' => 'required']);

        $this->assertDatabaseHas('subcategories', [
            'category_id' => $category->id
        ]);

        $this->assertDatabaseHas('products', [
            'subcategory_id' => $subcategory->id
        ]);
    }

    /** @test  */
    public function the_subcategory_id_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.subcategory_id', '')
            ->call('save')
            ->assertHasErrors(['product.subcategory_id' => 'required']);

        $this->assertDatabaseHas('products', [
            'subcategory_id' => $subcategory->id
        ]);
    }

    /** @test  */
    public function the_name_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.name', '')
            ->call('save')
            ->assertHasErrors(['product.name' => 'required']);

        $this->assertDatabaseHas('products', [
            'name' => $product->name
        ]);
    }

    /** @test  */
    public function the_slug_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.slug', '')
            ->call('save')
            ->assertHasErrors(['product.slug' => 'required']);

        $this->assertDatabaseHas('products', [
            'slug' => $product->slug
        ]);
    }

    /** @test  */
    public function the_slug_field_is_unique()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $productA
        ])->set('product.slug', $productB->slug)
            ->call('save')
            ->assertHasErrors(['product.slug' => 'unique']);

        $this->assertDatabaseHas('products', [
            'id' => $productA->id,
            'slug' => $productA->slug
        ]);
    }

    /** @test  */
    public function the_description_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.description', '')
            ->call('save')
            ->assertHasErrors(['product.description' => 'required']);

        $this->assertDatabaseHas('products', [
            'description' => $product->description
        ]);
    }

    /** @test  */
    public function the_brand_id_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.brand_id', '')
            ->call('save')
            ->assertHasErrors(['product.brand_id' => 'required']);

        $this->assertDatabaseHas('products', [
            'brand_id' => $product->brand_id
        ]);
    }

    /** @test  */
    public function the_price_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.price', '')
            ->call('save')
            ->assertHasErrors(['product.price' => 'required']);

        $this->assertDatabaseHas('products', [
            'price' => $product->price
        ]);
    }

    /** @test  */
    public function the_quantity_field_is_required()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.quantity', '')
            ->call('save')
            ->assertHasErrors(['product.quantity' => 'required']);

        $this->assertDatabaseHas('products', [
            'quantity' => $product->quantity
        ]);
    }

    /** @test  */
    public function the_quantity_field_is_numeric()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->set('product.quantity', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['product.quantity' => 'numeric']);

        $this->assertDatabaseHas('products', [
            'quantity' => $product->quantity
        ]);
    }

}
