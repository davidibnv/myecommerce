<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Admin\ColorProduct;
use App\Http\Livewire\Admin\ColorSize;
use App\Http\Livewire\Admin\CreateProduct;
use App\Http\Livewire\Admin\SizeProduct;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CreateProductsTest extends TestCase
{

    /** @test */
    public function it_creates_a_simple_product()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save');

        $this->assertDatabaseCount('products', 1)
            ->assertDatabaseHas('products', [
                'name' => 'Nombre del producto',
                'slug' => 'nombre-del-producto',
                'description' => 'Descripción del producto',
                'price' => 5,
                'subcategory_id' => $data['subcategory']->id,
                'brand_id' => $data['brand']->id,
                'quantity' => 10
            ]);
    }

    /** @test */
    public function it_creates_a_product_with_color()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, true, false, true, true, false, false, true);

        $this->actingAs($data['user']);

        $this->assertDatabaseCount('products', 0)
            ->assertDatabaseCount('colors', 1)
            ->assertDatabaseCount('color_product', 0);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->call('save');

        $this->assertDatabaseCount('products', 1)
            ->assertDatabaseHas('products', [
                'name' => 'Nombre del producto',
                'slug' => 'nombre-del-producto',
                'description' => 'Descripción del producto',
                'price' => 5,
                'subcategory_id' => $data['subcategory']->id,
                'brand_id' => $data['brand']->id,
                'quantity' => null
            ]);

        $product = Product::first();

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->set('color_id', $data['color']->id)
            ->set('quantity', 25)
            ->call('save');

        $this->assertDatabaseCount('color_product', 1)
            ->assertDatabaseHas('color_product', [
                'color_id' => $data['color']->id,
                'product_id' => $product->id,
                'quantity' => 25
            ]);
    }

    /** @test */
    public function it_creates_a_product_with_color_and_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 0)
            ->assertDatabaseCount('colors', 1)
            ->assertDatabaseCount('sizes', 0)
            ->assertDatabaseCount('color_size', 0);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('brand_id', $brand->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->call('save');

        $this->assertDatabaseCount('products', 1)
            ->assertDatabaseHas('products', [
                'name' => 'Nombre del producto',
                'slug' => 'nombre-del-producto',
                'description' => 'Descripción del producto',
                'price' => 5,
                'subcategory_id' => $subcategory->id,
                'brand_id' => $brand->id,
                'quantity' => null
            ]);

        $product = Product::first();

        Livewire::test(SizeProduct::class, [
            'product' => $product
        ])->set('name', 'Talla Ejemplo')
            ->call('save');

        $this->assertDatabaseCount('sizes', 1)
            ->assertDatabaseHas('sizes', [
                'name' => 'Talla Ejemplo',
                'product_id' => $product->id
            ]);

        $size = Size::first();

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->set('color_id', $color->id)
            ->set('quantity', 25)
            ->call('save');

        $this->assertDatabaseCount('color_size', 1)
            ->assertDatabaseHas('color_size', [
                'color_id' => $color->id,
                'size_id' => $size->id,
                'quantity' => 25
            ]);
    }

    /** @test  */
    public function the_category_id_field_is_required()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', '')
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['category_id'=> 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test  */
    public function the_subcategory_id_field_is_required()
    {
/*        $category = $this->createCategory();
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', '')
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['subcategory_id'=> 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_name_field_is_required()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', '')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_slug_field_is_required()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', '')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['slug' => 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_slug_field_is_unique()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save');

        $this->assertDatabaseCount('products', 1);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['slug' => 'unique']);

        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function the_description_field_is_required()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', '')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['description' => 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_brand_id_field_is_required()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', '')
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', 5)
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['brand_id' => 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_price_field_is_required()
    {
/*        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);*/

        $data = $this->generateData(true,true, true, false, false, true, true);

        $this->actingAs($data['user']);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $data['category']->id)
            ->set('subcategory_id', $data['subcategory']->id)
            ->set('brand_id', $data['brand']->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', '')
            ->set('quantity', 10)
            ->call('save')
            ->assertHasErrors(['price' => 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_quantity_field_is_required_when_creating_a_simple_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('brand_id', $brand->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', '5')
            ->set('quantity', '')
            ->call('save')
            ->assertHasErrors(['quantity' => 'required']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function the_quantity_field_is_optional_when_creating_a_product_with_color()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('brand_id', $brand->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', '5')
            ->set('quantity', '')
            ->call('save')
            ->assertHasNoErrors('quantity');

        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function the_quantity_field_is_optional_when_creating_a_product_with_color_and_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->set('category_id', $category->id)
            ->set('subcategory_id', $subcategory->id)
            ->set('brand_id', $brand->id)
            ->set('name', 'Nombre del producto')
            ->set('slug', 'nombre-del-producto')
            ->set('description', 'Descripción del producto')
            ->set('price', '5')
            ->set('quantity', '')
            ->call('save')
            ->assertHasNoErrors('quantity');

        $this->assertDatabaseCount('products', 1);
    }

}
