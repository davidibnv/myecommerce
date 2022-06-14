<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Admin\ColorProduct;
use App\Http\Livewire\Admin\EditProduct;
use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class EditColorProductsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_deletes_a_product_with_colors()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$colorA, $colorB]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('images', 4);
        $this->assertDatabaseCount('colors', 2);
        $this->assertDatabaseCount('color_product', 2);

        Livewire::test(EditProduct::class, [
            'product' => $product
        ])->call('delete');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('images', 0);
        $this->assertDatabaseCount('colors', 2);
        $this->assertDatabaseCount('color_product', 0);
    }

    /** @test */
    public function it_adds_color_to_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('color_product', 0);

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->set('color_id', $color->id)
            ->set('quantity', 10)
            ->call('save');

        $this->assertDatabaseCount('color_product', 1);

        $this->assertDatabaseHas('color_product', [
            'color_id' => $color->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function it_deletes_a_color_of_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$color]);

        $user = $this->createAdminUser();

        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 1);
        $this->assertDatabaseCount('color_product', 1);

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->call('delete', $product->colors->find($color->id)->pivot->id);

        $this->assertDatabaseCount('color_product', 0);
    }

    /** @test */
    public function it_updates_the_name_and_the_quantity_of_a_color()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, [$colorA]);

        $user = $this->createAdminUser();
        $this->actingAs($user);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('colors', 2);
        $this->assertDatabaseCount('color_product', 1);

        $this->assertDatabaseHas('color_product', [
            'color_id' => $colorA->id,
            'product_id' => $product->id,
            'quantity' => 50
        ]);

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->call('edit', $product->colors->find($colorA->id)->pivot->id)
            ->set('pivot_color_id', $colorB->id)
            ->set('pivot_quantity', 75)
            ->call('update');

        $this->assertDatabaseCount('color_product', 1);

        $this->assertDatabaseHas('color_product', [
            'color_id' => $colorB->id,
            'product_id' => $product->id,
            'quantity' => 75
        ]);
    }

    /** @test  */
    public function the_color_id_field_is_required_when_adding_a_color_to_a_product()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->set('color_id', '')
            ->call('save')
            ->assertHasErrors(['color_id' => 'required']);

        $this->assertDatabaseCount('color_product', 1)
            ->assertDatabaseHas('color_product', [
                'color_id' => $color->id,
                'product_id' => $product->id
            ]);
    }

    /** @test  */
    public function the_color_quantity_field_is_required_when_adding_a_color_to_a_product()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->set('color_id', $color->id)
            ->set('quantity', '')
            ->call('save')
            ->assertHasErrors(['quantity' => 'required']);

        $this->assertDatabaseCount('color_product', 1)
            ->assertDatabaseHas('color_product', [
                'color_id' => $color->id,
                'product_id' => $product->id,
                'quantity' => $product->colors()->find($color->id)->pivot->quantity
            ]);
    }

    /** @test  */
    public function the_color_quantity_field_is_numeric_when_adding_a_color_to_a_product()
    {
        $category = $this->createCategory();

        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $user = $this->createAdminUser();

        $this->actingAs($user);

        Livewire::test(ColorProduct::class, [
            'product' => $product
        ])->set('color_id', $color->id)
            ->set('quantity', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['quantity' => 'numeric']);

        $this->assertDatabaseCount('color_product', 1)
            ->assertDatabaseHas('color_product', [
                'color_id' => $color->id,
                'product_id' => $product->id,
                'quantity' => $product->colors()->find($color->id)->pivot->quantity
            ]);
    }
}
