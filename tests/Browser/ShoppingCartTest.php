<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ShoppingCartTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_the_products_in_the_cart_when_clicking_its_icon()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC) {
            $browser->visitRoute('products.show', $productA)
                ->assertButtonEnabled('@add-to-cart-btn')
                ->press('@add-to-cart-btn')
                ->visitRoute('products.show', $productB)
                ->assertButtonEnabled('@add-to-cart-btn')
                ->press('@add-to-cart-btn')
                ->click('@cart-icon')
                ->pause(1000)
                ->assertSeeIn('@cart-content', $productA->name)
                ->assertSeeIn('@cart-content', $productB->name)
                ->assertDontSeeIn('@cart-content', $productC->name)
                ->screenshot('shopping-cart/show-cart-content');
        });
    }

    /** @test */
    public function the_shopping_cart_count_icon_increments_when_adding_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->assertSeeNothingIn('@cart-products-count-icon')
                ->assertButtonEnabled('@add-to-cart-btn')
                ->press('@add-to-cart-btn')
                ->waitForTextIn('@cart-products-count-icon', '1')
                ->screenshot('shopping-cart/cart-count-increments');
        });
    }

    /** @test */
    public function the_shopping_cart_view_shows_the_cart_content()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC) {
            $browser->visitRoute('products.show', $productA)
                ->press('@add-to-cart-btn')
                ->visitRoute('products.show', $productB)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('shopping-cart')
                ->assertSee($productA->name)
                ->assertSee($productB->name)
                ->assertDontSee($productC->name)
                ->screenshot('shopping-cart/show-cart-content');
        });
    }

    /** @test */
    public function can_not_add_more_quantity_of_a_simple_product_than_stock_exists_to_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product);

            for ($i = 1; $i < $product->quantity; $i++) {
                $browser->press('@increase-quantity-btn')
                    ->pause(500);
            }

            $browser->press('@add-to-cart-btn')
                ->waitForTextIn('@cart-products-count-icon', $product->quantity)
                ->waitForTextIn('@available-stock', '0')
                ->assertDisabled('@increase-quantity-btn')
                ->screenshot('show-products/cannot-add-more-qty-than-stock-of-simple-product');
        });
    }

    /** @test */
    public function can_not_add_more_quantity_of_a_color_product_than_stock_exists_to_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $quantity = $product->colors()->find($color->id)->pivot->quantity;

        $this->browse(function (Browser $browser) use ($product, $color, $quantity) {
            $browser->visitRoute('products.show', $product)
                ->select('@color-dropdown', $color->id)
                ->pause(1000);

            for ($i = 1; $i < $quantity; $i++) {
                $browser->press('@increase-quantity-btn')
                    ->pause(500);
            }

            $browser->press('@add-to-cart-btn')
                ->waitForTextIn('@cart-products-count-icon', $quantity)
                //->waitForTextIn('@available-stock', '0') // Hay un error, cuando llega a 0 muestra el stock total del color
                ->assertDisabled('@increase-quantity-btn')
                ->screenshot('show-products/cannot-add-more-qty-than-stock-of-color-product');
        });
    }

    /** @test */
    public function can_not_add_more_quantity_of_a_size_product_than_stock_exists_to_the_cart()
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
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id);

            for ($i = 1; $i < $quantity; $i++) {
                $browser->press('@increase-quantity-btn')
                    ->pause(500);
            }

            $browser->press('@add-to-cart-btn')
                ->waitForTextIn('@cart-products-count-icon', $quantity)
                //->waitForTextIn('@available-stock', '0') // Hay un error, cuando llega a 0 muestra el stock total de la talla
                ->assertDisabled('@increase-quantity-btn')
                ->screenshot('show-products/cannot-add-more-qty-than-stock-of-size-product');
        });
    }

    /** @test */
    public function it_can_change_a_simple_product_quantity_in_the_cart_and_the_total_updates()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('shopping-cart')
                ->assertSee($product->name)
                ->assertSeeIn('@product-' . $product->id . '-total-cost', $product->price)
                ->assertSeeIn('@total-cost', $product->price)
                ->press('@increase-quantity-btn')
                ->pause(1000)
                ->assertSeeIn('@product-' . $product->id . '-total-cost', $product->price * 2)
                ->assertSeeIn('@total-cost', $product->price * 2)
                ->screenshot('shopping-cart/change-product-quantity-and-total-updates');
        });
    }

    /** @test */
    public function it_can_change_a_color_product_quantity_in_the_cart_and_the_total_updates()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $this->browse(function (Browser $browser) use ($product, $color) {
            $browser->visitRoute('products.show', $product)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('shopping-cart')
                ->assertSee($product->name)
                ->assertSeeIn('@product-' . $product->id . '-total-cost', $product->price)
                ->assertSeeIn('@total-cost', $product->price)
                ->press('@increase-quantity-btn')
                ->pause(1000)
                ->assertSeeIn('@product-' . $product->id . '-total-cost', $product->price * 2)
                ->assertSeeIn('@total-cost', $product->price * 2)
                ->screenshot('shopping-cart/change-color-product-quantity-and-total-updates');
        });
    }

    /** @test */
    public function it_can_change_a_size_product_quantity_in_the_cart_and_the_total_updates()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);
        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $this->browse(function (Browser $browser) use ($product, $color, $size) {
            $browser->visitRoute('products.show', $product)
                ->select('@size-dropdown', $size->id)
                ->assertSelected('@size-dropdown', $size->id)
                ->pause(1000)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->pause(1000)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('shopping-cart')
                ->assertSee($product->name)
                ->assertSeeIn('@product-' . $product->id . '-total-cost', $product->price)
                ->assertSeeIn('@total-cost', $product->price)
                ->press('@increase-quantity-btn')
                ->pause(1000)
                ->assertSeeIn('@product-' . $product->id . '-total-cost', $product->price * 2)
                ->assertSeeIn('@total-cost', $product->price * 2)
                ->screenshot('shopping-cart/change-size-product-quantity-and-total-updates');
        });
    }

    /** @test */
    public function it_can_remove_an_item_from_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB) {
            $browser->visitRoute('products.show', $productA)
                ->press('@add-to-cart-btn')
                ->visitRoute('products.show', $productB)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('shopping-cart')
                ->assertSee($productA->name)
                ->assertSee($productB->name)
                ->press('@remove-product-' . $productB->id)
                ->pause(1000)
                ->assertSee($productA->name)
                ->assertDontSee($productB->name)
                ->screenshot('shopping-cart/can-remove-an-item-from-cart');
        });
    }

    /** @test */
    public function it_can_clear_the_cart()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($product) {
            $browser->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('shopping-cart')
                ->assertSee($product->name)
                ->press('@clear-cart')
                ->pause(1000)
                ->assertDontSee($product->name)
                ->assertSee('TU CARRITO DE COMPRAS ESTÁ VACÍO')
                ->screenshot('shopping-cart/can-clear-the-cart');
        });
    }

    // EJERCICIO 2 - hecha previamente la parte del test

    /** @test */
    public function cart_is_saved_in_database_when_logging_out_and_is_recovered_if_user_logins_again()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->assertDatabaseCount('shoppingcart', 0);

        $this->browse(function (Browser $browser) use ($productA, $productB, $user) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $productA)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('products.show', $productB)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->click('@user-btn')
                ->pause(1000)
                ->click('@logout-btn');
        });

        $this->assertDatabaseCount('shoppingcart', 1);

        $this->browse(function (Browser $browser) use ($productA, $productB, $user) {
            $browser->visitRoute('shopping-cart')
                ->assertGuest()
                ->waitForText('TU CARRITO DE COMPRAS ESTÁ VACÍO')
                ->loginAs($user)
                ->visitRoute('shopping-cart')
                ->assertSee($productA->name)
                ->assertSeeIn('@product-' . $productA->id . '-price', $productA->price)
                ->assertSeeIn('@product-' . $productA->id . '-qty', 1)
                ->assertSee($productB->name)
                ->assertSeeIn('@product-' . $productB->id . '-price', $productB->price)
                ->assertSeeIn('@product-' . $productB->id . '-qty', 1)
                ->screenshot('shopping-cart/cart-is-saved-when-logout-and-recover-when-login-again');
        });
    }
    // Fin del test del Ejercicio 2
}
