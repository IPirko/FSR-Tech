<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//welcome
Route::get('login', function () {
    return redirect(route('home'));
})->name('welcome');
Route::get('home', function () {
    if (!empty(Auth::user())) {
        switch (Auth::user()->type()) {
      case 'cso':
          return redirect(route('cso.home'));
        break;
      case 'donor':
          return redirect(route('donor.home'));
        break;
      case 'admin':
          return redirect(route('admin.home'));
        break;

      default:
        return redirect(route('login'));
        break;
  }
    } else {
        return redirect(route('login'));
    }
})->name('home');

// Authentication Routes...
Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/', 'Auth\LoginController@login');
Route::get('logout', function () {
    return redirect(route('home'));
})->name('logout');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
  //get organizations with ajax
Route::post('register/organizations', 'Auth\RegisterController@getOrganizations')->name('register.organizations');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

//admin routes
Route::get('admin', function () {
    if (Auth::user()) {
        if (Auth::user()->type() == "admin") {
            return redirect(route('admin.home'));
        }
    }
    return redirect(route('admin.login'));
})->name('admin');
Route::get('admin/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
Route::post('admin/login', 'Auth\AdminLoginController@login')->name('admin.login');
Route::get('admin/logout', function () {
    return redirect(route('home'));
})->name('admin/logout');
Route::post('admin/logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
Route::get('admin/home', 'Admin\HomeController@index')->name('admin.home');

Route::get('admin/users/approve', 'Admin\ApproveUsersController@index')->name('admin.approve_users');
Route::post('admin/users/approve', 'Admin\ApproveUsersController@handle_post')->name('admin.approve_users');
Route::get('admin/users/cso', 'Admin\CsoUsersController@index')->name('admin.cso_users');
Route::post('admin/users/cso', 'Admin\CsoUsersController@handle_post')->name('admin.cso_users');
Route::get('admin/users/donor', 'Admin\DonorUsersController@index')->name('admin.donor_users');
Route::post('admin/users/donor', 'Admin\DonorUsersController@handle_post')->name('admin.donor_users');

Route::get('admin/organizations/new', 'Admin\NewOrganizationController@index')->name('admin.new_organization');
Route::post('admin/organizations/new', 'Admin\NewOrganizationController@handle_post')->name('admin.new_organization');
Route::get('admin/organizations/cso', 'Admin\CsoOrganizationsController@index')->name('admin.cso_organizations');
Route::get('admin/organizations/donor', 'Admin\DonorOrganizationsController@index')->name('admin.donor_organizations');

Route::get('admin/food_types', 'Admin\FoodTypesController@index')->name('admin.food_types');
Route::get('admin/food_types/new', 'Admin\NewFoodTypeController@index')->name('admin.new_food_type');
Route::post('admin/food_types/new', 'Admin\NewFoodTypeController@handle_post')->name('admin.new_food_type');

Route::get('admin/products', 'Admin\ProductsController@index')->name('admin.products');
Route::post('admin/products', 'Admin\ProductsController@handle_post')->name('admin.products');
Route::get('admin/products/new', 'Admin\NewProductController@index')->name('admin.new_product');
Route::post('admin/products/new', 'Admin\NewProductController@handle_post')->name('admin.new_product');

Route::get('admin/quantity_types', 'Admin\QuantityTypesController@index')->name('admin.quantity_types');
Route::get('admin/quantity_types/new', 'Admin\NewQuantityTypeController@index')->name('admin.new_quantity_type');
Route::post('admin/quantity_types/new', 'Admin\NewQuantityTypeController@handle_post')->name('admin.new_quantity_type');

Route::get('admin/volunteers', 'Admin\VolunteersController@index')->name('admin.volunteers');
Route::post('admin/volunteers', 'Admin\VolunteersController@handle_post')->name('admin.volunteers');
Route::get('admin/volunteers/new', 'Admin\NewVolunteerController@index')->name('admin.new_volunteer');
Route::post('admin/volunteers/new', 'Admin\NewVolunteerController@handle_post')->name('admin.new_volunteer');

Route::get('admin/locations', 'Admin\LocationsController@index')->name('admin.locations');
Route::get('admin/locations/new', 'Admin\NewLocationController@index')->name('admin.new_location');
Route::post('admin/locations/new', 'Admin\NewLocationController@handle_post')->name('admin.new_location');

//donor routes
Route::get('donor/home', 'Donor\HomeController@index')->name('donor.home');
Route::get('donor/profile', 'Donor\ProfileController@index')->name('donor.profile');
Route::get('donor/edit_profile', 'Donor\ProfileController@edit_profile')->name('donor.edit_profile');
Route::post('donor/edit_profile', 'Donor\ProfileController@handle_post');

Route::get('donor/new_listing', 'Donor\NewListingController@index')->name('donor.new_listing');
Route::post('donor/new_listing', 'Donor\NewListingController@handle_post');
  //get products with ajax
Route::post('donor/new_listing/products', 'Donor\NewListingController@getProducts')->name('donor.new_listing.products');
Route::post('donor/new_listing/quantity_types', 'Donor\NewListingController@getQuantityTypes')->name('donor.new_listing.quantity_types');
Route::get('donor/my_active_listings', 'Donor\MyActiveListingsController@index')->name('donor.my_active_listings');
Route::post('donor/my_active_listings', 'Donor\MyActiveListingsController@handle_post');

Route::get('donor/my_accepted_listings/{listing_offer_id}', 'Donor\MyAcceptedListingsController@single_listing_offer')->name('donor.single_listing_offer');
Route::post('donor/my_accepted_listings/{listing_offer_id}', 'Donor\MyAcceptedListingsController@single_listing_offer_post')->name('donor.single_listing_offer');



//cso routes
Route::get('cso/home', 'Cso\HomeController@index')->name('cso.home');
Route::get('cso/profile', 'Cso\ProfileController@index')->name('cso.profile');
Route::get('cso/edit_profile', 'Cso\ProfileController@edit_profile')->name('cso.edit_profile');
Route::post('cso/edit_profile', 'Cso\ProfileController@handle_post');

Route::get('cso/volunteers', 'Cso\VolunteersController@index')->name('cso.volunteers');
Route::post('cso/volunteers', 'Cso\VolunteersController@handle_post')->name('cso.volunteers');
Route::get('cso/volunteers/new', 'Cso\VolunteersController@new_volunteer')->name('cso.new_volunteer');
Route::post('cso/volunteers/new', 'Cso\VolunteersController@new_volunteer_post')->name('cso.new_volunteer');
Route::get('cso/volunteers/{volunteer_id}', 'Cso\VolunteersController@edit_volunteer')->name('cso.edit_volunteer');
Route::post('cso/volunteers/{volunteer_id}', 'Cso\VolunteersController@edit_volunteer_post')->name('cso.edit_volunteer');

Route::get('cso/active_listings', 'Cso\ActiveListingsController@index')->name('cso.active_listings');
Route::post('cso/active_listings', 'Cso\ActiveListingsController@handle_post');
Route::post('cso/active_listings/add_volunteer', 'Cso\ActiveListingsController@add_volunteer')->name('cso.active_listings.add_volunteer');
Route::post('cso/active_listings/get_volunteers', 'Cso\ActiveListingsController@get_volunteers')->name('cso.active_listings.get_volunteers');
Route::post('cso/active_listings/get_volunteer', 'Cso\ActiveListingsController@get_volunteer')->name('cso.active_listings.get_volunteer');

Route::get('cso/accepted_listings', 'Cso\AcceptedListingsController@index')->name('cso.accepted_listings');
Route::post('cso/accepted_listings', 'Cso\AcceptedListingsController@handle_post');
Route::post('cso/accepted_listings/update_volunteer', 'Cso\AcceptedListingsController@update_volunteer')->name('cso.accepted_listings.update_volunteer');
Route::get('cso/accepted_listings/{listing_offer_id}', 'Cso\AcceptedListingsController@single_accepted_listing')->name('cso.accepted_listings.single_accepted_listing');
Route::post('cso/accepted_listings/{listing_offer_id}', 'Cso\AcceptedListingsController@single_accepted_listing_post')->name('cso.accepted_listings.single_accepted_listing');
