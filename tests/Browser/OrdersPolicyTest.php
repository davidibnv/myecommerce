<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OrdersPolicyTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test  */
    public function users_can_not_access_to_others_orders()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->browse(function (Browser $browser) use ($userA, $userB, $product) {
            $browser->loginAs($userA)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Usuario A')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->press('@create-order')
                ->pause(1000)
                ->logout();

            $order = Order::first();

            $browser->loginAs($userB)
                ->visitRoute('orders.show', $order)
                ->assertSee('ESTA ACCIÓN NO ESTÁ AUTORIZADA')
                ->screenshot('orders-policy/show-order-policy');
        });
    }

    /** @test  */
    public function users_can_not_access_to_pay_others_orders()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->browse(function (Browser $browser) use ($userA, $userB, $product) {
            $browser->loginAs($userA)
                ->visitRoute('products.show', $product)
                ->press('@add-to-cart-btn')
                ->pause(1000)
                ->visitRoute('orders.create')
                ->type('@contact-name', 'Usuario A')
                ->type('@contact-phone', '657485734')
                ->radio('envio_type', 1)
                ->press('@create-order')
                ->pause(1000)
                ->logout();

            $order = Order::first();

            $browser->loginAs($userB)
                ->visitRoute('orders.payment', $order)
                ->assertSee('ESTA ACCIÓN NO ESTÁ AUTORIZADA')
                ->screenshot('orders-policy/payment-order-policy');
        });
    }
}
