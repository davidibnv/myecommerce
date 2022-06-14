<?php

namespace Tests\Browser;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ShowProductsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_the_details_view_of_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);
        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($category, $subcategory, $product) {
            $browser->visit('/')
                ->click('@show-category-' . $category->id)
                ->click('@view-product-' . $product->id)
                ->assertUrlIs(route('products.show', $product))
                ->assertSee($product->name)
                ->assertSee(Str::title($product->brand->name))
                ->assertSee($product->price)
                ->screenshot('show-products/show-product-details');
        });
    }

    /** @test */
    public function the_details_view_of_a_product_without_color_and_size_contains_the_necessary_elements()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);
        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($category, $product, $brand) {
            $browser->visit('/')
                ->click('@show-category-' . $category->id)
                ->click('@view-product-' . $product->id)
                ->assertUrlIs(route('products.show', $product))
                ->assertAttribute('@product-image-' . $product->images->find(1)->id, 'src', '/storage/' . $product->images->find(1)->url)
                ->assertAttribute('@product-image-' . $product->images->find(2)->id, 'src', '/storage/' . $product->images->find(2)->url)
                ->assertAttribute('@product-image-' . $product->images->find(3)->id, 'src', '/storage/' . $product->images->find(3)->url)
                ->assertAttribute('@product-image-' . $product->images->find(4)->id, 'src', '/storage/' . $product->images->find(4)->url)
                ->assertSee($product->name)
                ->assertSee(Str::title($brand->name))
                ->assertSee($product->price)
                ->assertSee($product->description)
                ->assertSee('Stock disponible: ' . $product->quantity)
                ->assertSeeIn('@decrease-quantity-btn', '-')
                ->assertSeeIn('@increase-quantity-btn', '+')
                ->assertPresent('@add-to-cart-btn')
                ->screenshot('show-products/show-product-details-without-color-and-size');
        });
    }

    /** @test */
    public function the_details_view_of_a_product_with_color_contains_the_necessary_elements()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($colorA, $colorB));

        $productQuantity = $product->colors->first()->pivot->quantity;

        $this->browse(function (Browser $browser) use ($category, $product, $brand, $productQuantity, $colorA, $colorB) {
            $browser->visit('/')
                ->click('@show-category-' . $category->id)
                ->click('@view-product-' . $product->id)
                ->assertUrlIs(route('products.show', $product))
                ->assertAttribute('@product-image-' . $product->images->find(1)->id, 'src', '/storage/' . $product->images->find(1)->url)
                ->assertAttribute('@product-image-' . $product->images->find(2)->id, 'src', '/storage/' . $product->images->find(2)->url)
                ->assertAttribute('@product-image-' . $product->images->find(3)->id, 'src', '/storage/' . $product->images->find(3)->url)
                ->assertAttribute('@product-image-' . $product->images->find(4)->id, 'src', '/storage/' . $product->images->find(4)->url)
                ->assertSee($product->name)
                ->assertSee(Str::title($brand->name))
                ->assertSee($product->price)
                ->assertSee($product->description)
                ->pause(2000)
                ->assertSelectHasOptions('@color-dropdown', [$colorA->id, $colorB->id])
                ->select('@color-dropdown', $colorA->id)
                ->assertSelected('@color-dropdown', $colorA->id)
                ->pause(1000)
                ->assertSee('Stock disponible: ' . $productQuantity)
                ->assertSeeIn('@decrease-quantity-btn', '-')
                ->assertSeeIn('@increase-quantity-btn', '+')
                ->assertPresent('@add-to-cart-btn')
                ->screenshot('show-products/show-product-details-with-color');
        });
    }

    /** @test */
    public function the_details_view_of_a_product_with_color_and_size_contains_the_necessary_elements()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($colorA, $colorB));

        $sizeA = $this->createSize($product->id, array($colorA, $colorB));
        $sizeB = $this->createSize($product->id, array($colorA, $colorB));

        $productQuantity = $product->sizes->find($sizeA->id)->colors->find($colorA->id)->pivot->quantity;

        $this->browse(function (Browser $browser) use ($category, $product, $brand, $productQuantity, $colorA, $colorB, $sizeA, $sizeB) {
            $browser->visit('/')
                ->click('@show-category-' . $category->id)
                ->click('@view-product-' . $product->id)
                ->assertUrlIs(route('products.show', $product))
                ->assertAttribute('@product-image-' . $product->images->find(1)->id, 'src', '/storage/' . $product->images->find(1)->url)
                ->assertAttribute('@product-image-' . $product->images->find(2)->id, 'src', '/storage/' . $product->images->find(2)->url)
                ->assertAttribute('@product-image-' . $product->images->find(3)->id, 'src', '/storage/' . $product->images->find(3)->url)
                ->assertAttribute('@product-image-' . $product->images->find(4)->id, 'src', '/storage/' . $product->images->find(4)->url)
                ->assertSee($product->name)
                ->assertSee(Str::title($brand->name))
                ->assertSee($product->price)
                ->assertSee($product->description)
                ->pause(2000)
                ->assertSelectHasOptions('@size-dropdown', [$sizeA->id, $sizeB->id])
                ->select('@size-dropdown', $sizeA->id)
                ->assertSelected('@size-dropdown', $sizeA->id)
                ->pause(1000)
                ->assertSelectHasOptions('@color-dropdown', [$colorA->id, $colorB->id])
                ->select('@color-dropdown', $colorA->id)
                ->assertSelected('@color-dropdown', $colorA->id)
                ->pause(1000)
                ->assertSee('Stock disponible: ' . $productQuantity)
                ->assertSeeIn('@decrease-quantity-btn', '-')
                ->assertSeeIn('@increase-quantity-btn', '+')
                ->assertPresent('@add-to-cart-btn')
                ->screenshot('show-products/show-product-details-with-color-and-size');
        });
    }

    /** @test */
    public function the_decrease_quantity_button_in_the_details_view_has_a_limit()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);
        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit(route('products.show', $product))
                ->assertSeeIn('@product-quantity', '1')
                ->assertButtonDisabled('@decrease-quantity-btn')
                ->press('@decrease-quantity-btn')
                ->assertSeeIn('@product-quantity', '1')
                ->assertButtonDisabled('@decrease-quantity-btn')
                ->screenshot('show-products/decrease-product-qty-button-limit');
        });
    }

    /** @test */
    public function the_increase_quantity_button_in_the_details_view_has_a_limit()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);
        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visit(route('products.show', $product))
                ->assertSeeIn('@product-quantity', '1')
                ->assertButtonEnabled('@increase-quantity-btn');

            for ($i = 1; $i < $product->quantity; $i++) {
                $browser->press('@increase-quantity-btn')
                    ->pause(500);
            }

            $browser->assertSeeIn('@product-quantity', $product->quantity)
                ->assertButtonDisabled('@increase-quantity-btn')
                ->screenshot('show-products/increase-product-qty-button-limit');
        });
    }

    /** @test */
    public function it_doesnt_show_size_nor_color_dropdowns_in_the_details_view_when_the_product_doesnt_have_these_features()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);
        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->assertNotPresent('@size-dropdown')
                ->assertNotPresent('@color-dropdown')
                ->screenshot('show-products/doesnt-show-dropdowns-for-simple-product');
        });
    }

    /** @test */
    public function it_only_shows_the_color_dropdown_in_the_details_view_when_the_product_only_have_this_feature()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($colorA, $colorB));

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->assertNotPresent('@size-dropdown')
                ->assertPresent('@color-dropdown')
                ->screenshot('show-products/only-show-color-dropdown-when-product-only-has-color');
        });
    }

    /** @test */
    public function it_shows_size_and_color_dropdowns_in_the_details_view_when_the_product_has_both_features()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $colorA = $this->createColor();
        $colorB = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($colorA, $colorB));

        $this->createSize($product->id, array($colorA, $colorB));
        $this->createSize($product->id, array($colorA, $colorB));

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->assertPresent('@size-dropdown')
                ->assertPresent('@color-dropdown')
                ->screenshot('show-products/show-color-and-size-dropdowns-when-product-have-them');
        });
    }

    /** @test */
    public function it_shows_the_available_stock_of_a_simple_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->waitForTextIn('@available-stock', $product->quantity)
                ->screenshot('show-products/show-available-stock-of-simple-product');
        });
    }

    /** @test */
    public function it_shows_the_available_stock_of_a_color_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $quantity = $product->colors()->find($color->id)->pivot->quantity;

        $this->browse(function (Browser $browser) use ($product, $quantity, $color) {
            $browser->visitRoute('products.show', $product)
                ->select('@color-dropdown', $color->id)
                ->waitForTextIn('@available-stock', $quantity)
                ->screenshot('show-products/show-available-stock-of-color-product');
        });
    }

    /** @test */
    public function it_shows_the_available_stock_of_a_size_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $quantity = $product->sizes()->find($size->id)->colors()->find($color->id)->pivot->quantity;

        $this->browse(function (Browser $browser) use ($product, $size, $color, $quantity) {
            $browser->visitRoute('products.show', $product)
                ->select('@size-dropdown', $size->id)
                ->assertSelected('@size-dropdown', $size->id)
                ->pause(1000)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->waitForTextIn('@available-stock', $quantity)
                ->screenshot('show-products/show-available-stock-of-size-product');
        });
    }


    /** @test */
    public function the_stock_of_a_simple_product_changes_when_adding_it_to_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $initialStock = $product->quantity;

        $this->browse(function (Browser $browser) use ($product, $initialStock) {
            $browser->visitRoute('products.show', $product)
                ->assertSeeIn('@available-stock', $initialStock)
                ->press('@add-to-cart-btn')
                ->waitForTextIn('@available-stock', $initialStock - 1)
                ->screenshot('show-products/stock-of-simple-product-changes-when-adding-to-cart');
        });
    }

    /** @test */
    public function the_stock_of_a_color_product_changes_when_adding_it_to_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $initialStock = $product->colors()->find($color->id)->pivot->quantity;

        $this->browse(function (Browser $browser) use ($product, $color, $initialStock) {
            $browser->visitRoute('products.show', $product)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->assertSeeIn('@available-stock', $initialStock)
                ->press('@add-to-cart-btn')
                ->waitForTextIn('@available-stock', $initialStock - 1)
                ->screenshot('show-products/stock-of-color-product-changes-when-adding-to-cart');
        });
    }

    /** @test */
    public function the_stock_of_a_size_product_changes_when_adding_it_to_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $initialStock = $product->sizes()->find($size->id)->colors()->find($color->id)->pivot->quantity;

        $this->browse(function (Browser $browser) use ($product, $size, $color, $initialStock) {
            $browser->visitRoute('products.show', $product)
                ->select('@size-dropdown', $size->id)
                ->assertSelected('@size-dropdown', $size->id)
                ->pause(1000)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->pause(1000)
                ->assertSeeIn('@available-stock', $initialStock)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->waitForTextIn('@available-stock', $initialStock - 1)
                ->screenshot('show-products/stock-of-size-product-changes-when-adding-to-cart');
        });
    }
}
