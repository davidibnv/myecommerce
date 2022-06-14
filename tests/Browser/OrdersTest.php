<?php

namespace Tests\Browser;

use App\Models\City;
use App\Models\Department;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OrdersTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function only_logged_users_can_access_to_the_create_order_view()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($product, $user) {
            $browser->assertGuest()
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->assertRouteIs('login')
                ->loginAs($user)
                ->visitRoute('orders.create')
                ->assertRouteIs('orders.create')
                ->screenshot('orders/only-logged-users-can-access-to-create-order-view');
        });
    }

    /** @test */
    public function address_form_is_hidden_if_store_pickup_option_is_selected()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($product, $user) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->radio('envio_type', 1)
                ->assertMissing('@address-form')
                ->screenshot('orders/address-form-is-hidden-when-store-pickup');
        });
    }

    /** @test */
    public function address_form_is_shown_if_home_delivery_option_is_selected()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($product, $user) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->radio('envio_type', 2)
                ->assertVisible('@address-form')
                ->screenshot('orders/address-form-is-shown-when-home-delivery');
        });
    }

    /** @test */
    public function it_creates_an_order_then_destroy_cart_and_redirect_when_store_pickup_is_selected()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->assertDatabaseCount('orders', 0);

        $this->browse(function (Browser $browser) use ($product, $user) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Nombre')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->press('@create-order')
                ->pause(1000)
                ->assertRouteIs('orders.payment', Order::first())
                ->screenshot('orders/it-creates-an-order-when-store-pickup-option');
        });

        $this->assertSame(0, Cart::count());

        $this->assertDatabaseCount('orders', 1);
    }

    /** @test */
    public function it_creates_an_order_then_destroy_cart_and_redirect_when_home_delivery_is_selected()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $deparment = Department::factory()->create();

        $city = City::factory()->create([
            'department_id' => $deparment->id
        ]);

        $district = District::factory()->create([
            'city_id' => $city->id
        ]);

        $this->assertDatabaseCount('orders', 0);

        $this->browse(function (Browser $browser) use ($product, $user, $deparment, $city, $district) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->radio('envio_type', 2)
                ->select('@department', $deparment->id)
                ->pause(1000)
                ->select('@city', $city->id)
                ->pause(1000)
                ->select('@district', $district->id)
                ->type('@contact-name', 'Nombre')
                ->type('@contact-phone', '657485734')
                ->type('@address', 'Calle Ejemplo 1')
                ->type('@reference', 'Referencia')
                ->press('@create-order')
                ->pause(1000)
                ->assertRouteIs('orders.payment', Order::first())
                ->screenshot('orders/it-creates-an-order-when-home-delivery-option');
        });

        $this->assertSame(0, Cart::count());

        $this->assertDatabaseCount('orders', 1);
    }

    /** @test */
    public function the_address_selects_show_correct_options_when_selecting_either_one()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $deparmentA = Department::factory()->create();
        $deparmentB = Department::factory()->create();

        $cityA = City::factory()->create([
            'department_id' => $deparmentA->id
        ]);

        $cityB = City::factory()->create([
            'department_id' => $deparmentB->id
        ]);

        $districtA = District::factory()->create([
            'city_id' => $cityA->id
        ]);

        $districtB = District::factory()->create([
            'city_id' => $cityB->id
        ]);

        $this->browse(function (Browser $browser) use ($product, $user, $deparmentA, $deparmentB, $cityA, $cityB, $districtA, $districtB) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->radio('envio_type', 2)
                ->assertSelectHasOptions('@department', [$deparmentA->id, $deparmentB->id])
                ->select('@department', $deparmentA->id)
                ->pause(1000)
                ->assertSelectHasOption('@city', $cityA->id)
                ->assertSelectMissingOption('@city', $cityB->id)
                ->select('@city', $cityA->id)
                ->pause(1000)
                ->assertSelectHasOption('@district', $districtA->id)
                ->assertSelectMissingOption('@district', $districtB->id)
                ->screenshot('orders/address-selects-show-correct-options');
        });
    }

    /** @test */
    public function the_stock_of_a_simple_product_updates_in_database_when_an_order_is_created()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);
        $initialStock = $product->quantity;

        $user = User::factory()->create();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 15
        ]);

        $this->browse(function (Browser $browser) use ($user, $product, $initialStock) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Nombre')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->press('@create-order')
                ->screenshot('orders/stock-of-simple-product-updates-in-db-when-creating-order');
        });

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => $initialStock - 1
        ]);
    }

    /** @test */
    public function the_stock_of_a_color_product_updates_in_database_when_an_order_is_created()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));
        $initialStock = $product->colors()->find($color->id)->pivot->quantity;

        $user = User::factory()->create();

        $this->assertDatabaseHas('color_product', [
            'color_id' => $color->id,
            'product_id' => $product->id,
            'quantity' => $initialStock
        ]);

        $this->browse(function (Browser $browser) use ($user, $product, $color, $initialStock) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Nombre')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->press('@create-order')
                ->screenshot('orders/stock-of-color-product-updates-in-db-when-creating-order');
        });

        $this->assertDatabaseHas('color_product', [
            'color_id' => $color->id,
            'product_id' => $product->id,
            'quantity' => $initialStock - 1
        ]);
    }

    /** @test */
    public function the_stock_of_a_size_product_updates_in_database_when_an_order_is_created()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id, true, true);
        $brand = $this->createBrand($category->id);

        $color = $this->createColor();

        $product = $this->createProduct($subcategory->id, $brand->id, Product::PUBLICADO, array($color));

        $size = $this->createSize($product->id, array($color));

        $initialStock = $product->sizes()->find($size->id)->colors()->find($color->id)->pivot->quantity;

        $user = User::factory()->create();

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => $initialStock
        ]);

        $this->browse(function (Browser $browser) use ($user, $product, $size, $color, $initialStock) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->select('@size-dropdown', $size->id)
                ->assertSelected('@size-dropdown', $size->id)
                ->pause(1000)
                ->select('@color-dropdown', $color->id)
                ->assertSelected('@color-dropdown', $color->id)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Nombre')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->pause(1000)
                ->press('@create-order')
                ->screenshot('orders/stock-of-size-product-updates-in-db-when-creating-order');
        });

        $this->assertDatabaseHas('color_size', [
            'color_id' => $color->id,
            'size_id' => $size->id,
            'quantity' => $initialStock - 1
        ]);
    }

    /** @test */
    public function pending_orders_are_cancelled_after_a_while()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Nombre')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->press('@create-order')
                ->pause(1000);

            $order = Order::first();
            $order->created_at = now()->subMinutes(11);
            $order->save();

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'user_id' => $user->id,
                'status' => 1
            ]);

            $this->artisan('schedule:run');

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'user_id' => $user->id,
                'status' => 5
            ]);
        });
    }

}
