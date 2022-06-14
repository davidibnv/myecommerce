<?php

namespace Tests\Browser\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;
use Tests\DuskTestCase;

class SearchTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test  */
    public function the_search_input_filters_by_name_when_the_user_searchs_for_a_product()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);

        Role::create(['name' => 'admin']);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->browse(function (Browser $browser) use ($user, $productA, $productB, $productC) {
            $browser->loginAs($user)
                ->visitRoute('admin.index')
                ->assertSee($productA->name)
                ->assertSee($productB->name)
                ->assertSee($productC->name)
                ->type('@search', $productA->name)
                ->waitForText($productA->name)
                ->waitUntilMissingText($productB->name)
                ->waitUntilMissingText($productC->name)
                ->screenshot('admin/search/search-shows-results-if-exist');
        });
    }

    /** @test  */
    public function the_search_input_show_a_message_when_do_not_exists_results()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);

        Role::create(['name' => 'admin']);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->browse(function (Browser $browser) use ($user, $productA, $productB, $productC) {
            $browser->loginAs($user)
                ->visitRoute('admin.index')
                ->assertSee($productA->name)
                ->assertSee($productB->name)
                ->assertSee($productC->name)
                ->type('@search', 'invalid-name')
                ->waitUntilMissingText($productA->name)
                ->waitUntilMissingText($productB->name)
                ->waitUntilMissingText($productC->name)
                ->waitForText('No existen productos coincidentes')
                ->screenshot('admin/search/search-shows-a-message-when-no-results');
        });
    }
}
