<?php

namespace Tests\Browser;

use App\Models\City;
use App\Models\Department;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_unlogged_user_options_when_an_unlogged_user_clicks_the_account_icon()
    {
        $this->createCategory();

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertGuest()
                ->click('@user-btn')
                ->waitForText('Iniciar sesión')
                ->waitForText('Registrarse')
                ->assertDontSee('Administrar cuenta')
                ->assertDontSee('Perfil')
                ->assertDontSee('Finalizar sesión')
                ->screenshot('login/show-unlogged-user-options');
        });
    }

    /** @test */
    public function it_shows_authenticated_user_options_when_a_logged_user_clicks_the_account_icon()
    {
        $this->createCategory();

        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/')
                ->click('@user-btn')
                ->waitForText('Administrar cuenta')
                ->waitForText('Perfil')
                ->waitForText('Finalizar sesión')
                ->assertDontSee('Iniciar sesión')
                ->assertDontSee('Registrarse')
                ->screenshot('login/show-logged-user-options');
        });
    }

    /** @test */
    public function unlogged_users_can_not_access_to_routes_that_require_authentication()
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

        $this->browse(function (Browser $browser) use ($user, $product, $category, $deparment, $city) {
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
                ->logout();

            $order = Order::first();

            $browser->assertGuest()
                ->visitRoute('orders.index')
                ->assertRouteIs('login')
                ->visitRoute('orders.create')
                ->assertRouteIs('login')
                ->visitRoute('orders.show', $order)
                ->assertRouteIs('login')
                ->visitRoute('orders.payment', $order)
                ->assertRouteIs('login')

                /* Admin routes */
                ->visitRoute('admin.index')
                ->assertRouteIs('login')
                ->visitRoute('admin.products.create')
                ->assertRouteIs('login')
                ->visitRoute('admin.products.edit', $product)
                ->assertRouteIs('login')
                ->visitRoute('admin.categories.index')
                ->assertRouteIs('login')
                ->visitRoute('admin.categories.show', $category)
                ->assertRouteIs('login')
                ->visitRoute('admin.brands.index')
                ->assertRouteIs('login')
                ->visitRoute('admin.orders.index')
                ->assertRouteIs('login')
                ->visitRoute('admin.orders.show', $order)
                ->assertRouteIs('login')
                ->visitRoute('admin.departments.index')
                ->assertRouteIs('login')
                ->visitRoute('admin.departments.show', $deparment)
                ->assertRouteIs('login')
                ->visitRoute('admin.cities.show', $city)
                ->assertRouteIs('login')
                ->visitRoute('admin.users.index')
                ->assertRouteIs('login')

                ->screenshot('login/unlogged-users-cant-access-routes-that-require-login');
        });
    }

    /** @test */
    public function users_without_role_can_access_to_routes_that_do_not_require_a_specific_role()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        $this->assertSame(0, $user->roles->count());

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
                ->pause(1000)
                ->logout();

            $order = Order::first();

            $browser->loginAs($user)
                ->visitRoute('orders.index')
                ->assertRouteIs('orders.index')
                ->visitRoute('orders.create')
                ->assertRouteIs('orders.create')
                ->visitRoute('orders.show', $order)
                ->assertRouteIs('orders.show', $order)
                ->visitRoute('orders.payment', $order)
                ->assertRouteIs('orders.payment', $order)
                ->screenshot('login/users-without-role-can-access-routes-dont-require-specific-role');
        });
    }

    /** @test */
    public function users_with_admin_role_can_access_to_routes_that_require_this_role()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $product = $this->createProduct($subcategory->id, $brand->id);

        $user = User::factory()->create();

        Role::create(['name' => 'admin']);
        $user->assignRole('admin');

        $this->assertSame(1, $user->roles->count());

        $deparment = Department::factory()->create();

        $city = City::factory()->create([
            'department_id' => $deparment->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $product, $category, $deparment, $city) {
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
                ->logout();

            $order = Order::first();

            $browser->loginAs($user)
                ->visitRoute('admin.index')
                ->assertRouteIs('admin.index')
                ->visitRoute('admin.products.create')
                ->assertRouteIs('admin.products.create')
                ->visitRoute('admin.products.edit', $product)
                ->assertRouteIs('admin.products.edit', $product)
                ->visitRoute('admin.categories.index')
                ->assertRouteIs('admin.categories.index')
                ->visitRoute('admin.categories.show', $category)
                ->assertRouteIs('admin.categories.show', $category)
                ->visitRoute('admin.brands.index')
                ->assertRouteIs('admin.brands.index')
                ->visitRoute('admin.orders.index')
                ->assertRouteIs('admin.orders.index')
                ->visitRoute('admin.orders.show', $order)
                ->assertRouteIs('admin.orders.show', $order)
                ->visitRoute('admin.departments.index')
                ->assertRouteIs('admin.departments.index')
                ->visitRoute('admin.departments.show', $deparment)
                ->assertRouteIs('admin.departments.show', $deparment)
                ->visitRoute('admin.cities.show', $city)
                ->assertRouteIs('admin.cities.show', $city)
                ->visitRoute('admin.users.index')
                ->assertRouteIs('admin.users.index')

                ->screenshot('login/admins_can_access_to_routes_that_require_this_role');
        });
    }

}
