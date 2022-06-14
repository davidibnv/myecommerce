<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CategoryTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_the_categories_when_clicking_the_categories_button()
    {
        $firstCategory = $this->createCategory();

        $secondCategory = $this->createCategory();

        $this->browse(function (Browser $browser) use ($firstCategory, $secondCategory) {
            $browser->visit('/')
                ->click('@categories-button')
                ->waitForText($firstCategory->name)
                ->assertSee($secondCategory->name)
                ->screenshot('category/show-categories');
        });
    }

    /** @test */
    public function it_shows_the_subcategories_of_a_category_when_hovering_it()
    {
        $firstCategory = $this->createCategory();
        $secondCategory = $this->createCategory();

        $firstSubcategory = $this->createSubcategory($firstCategory->id);
        $secondSubcategory = $this->createSubcategory($secondCategory->id);

        $this->browse(function (Browser $browser) use ($firstCategory, $firstSubcategory, $secondSubcategory) {
            $browser->visit('/')
                ->click('@categories-button')
                ->mouseover('@category-' . $firstCategory->id)
                ->waitForText($firstSubcategory->name)
                ->assertDontSee($secondSubcategory->name)
                ->screenshot('category/show-subcategories');
        });
    }

    /** @test */
    public function it_shows_the_detailed_view_of_a_category()
    {
        $categoryA = $this->createCategory();

        $subcategoryA = $this->createSubcategory($categoryA->id);
        $subcategoryB = $this->createSubcategory($categoryA->id);

        $brandA = $this->createBrand($categoryA->id);
        $brandB = $this->createBrand($categoryA->id);

        $productA = $this->createProduct($subcategoryA->id, $brandA->id);
        $productB = $this->createProduct($subcategoryB->id, $brandB->id);

        $categoryB = $this->createCategory();

        $subcategoryC = $this->createSubcategory($categoryB->id);
        $subcategoryD = $this->createSubcategory($categoryB->id);

        $brandC = $this->createBrand($categoryB->id);
        $brandD = $this->createBrand($categoryB->id);

        $productC = $this->createProduct($subcategoryC->id, $brandC->id);
        $productD = $this->createProduct($subcategoryD->id, $brandD->id);

        $this->browse(function (Browser $browser) use ($categoryA, $subcategoryA, $subcategoryB, $brandA, $brandB, $productA, $productB, $subcategoryC, $subcategoryD, $brandC, $brandD, $productC, $productD) {
            $browser->visit('/')
                ->click('@show-category-' . $categoryA->id)
                ->assertSee(Str::title($subcategoryA->name))
                ->assertSee(Str::title($subcategoryB->name))
                ->assertSee(Str::title($brandA->name))
                ->assertSee(Str::title($brandB->name))
                ->assertSee(Str::limit($productA->name, 20))
                ->assertSee(Str::limit($productB->name, 20))
                ->assertDontSee(Str::title($subcategoryC->name))
                ->assertDontSee(Str::title($subcategoryD->name))
                ->assertDontSee(Str::title($brandC->name))
                ->assertDontSee(Str::title($brandD->name))
                ->assertDontSee(Str::limit($productC->name, 20))
                ->assertDontSee(Str::limit($productD->name, 20))
                ->screenshot('category/show-detailed-category-view');
        });
    }

}
