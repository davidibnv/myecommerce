<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WelcomeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function the_welcome_view_shows_at_least_five_products()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);
        $productD = $this->createProduct($subcategory->id, $brand->id);
        $productE = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC, $productD, $productE) {
            $browser->visit('/')
                ->assertSee(Str::limit($productA->name, 20))
                ->assertSee(Str::limit($productB->name, 20))
                ->assertSee(Str::limit($productC->name, 20))
                ->assertSee(Str::limit($productD->name, 20))
                ->assertSee(Str::limit($productE->name, 20))
                ->screenshot('welcome/show-five-products');
        });
    }

    /** @test */
    public function the_welcome_view_only_shows_published_products()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);
        $productD = $this->createProduct($subcategory->id, $brand->id);
        $productE = $this->createProduct($subcategory->id, $brand->id);

        $unpublishedProductA = $this->createProduct($subcategory->id, $brand->id, Product::BORRADOR);
        $unpublishedProductB = $this->createProduct($subcategory->id, $brand->id, Product::BORRADOR);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC, $productD, $productE, $unpublishedProductA, $unpublishedProductB) {
            $browser->visit('/')
                ->waitForText(Str::limit($productA->name, 20))
                ->assertSee(Str::limit($productB->name, 20))
                ->assertSee(Str::limit($productC->name, 20))
                ->assertSee(Str::limit($productD->name, 20))
                ->assertSee(Str::limit($productE->name, 20))
                ->assertDontSee(Str::limit($unpublishedProductA->name, 20))
                ->assertDontSee(Str::limit($unpublishedProductB->name, 20))
                ->screenshot('welcome/only-show-published-products');
        });
    }

    /** @test */
    public function user_can_access_to_his_orders_using_the_dropdown()
    {
        $this->createCategory();

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/')
                ->click('@user-btn')
                ->click('@my-orders')
                ->assertRouteIs('orders.index')
                ->screenshot('welcome/user-can-access-to-his-orders');
        });
    }
}
