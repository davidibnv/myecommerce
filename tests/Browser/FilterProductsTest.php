<?php

namespace Tests\Browser;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FilterProductsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_filters_products_by_subcategory_in_the_show_category_details_view()
    {
        $category = $this->createCategory();

        $subcategoryA = $this->createSubcategory($category->id);
        $subcategoryB = $this->createSubcategory($category->id);

        $brandA = $this->createBrand($category->id);
        $brandB = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategoryA->id, $brandA->id);
        $productB = $this->createProduct($subcategoryA->id, $brandB->id);
        $productC = $this->createProduct($subcategoryB->id, $brandA->id);
        $productD = $this->createProduct($subcategoryB->id, $brandB->id);

        $this->browse(function (Browser $browser) use ($category, $subcategoryA, $productA, $productB, $productC, $productD) {
            $browser->visit('/')
                ->click('@show-category-' . $category->id)
                ->click('@filter-subcategory-' . $subcategoryA->id)
                ->assertSee(Str::limit($productA->name, 20))
                ->assertSee(Str::limit($productB->name, 20))
                ->assertDontSee(Str::limit($productC->name, 20))
                ->assertDontSee(Str::limit($productD->name, 20))
                ->screenshot('filter-products/filter-products-by-subcategory');
        });
    }

    /** @test */
    public function it_filters_products_by_brand_in_the_show_category_details_view()
    {
        $category = $this->createCategory();

        $subcategoryA = $this->createSubcategory($category->id);
        $subcategoryB = $this->createSubcategory($category->id);

        $brandA = $this->createBrand($category->id);
        $brandB = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategoryA->id, $brandA->id);
        $productB = $this->createProduct($subcategoryB->id, $brandA->id);
        $productC = $this->createProduct($subcategoryA->id, $brandB->id);
        $productD = $this->createProduct($subcategoryB->id, $brandB->id);

        $this->browse(function (Browser $browser) use ($category, $brandA, $productA, $productB, $productC, $productD) {
            $browser->visit('/')
                ->click('@show-category-' . $category->id)
                ->click('@filter-brand-' . $brandA->id)
                ->assertSee(Str::limit($productA->name, 20))
                ->assertSee(Str::limit($productB->name, 20))
                ->assertDontSee(Str::limit($productC->name, 20))
                ->assertDontSee(Str::limit($productD->name, 20))
                ->screenshot('filter-products/filter-products-by-brand');
        });
    }

    /** @test */
    public function the_product_search_input_filters_when_user_write_correct_data()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = Product::factory()->create([
            'name' => 'ProductoA',
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
        ]);

        $this->createImage($productA->id, Product::class);

        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);
        $productD = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC, $productD) {
            $browser->visit('/search?name=productoa')
                ->assertSee($productA->name)
                ->assertDontSee($productB->name)
                ->assertDontSee($productC->name)
                ->assertDontSee($productD->name)
                ->screenshot('filter-products/show-the-product-when-search-its-name');
        });
    }

    /** @test */
    public function the_product_search_input_shows_a_message_when_user_doesnt_write_correct_data()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);
        $productD = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC, $productD) {
            $browser->visit('/search?name=invalid-product-name')
                ->assertDontSee($productA->name)
                ->assertDontSee($productB->name)
                ->assertDontSee($productC->name)
                ->assertDontSee($productD->name)
                ->assertSee('Ningún producto coincide con esos parámetros')
                ->screenshot('filter-products/show-a-message-when-search-is-wrong');
        });
    }

    /** @test */
    public function the_product_search_input_show_all_products_when_is_empty()
    {
        $category = $this->createCategory();
        $subcategory = $this->createSubcategory($category->id);
        $brand = $this->createBrand($category->id);

        $productA = $this->createProduct($subcategory->id, $brand->id);
        $productB = $this->createProduct($subcategory->id, $brand->id);
        $productC = $this->createProduct($subcategory->id, $brand->id);
        $productD = $this->createProduct($subcategory->id, $brand->id);

        $this->browse(function (Browser $browser) use ($productA, $productB, $productC, $productD) {
            $browser->visitRoute('search')
                ->assertSee($productA->name)
                ->assertSee($productB->name)
                ->assertSee($productC->name)
                ->assertSee($productD->name)
                ->screenshot('filter-products/show-all-products-when-search-is-empty');
        });
    }
}
