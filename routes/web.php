<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BikeRentController;
// use App\Http\Controllers\DemoController;
use App\Http\Controllers\KhaltiController;
use App\Http\Controllers\MechanicController;
use App\Http\Controllers\MechanicsController;
use App\Models\Bike;
use App\Models\Mechanic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->user_type == 'user') {
            $bikes = Bike::all()->random(4);
            $mechanics = Mechanic::all()->random(4);
            return view('main.home', ['bikes' => $bikes, 'mechanics' => $mechanics]);
        }
        if (Auth::user()->user_type == 'admin') {
            return redirect()->route('admin.home');
        }
        if (Auth::user()->user_type == 'mechanic') {
            $id = Mechanic::where('user_id', '=', Auth::id())->value('id');
            return redirect()->route('mech.requested', ['mechanic' => $id]);
        }
    } else {
        try {
            $bikes = Bike::all()->random(4) ?? null;
            $mechanics = Mechanic::all()->random(4) ?? null;

            return view('main.home', ['bikes' => $bikes, 'mechanics' => $mechanics]);
        } catch (Exception $e) {
            return abort(404, $e->getMessage());
        }
    }
})->name('user.home');

Route::get('/home', function () {
    return redirect('/');
});

// Route::get('/demo', [DemoController::class, 'index']);

Auth::routes();

Route::get('/bike/all', [BikeRentController::class, 'bikeAll'])->name('bike.all');
Route::get('/bike/details/{bike:id}', [BikeRentController::class, 'bikeSingle'])->name('bike.details');
Route::get('/rent-now/{bike:id}', [BikeRentController::class, 'index'])->middleware('auth')->name('bike.rentnow');
Route::post('/rent-now/{bike:id}/bike', [BikeRentController::class, 'rentNow'])->middleware(['auth'])->name('bike.rent');
Route::get('/bike/rent/{bike:id}/done/{user}', [BikeRentController::class, 'rentDone'])->middleware(['auth'])->name('bike.rentDone');
// Route::match(['get', 'post'], '/bike/rent/{bike:id}/done/{user}', [BikeRentController::class, 'rentDone'])->middleware(['auth'])->name('bike.rentDone');


Route::post('/bike/rent-done/{booking:id}', [BikeRentController::class, 'rentUpdate'])->middleware(['auth'])->name('bike.rentUpdate');
// Route::match(['get', 'post'], '/bike/rent-done/{booking:id}', [BikeRentController::class, 'rentUpdate'])->middleware(['auth'])->name('bike.rentUpdate');
Route::post('/khalticonfirm', [KhaltiController::class, 'confirmKhalti'])->middleware(['auth'])->name('confirmKhalti');

Route::get('/bike/rent-done/{booking:id}', [BikeRentController::class, 'showBooking'])->middleware(['auth'])->name('bike.showRent');
Route::get('/download/order/{booking:id}', [BikeRentController::class, 'downloadOrder'])->middleware(['auth'])->name('bike.downloadOrder');
Route::get('/my-bookings/{user:id}', [BikeRentController::class, 'myBookings'])->middleware(['auth'])->name('bookings');
Route::post('/review/bike/{bike:id}', [BikeRentController::class, 'bikeReview'])->middleware(['auth'])->name('bike.review');
Route::get('/search', [BikeRentController::class, 'search'])->name('search');

Route::get('/mechanic/all', [MechanicsController::class, 'mechanicsAll'])->name('mech.all');
Route::get('/mechanic/details/{mechanic:id}', [MechanicsController::class, 'mechanicSingle'])->name('mech.single');
Route::post('/mechanic/book/{mechanic:id}', [MechanicsController::class, 'mechanicAppoint'])->middleware('auth')->name('mech.appoint');
Route::get('/mechanic/checkout/{appointment:id}', [MechanicsController::class, 'mechanicCheckout'])->middleware(['auth'])->name('mech.checkout');
Route::post('/mechanic/checkout/done/{appointment:id}', [MechanicsController::class, 'updateAppointment'])->middleware(['auth'])->name('mech.update');
Route::get('/mechanic/checkout/done/{appointment:id}', [MechanicsController::class, 'showAppointment'])->middleware(['auth'])->name('mech.show');
Route::get('/mechanics/download/{appointment:id}', [MechanicsController::class, 'downloadAppointment'])->middleware(['auth'])->name('mech.download');

Route::get('/admin', function () {
    return redirect()->route('admin.requested');
})->name('admin.home');

// Admin ['auth', 'is_admin'] middleware groups
// Changed by Suman 👍
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'is_admin']], function () {
    Route::get('bikes/requested', [AdminController::class, 'bikesRequested'])->name('admin.requested');
    Route::get('bikes/booking/confirm/{booking:id}', [AdminController::class, 'confirmBooking'])->name('admin.confirm');
    Route::get('bikes/booking/cancel/{booking:id}', [AdminController::class, 'cancelBooking'])->name('admin.cancel');
    Route::get('bikes/confirmed', [AdminController::class, 'getConfirmed'])->name('bikes.confirmed');
    Route::get('bikes/confirmed/undo/{booking:id}', [AdminController::class, 'undoConfirm'])->name('bikes.undoConfirmed');
    Route::get('bikes/cancelled', [AdminController::class, 'getCancelled'])->name('bikes.cancelled');
    Route::get('bikes/cancelled/undo/{booking:id}', [AdminController::class, 'undoCancel'])->name('bikes.undoCancel');
    Route::get('bikes/bookings/all', [AdminController::class, 'allBookings'])->name('admin.allBookings');
    Route::get('booking/delete/{booking:id}', [AdminController::class, 'deletebooking'])->name('booking.delete');

    Route::get('bikes', [AdminController::class, 'bikes'])->name('admin.bikes');
    Route::get('bikes/add', [AdminController::class, 'bikeAddForm'])->name('admin.addBike');

    // Route::get('/image', function() {

    //     $img =  ImageManagerStatic::make('bc2.jpg')->resize(190, 73);
    //     return $img->response('png');
    // });

    // Route::post('bikes/drop', [AdminController::class, 'bikesDrop'])->name('drop.bikes');
    Route::post('bikes/store', [AdminController::class, 'storebike'])->name('admin.storebike');
    Route::get('bike/update/{bike:id}', [AdminController::class, 'getSingleBike'])->name('admin.getSingleBike');
    Route::post('bike/update/{bike:id}', [AdminController::class, 'updateBike'])->name('admin.updateBike');
    Route::get('bike/remove/{bike:id}', [AdminController::class, 'removeBike'])->name('admin.removeBike');

    //for brands
    Route::get('brands', [AdminController::class, 'getBrands'])->name('admin.allBrands');
    Route::get('brand/add', [AdminController::class, 'addBrand'])->name('admin.addBrand');
    Route::post('brand/store', [AdminController::class, 'storeBrand'])->name('admin.storeBrand');
    Route::get('brand/update/{brand:id}', [AdminController::class, 'getSingleBrand'])->name('admin.singleBrand');
    Route::post('brand/update/{brand:id}', [AdminController::class, 'updateBrand'])->name('admin.updateBrand');
    Route::get('brand/remove/{brand:id}', [AdminController::class, 'removeBrand'])->name('admin.removeBrand');

    //for reviews
    Route::get('reviews', [AdminController::class, 'getReviews'])->name('admin.allReviews');
    Route::get('review/remove/{review:id}', [AdminController::class, 'removeReview'])->name('admin.removeReview');

    //for users
    Route::get('users', [AdminController::class, 'getUsers'])->name('admin.allUsers');
    Route::get('user/remove/{user:id}', [AdminController::class, 'removeUser'])->name('admin.removeUser');

    //for mechanics
    Route::get('mechanics', [AdminController::class, 'getMechanics'])->name('admin.allMech');
    Route::get('mechanic/add', [AdminController::class, 'addMechanic'])->name('admin.addMech');
    Route::post('mechanic/store', [AdminController::class, 'storeMechanic'])->name('admin.storeMech');
    Route::get('mechanic/update/{mechanic:id}', [AdminController::class, 'getSingleMechanic'])->name('admin.singleMech');
    Route::post('mechanic/update/{mechanic:id}', [AdminController::class, 'updateMechanic'])->name('admin.updateMech');
    Route::get('mechanic/remove/{mechanic:id}', [AdminController::class, 'removeMechanic'])->name('admin.removeMech');
});

//Mechanic's Appointment
Route::group(['prefix' => 'mechanic'], function () {
    Route::group(['prefix' => 'appointment'], function () {
        Route::get('requested/{mechanic:id}', [MechanicController::class, 'requested'])->name('mech.requested');
        Route::get('confirm/{appointment:id}', [MechanicController::class, 'confirm'])->name('mech.confirm');
        Route::get('cancel/{appointment:id}', [MechanicController::class, 'cancel'])->name('mech.cancel');

        Route::get('confirmed/{mechanic:id}', [MechanicController::class, 'getConfirmed'])->name('mech.getConfirmed');
        Route::get('confirmed/undo/{appointment:id}', [MechanicController::class, 'undoConfirmed'])->name('mech.undoConfirmed');

        Route::get('cancelled/{mechanic:id}', [MechanicController::class, 'gettCancelled'])->name('mech.getCancelled');
        Route::get('cancelled/undo/{appointment:id}', [MechanicController::class, 'undoCancel'])->name('mech.undoCancel');

        Route::get('all/{mechanic:id}', [MechanicController::class, 'getAll'])->name('mech.getAll');
        Route::get('remove/{appointment:id}', [MechanicController::class, 'remove'])->name('mech.remove');
    });

    Route::get('chat', [MechanicController::class, 'mechMessages'])->name('mech.messages');
});

Route::get('/user/chat', function () {
    return view('main.user-chat');
})->middleware(['auth']);

Route::get('/get/messages/{id}', [MechanicController::class, 'getMessages']);

Route::get('/user/chat/{id}', [BikeRentController::class, 'getChatList'])->middleware(['auth'])->name('user.chatList');

Route::post('/message/send/{id}', [MechanicController::class, 'sendMessage']);

Route::get('/user/account', [BikeRentController::class, 'getAccount'])->middleware(['auth'])->name('user.account');

Route::post('/user/account/update/{user:id}', [BikeRentController::class, 'updateUser'])->middleware('auth')->name('user.update');

Route::get('/about', [BikeRentController::class, 'aboutUs'])->name('aboutUs');
