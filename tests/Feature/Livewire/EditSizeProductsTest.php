<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Admin\ColorSize;
use App\Http\Livewire\Admin\EditProduct;
use App\Http\Livewire\Admin\SizeProduct;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class EditSizeProductsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_deletes_a_product_with_color_and_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $this->createSize($product->id, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('images', 4);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->call('delete');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('images', 0);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 0);
        $this->assertDatabaseCount('color_size', 0);
    }

    /** @test */
    public function it_adds_a_size_to_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 0);

        Livewire::test(SizeProduct::class, [
            'product' => $product
        ])->set('name', 'Talla Nueva')
            ->call('save');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 1);

        $this->assertDatabaseHas('sizes', [
            'name' => 'Talla Nueva',
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function it_updates_a_size_name()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $size = $this->createSize($product->id, []);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 1);

        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'name' => $size->name,
            'product_id' => $product->id
        ]);

        Livewire::test(SizeProduct::class, [
            'product' => $product
        ])->set('size', $size)
            ->set('name_edit', 'Nuevo nombre')
            ->call('update');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 1);

        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'name' => 'Nuevo nombre',
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function it_deletes_a_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $size = $this->createSize($product->id, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'name' => $size->name,
            'product_id' => $product->id
        ]);

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id
        ]);

        Livewire::test(SizeProduct::class, [
            'product' => $product
        ])->call('delete', $size);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 0);
        $this->assertDatabaseCount('color_size', 0);
    }

    /** @test */
    public function it_adds_a_color_to_a_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $size = $this->createSize($product->id, []);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 0);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->set('color_id', $color->id)
            ->set('quantity', 50)
            ->call('save');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 50
        ]);
    }

    /** @test */
    public function it_updates_a_color_and_its_quantity_within_a_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$colorA]);

        $size = $this->createSize($product->id, [$colorA]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 2);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        $this->assertDatabaseHas('color_size', [
            'color_id' => $colorA->id,
            'size_id' => $size->id,
            'quantity' => 12
        ]);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->call('edit', $size->colors()->find($colorA->id)->pivot->id)
            ->set('pivot_color_id', $colorB->id)
            ->set('pivot_quantity', 50)
            ->call('update');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 2);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        $this->assertDatabaseHas('color_size', [
            'color_id' => $colorB->id,
            'size_id' => $size->id,
            'quantity' => 50
        ]);
    }

    /** @test */
    public function it_deletes_a_color()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $size = $this->createSize($product->id, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->call('delete', $size->colors()->find($color->id)->pivot->id);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 0);
    }

    /** @test */
    public function it_can_not_add_an_existing_size()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $size = $this->createSize($product->id, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 1);

        Livewire::test(SizeProduct::class, [
            'product' => $product
        ])->set('name', $size->name)
            ->call('save');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sizes', 1);

        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'name' => $size->name,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function it_updates_the_color_quantity_if_we_add_this_color_again()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $size = $this->createSize($product->id, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 12
        ]);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->set('color_id', $color->id)
            ->set('quantity', 8)
            ->call('save');

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('sizes', 1);
        $this->assertDatabaseCount('color_size', 1);

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => 20
        ]);
    }

    /** @test  */
    public function the_size_name_field_is_required_when_adding_a_size_to_a_product()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(SizeProduct::class, [
            'product' => $product
        ])->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('sizes', 1)
            ->assertDatabaseHas('sizes', [
                'id' => $size->id,
                'name' => $size->name,
                'product_id' => $product->id
            ]);
    }

    /** @test  */
    public function the_color_id_field_is_required_when_adding_a_color_to_a_size()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->set('color_id', '')
            ->call('save')
            ->assertHasErrors(['color_id' => 'required']);

        $this->assertDatabaseCount('color_size', 1)
            ->assertDatabaseHas('color_size', [
                'color_id' => $color->id,
                'size_id' => $size->id
            ]);
    }

    /** @test  */
    public function the_color_quantity_field_is_required_when_adding_a_color_to_a_size()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->set('quantity', '')
            ->call('save')
            ->assertHasErrors(['quantity' => 'required']);

        $this->assertDatabaseCount('color_size', 1)
            ->assertDatabaseHas('color_size', [
                'color_id' => $color->id,
                'size_id' => $size->id,
                'quantity' => $size->colors()->find($color->id)->pivot->quantity
            ]);
    }

    /** @test  */
    public function the_color_quantity_field_is_numeric_when_adding_a_color_to_a_size()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(ColorSize::class, [
            'size' => $size
        ])->set('quantity', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['quantity' => 'numeric']);

        $this->assertDatabaseCount('color_size', 1)
            ->assertDatabaseHas('color_size', [
                'color_id' => $color->id,
                'size_id' => $size->id,
                'quantity' => $size->colors()->find($color->id)->pivot->quantity
            ]);
    }
}
