<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->char('first_name', 255)->nullable();
            $table->char('last_name', 255)->nullable();
            $table->char('mobile', 20)->nullable();
            $table->char('country_code', 5)->nullable();
            $table->char('dob', 20)->nullable();
            $table->char('address', 255)->nullable();
            $table->char('city', 50)->nullable();
            $table->char('state', 20)->nullable();
            $table->char('country', 20)->nullable();
            $table->char('postal_code', 20)->nullable();
            $table->char('tax_id', 50)->nullable();
            $table->char('tax_id_type', 50)->nullable();
            $table->char('funding_source', 50)->nullable();
            $table->char('employment_status', 20)->nullable();
            $table->char('employer_name', 50)->nullable();
            $table->char('occupation', 255)->nullable();
            $table->boolean('public_shareholder')->default(false);
            $table->boolean('is_affiliated_exchange_or_finra')->default(false);
            $table->boolean('is_politically_exposed')->default(false);
            $table->boolean('is_immediate_family_exposed')->default(false);
            $table->char('shareholder_company_name', 255)->nullable();
            $table->char('shareholder_company_address', 255)->nullable();
            $table->char('shareholder_company_city', 255)->nullable();
            $table->char('shareholder_company_state', 255)->nullable();
            $table->char('shareholder_company_country', 255)->nullable();
            $table->char('shareholder_company_email', 255)->nullable();
            $table->char('account_id', 255)->nullable();
            $table->char('account_number', 255)->nullable();
            $table->char('account_type', 50)->nullable();
            $table->char('account_currency', 50)->nullable();
            $table->char('account_status', 50)->nullable();
            $table->char('watchlist_id', 255)->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('verified')->default(false);
            $table->char('last_login', 50)->nullable();
            $table->char('profile_completion', 50)->nullable();
            $table->char('avatar', 255)->nullable();
            $table->char('ip_address', 50)->nullable();
            $table->boolean('bank_linked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
