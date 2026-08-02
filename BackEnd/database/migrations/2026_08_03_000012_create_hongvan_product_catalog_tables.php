<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_product_categories', function (Blueprint $table): void {
            $table->comment('Hierarchical fertilizer product categories shared by localized catalog content');
            $table->id()->comment('Internal primary key of the product category');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the category in APIs');
            $table->foreignId('parent_id')->nullable()->comment('Optional parent category in the catalog hierarchy')->constrained('hongvan_product_categories', 'id', 'hv_pc_parent_fk')->nullOnDelete();
            $table->string('code', 64)->unique()->comment('Stable administrator-defined category code');
            $table->boolean('is_active')->default(true)->index()->comment('Whether administrators may publish and select this category');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the category is eligible for featured catalog placements');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending display order among sibling categories');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the category')->constrained('hongvan_users', 'id', 'hv_pc_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the category')->constrained('hongvan_users', 'id', 'hv_pc_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the category to trash')->constrained('hongvan_users', 'id', 'hv_pc_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the category was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the category was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the category was most recently updated');
            $table->index(['parent_id', 'is_active', 'sort_order'], 'hongvan_product_categories_tree_index');
        });

        Schema::create('hongvan_product_category_translations', function (Blueprint $table): void {
            $table->comment('Localized names, slugs and search metadata for fertilizer product categories');
            $table->id()->comment('Internal primary key of the category translation');
            $table->foreignId('product_category_id')->comment('Category that owns this localized content')->constrained('hongvan_product_categories', 'id', 'hv_pct_category_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public category name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the product category namespace');
            $table->text('summary')->nullable()->comment('Optional localized short category introduction');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['product_category_id', 'locale'], 'hongvan_product_category_translations_locale_unique');
            $table->unique(['locale', 'slug'], 'hongvan_product_category_translations_slug_unique');
        });

        Schema::create('hongvan_brands', function (Blueprint $table): void {
            $table->comment('Fertilizer brands referenced by products without storing commerce or inventory data');
            $table->id()->comment('Internal primary key of the brand');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the brand in APIs');
            $table->string('code', 64)->unique()->comment('Stable administrator-defined brand code');
            $table->foreignId('logo_media_id')->nullable()->comment('Optional media library logo assigned to the brand')->constrained('hongvan_media', 'id', 'hv_brands_logo_media_fk')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index()->comment('Whether the brand is available for catalog assignment');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending brand display order');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the brand')->constrained('hongvan_users', 'id', 'hv_brands_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the brand')->constrained('hongvan_users', 'id', 'hv_brands_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the brand to trash')->constrained('hongvan_users', 'id', 'hv_brands_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the brand was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the brand was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the brand was most recently updated');
        });

        Schema::create('hongvan_brand_translations', function (Blueprint $table): void {
            $table->comment('Localized names, slugs and descriptions for fertilizer brands');
            $table->id()->comment('Internal primary key of the brand translation');
            $table->foreignId('brand_id')->comment('Brand that owns this localized content')->constrained('hongvan_brands', 'id', 'hv_bt_brand_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public brand name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the brand namespace');
            $table->text('description')->nullable()->comment('Optional localized public brand description');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['brand_id', 'locale'], 'hongvan_brand_translations_locale_unique');
            $table->unique(['locale', 'slug'], 'hongvan_brand_translations_slug_unique');
        });

        Schema::create('hongvan_products', function (Blueprint $table): void {
            $table->comment('Fertilizer catalog products used for presentation and quote requests without commerce processing');
            $table->id()->comment('Internal primary key of the product');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the product in APIs');
            $table->string('sku', 100)->unique()->comment('Unique stock keeping reference used only as a catalog identifier');
            $table->string('code', 100)->nullable()->unique()->comment('Optional business-facing product code distinct from the internal key');
            $table->string('status', 24)->default('draft')->index()->comment('Editorial status: draft, published, archived, or scheduled');
            $table->foreignId('product_category_id')->nullable()->comment('Primary catalog category assigned to the product')->constrained('hongvan_product_categories', 'id', 'hv_products_category_fk')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->comment('Optional fertilizer brand assigned to the product')->constrained('hongvan_brands', 'id', 'hv_products_brand_fk')->nullOnDelete();
            $table->string('origin')->nullable()->comment('Optional non-localized country or region of product origin');
            $table->string('packaging')->nullable()->comment('Optional normalized packaging descriptor such as bag or bottle size');
            $table->boolean('is_featured')->default(false)->index()->comment('Whether the product is eligible for featured catalog placements');
            $table->string('price_mode', 16)->default('contact')->index()->comment('Pricing display mode: fixed, from, range, market, dealer, quantity, or contact');
            $table->decimal('price_amount', 19, 4)->nullable()->comment('Positive fixed public amount when price mode is fixed');
            $table->decimal('price_min', 19, 4)->nullable()->comment('Positive lower amount used by from and range price modes');
            $table->decimal('price_max', 19, 4)->nullable()->comment('Positive upper amount used by range price mode');
            $table->char('currency', 3)->default('VND')->comment('ISO 4217 currency code used by numeric catalog prices');
            $table->string('price_unit', 100)->nullable()->comment('Optional display unit such as kg, tonne, bottle, or 50 kg bag');
            $table->text('price_note')->nullable()->comment('Optional administrator-provided pricing note that does not bypass validation');
            $table->boolean('is_price_visible')->default(true)->index()->comment('Whether a valid public price may be displayed instead of quote contact');
            $table->timestamp('published_at')->nullable()->index()->comment('UTC time when the product becomes publicly visible');
            $table->timestamp('unpublished_at')->nullable()->index()->comment('Optional UTC time when public visibility ends');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the product')->constrained('hongvan_users', 'id', 'hv_products_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the product')->constrained('hongvan_users', 'id', 'hv_products_updated_by_fk')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('Administrator who moved the product to trash')->constrained('hongvan_users', 'id', 'hv_products_deleted_by_fk')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable()->index()->comment('UTC time when the product was moved to trash');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the product was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the product was most recently updated');
            $table->index(['status', 'published_at', 'unpublished_at'], 'hongvan_products_publication_index');
            $table->index(['product_category_id', 'brand_id', 'is_featured'], 'hongvan_products_catalog_index');
        });

        Schema::create('hongvan_product_translations', function (Blueprint $table): void {
            $table->comment('Localized product names, slugs, descriptions, usage guidance and SEO preparation fields');
            $table->id()->comment('Internal primary key of the product translation');
            $table->foreignId('product_id')->comment('Product that owns this localized content')->constrained('hongvan_products', 'id', 'hv_pt_product_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of this translation');
            $table->string('name')->comment('Localized public product name');
            $table->string('slug', 191)->comment('Localized URL slug unique inside the product namespace');
            $table->text('short_description')->nullable()->comment('Optional localized summary used by product cards and search results');
            $table->longText('description')->nullable()->comment('Optional sanitized localized product description');
            $table->longText('benefits')->nullable()->comment('Optional sanitized localized product benefits and applications');
            $table->longText('usage_instructions')->nullable()->comment('Optional sanitized localized product usage instructions');
            $table->string('meta_title')->nullable()->comment('Optional localized SEO title prepared for the SEO module');
            $table->text('meta_description')->nullable()->comment('Optional localized SEO description prepared for the SEO module');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the translation was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the translation was most recently updated');
            $table->unique(['product_id', 'locale'], 'hongvan_product_translations_locale_unique');
            $table->unique(['locale', 'slug'], 'hongvan_product_translations_slug_unique');
        });

        Schema::create('hongvan_product_media', function (Blueprint $table): void {
            $table->comment('Ordered media assignments for product galleries, documents and primary images');
            $table->id()->comment('Internal primary key of the product media assignment');
            $table->foreignId('product_id')->comment('Product that uses the assigned media')->constrained('hongvan_products', 'id', 'hv_pm_product_fk')->cascadeOnDelete();
            $table->foreignId('media_id')->comment('Media library record assigned to the product')->constrained('hongvan_media', 'id', 'hv_pm_media_fk')->restrictOnDelete();
            $table->string('role', 32)->default('gallery')->comment('Allowlisted product slot such as primary, gallery, document, or certificate');
            $table->string('locale', 10)->default('*')->comment('Locale scope of the assignment or asterisk when shared across languages');
            $table->boolean('is_primary')->default(false)->index()->comment('Whether this assignment is the primary media for its locale and role');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending media display order inside the product gallery');
            $table->string('alt_text')->nullable()->comment('Optional product-specific accessible text overriding media metadata');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the product-media assignment was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the product-media assignment was most recently updated');
            $table->unique(['product_id', 'media_id', 'role', 'locale'], 'hongvan_product_media_assignment_unique');
            $table->index(['product_id', 'role', 'locale', 'sort_order'], 'hongvan_product_media_display_index');
        });

        Schema::create('hongvan_product_tags', function (Blueprint $table): void {
            $table->comment('Reusable administrator-managed tags for grouping and filtering fertilizer products');
            $table->id()->comment('Internal primary key of the product tag');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the product tag in APIs');
            $table->string('name')->comment('Administrator-facing product tag name');
            $table->string('slug', 191)->unique()->comment('Unique normalized product tag identifier');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the product tag')->constrained('hongvan_users', 'id', 'hv_tags_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the product tag')->constrained('hongvan_users', 'id', 'hv_tags_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the product tag was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the product tag was most recently updated');
        });

        Schema::create('hongvan_product_tag_links', function (Blueprint $table): void {
            $table->comment('Pivot assignments connecting fertilizer products to reusable product tags');
            $table->foreignId('product_id')->comment('Product assigned to the tag')->constrained('hongvan_products', 'id', 'hv_ptl_product_fk')->cascadeOnDelete();
            $table->foreignId('product_tag_id')->comment('Tag assigned to the product')->constrained('hongvan_product_tags', 'id', 'hv_ptl_tag_fk')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the product-tag assignment was created');
            $table->primary(['product_id', 'product_tag_id']);
        });

        Schema::create('hongvan_product_attribute_definitions', function (Blueprint $table): void {
            $table->comment('Typed reusable attribute definitions used to validate product filter and specification values');
            $table->id()->comment('Internal primary key of the product attribute definition');
            $table->char('public_id', 26)->unique()->comment('Public ULID used to identify the attribute definition in APIs');
            $table->string('code', 100)->unique()->comment('Stable machine-readable product attribute code');
            $table->string('name')->comment('Administrator-facing attribute name');
            $table->string('data_type', 24)->comment('Allowlisted value type: text, decimal, boolean, option, or json');
            $table->string('unit', 64)->nullable()->comment('Optional normalized unit applied to decimal or text values');
            $table->json('options')->nullable()->comment('Allowlisted option values for option attributes without executable content');
            $table->boolean('is_filterable')->default(false)->index()->comment('Whether the attribute may be exposed through catalog filters');
            $table->boolean('is_required')->default(false)->comment('Whether administrators must provide a value for applicable products');
            $table->unsignedSmallInteger('sort_order')->default(0)->index()->comment('Ascending attribute display order');
            $table->foreignId('created_by')->nullable()->comment('Administrator who created the attribute definition')->constrained('hongvan_users', 'id', 'hv_pad_created_by_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('Administrator who most recently changed the attribute definition')->constrained('hongvan_users', 'id', 'hv_pad_updated_by_fk')->nullOnDelete();
            $table->timestamp('created_at')->nullable()->comment('UTC time when the attribute definition was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the attribute definition was most recently updated');
        });

        Schema::create('hongvan_product_attribute_values', function (Blueprint $table): void {
            $table->comment('Typed values assigned to products according to reusable attribute definitions');
            $table->id()->comment('Internal primary key of the product attribute value');
            $table->foreignId('product_id')->comment('Product that owns this attribute value')->constrained('hongvan_products', 'id', 'hv_pav_product_fk')->cascadeOnDelete();
            $table->foreignId('attribute_definition_id')->comment('Attribute definition that controls this value type')->constrained('hongvan_product_attribute_definitions', 'id', 'hv_pav_definition_fk')->cascadeOnDelete();
            $table->string('locale', 10)->default('*')->comment('Locale of textual content or asterisk for language-neutral values');
            $table->text('value_text')->nullable()->comment('Text or option value when permitted by the definition data type');
            $table->decimal('value_decimal', 19, 4)->nullable()->comment('Exact decimal value when permitted by the definition data type');
            $table->boolean('value_boolean')->nullable()->comment('Boolean value when permitted by the definition data type');
            $table->json('value_json')->nullable()->comment('Allowlisted structured value without executable code');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the attribute value was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the attribute value was most recently updated');
            $table->unique(['product_id', 'attribute_definition_id', 'locale'], 'hongvan_product_attribute_values_unique');
        });

        Schema::create('hongvan_product_specifications', function (Blueprint $table): void {
            $table->comment('Localized ordered free-form specifications that do not require reusable filter definitions');
            $table->id()->comment('Internal primary key of the product specification');
            $table->foreignId('product_id')->comment('Product that owns this specification')->constrained('hongvan_products', 'id', 'hv_ps_product_fk')->cascadeOnDelete();
            $table->string('locale', 10)->comment('BCP 47 compatible locale code of the specification');
            $table->string('label')->comment('Localized public specification label');
            $table->text('value')->comment('Localized public specification value stored as plain text');
            $table->string('unit', 64)->nullable()->comment('Optional normalized display unit for the specification');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending specification display order');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the specification was created');
            $table->timestamp('updated_at')->nullable()->comment('UTC time when the specification was most recently updated');
            $table->index(['product_id', 'locale', 'sort_order'], 'hongvan_product_specifications_display_index');
        });

        Schema::create('hongvan_product_related', function (Blueprint $table): void {
            $table->comment('Directed curated relationships between fertilizer products for catalog discovery');
            $table->foreignId('product_id')->comment('Source product that owns the related-products list')->constrained('hongvan_products', 'id', 'hv_pr_product_fk')->cascadeOnDelete();
            $table->foreignId('related_product_id')->comment('Target product shown as related to the source product')->constrained('hongvan_products', 'id', 'hv_pr_related_product_fk')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Ascending related-product display order');
            $table->timestamp('created_at')->nullable()->comment('UTC time when the product relationship was created');
            $table->primary(['product_id', 'related_product_id']);
            $table->index(['product_id', 'sort_order'], 'hongvan_product_related_display_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_product_related');
        Schema::dropIfExists('hongvan_product_specifications');
        Schema::dropIfExists('hongvan_product_attribute_values');
        Schema::dropIfExists('hongvan_product_attribute_definitions');
        Schema::dropIfExists('hongvan_product_tag_links');
        Schema::dropIfExists('hongvan_product_tags');
        Schema::dropIfExists('hongvan_product_media');
        Schema::dropIfExists('hongvan_product_translations');
        Schema::dropIfExists('hongvan_products');
        Schema::dropIfExists('hongvan_brand_translations');
        Schema::dropIfExists('hongvan_brands');
        Schema::dropIfExists('hongvan_product_category_translations');
        Schema::dropIfExists('hongvan_product_categories');
    }
};
