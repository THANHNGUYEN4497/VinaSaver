<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/', function () {
    return 'Locofull API';
});

Route::group(['prefix' => 'admin'], function () {
    Route::post('login', ['uses' => 'AdminController@login']);

    Route::group(['middleware' => 'auth:admin-api'], function () {
        Route::post('logout', ['uses' => 'AdminController@logout']);
        Route::group(['prefix' => 'connector'], function () {
            Route::get('list', ['uses' => 'ConnectorController@index']);
            Route::get('detail/{id}', ['uses' => 'ConnectorController@detail']);
            Route::post('delete/{id}', ['uses' => 'ConnectorController@delete']);
        });
        Route::group(['prefix' => 'job'], function () {
            Route::get('list', ['uses' => 'JobController@index']);
            Route::get('detail/{id}', ['uses' => 'JobController@detail']);
            Route::post('delete/{id}', ['uses' => 'JobController@delete']);
            Route::get('category', ['uses' => 'JobController@getListCategory']);

            //ApplicantController
            Route::group(['prefix' => 'applicant'], function () {
                Route::get('list', ['uses' => 'ApplicantController@index']);
                Route::get('detail/{id}', ['uses' => 'ApplicantController@detail']);
                Route::get('job/{job_id}', ['uses' => 'ApplicantController@detailJob']);
            });
        });

        //COMPANY
        Route::group(['prefix' => 'company'], function () {
            Route::get('list', ['uses' => 'CompanyController@search']);
            Route::get('detail/{id}', ['uses' => 'CompanyController@show']);
            Route::post('add', ['uses' => 'CompanyController@store']);
            Route::post('edit/{id}', ['uses' => 'CompanyController@update']);
            Route::post('delete/{id}', ['uses' => 'CompanyController@destroy']);
        });

//        BUSINESS FIELD
        Route::group(['prefix' => 'business'], function () {
            Route::get('list', ['uses' => 'CompanyController@getBusinessField']);
        });

        //POSITION
        Route::group(['prefix' => 'position'], function () {
            Route::get('list', ['uses' => 'StaffController@getPositions']);
        });

        //STAFF
        Route::group(['prefix' => 'staff'], function () {
            Route::get('list', ['uses' => 'StaffController@search']);
            Route::get('detail/{id}', ['uses' => 'StaffController@show']);
        });

        //PAYMENT
        Route::group(['prefix' => 'payment'], function () {
            Route::get('list', ['uses' => 'PaymentController@search']);
            Route::post('delete/{id}', ['uses' => 'PaymentController@destroy']);
            Route::post('edit/{id}', ['uses' => 'PaymentController@update']);
        });


        //ADMIN
        Route::post('add', ['uses' => 'AdminController@store']);
        Route::get('list', ['uses' => 'AdminController@search']);
        Route::get('detail/{id}', ['uses' => 'AdminController@show']);
        Route::post('edit/{id}', ['uses' => 'AdminController@update']);
        Route::post('delete/{id}', ['uses' => 'AdminController@destroy']);
    });
});

//COMPANY
Route::group(['prefix' => 'company'], function () {
    Route::post('login', ['uses' => 'StaffController@login']);
    Route::group(['middleware' => 'auth:staff-api'], function () {
        Route::post('logout', ['uses' => 'StaffController@logout']);
        Route::get('detail/{id}', ['uses' => 'CompanyController@show']);
        Route::post('edit/{id}', ['uses' => 'CompanyController@editByStaff']);
        Route::get('business-field', ['uses' => 'CompanyController@getBusinessField']);
        Route::post('report', ['uses' => 'CompanyController@report']);
        Route::post('transfer', ['uses' => 'PaymentController@transfer']);

        //STAFF
        Route::group(['prefix' => 'staff'], function () {
            Route::get('list', ['uses' => 'StaffController@index']);
            Route::get('detail/{id}', ['uses' => 'StaffController@detail']);
            Route::post('add', ['uses' => 'StaffController@add']);
            Route::post('edit/{id}', ['uses' => 'StaffController@edit']);
            Route::post('delete/{id}', ['uses' => 'StaffController@delete']);
        });

        //CHAT
        Route::group(['prefix' => 'chat'], function () {
            Route::get('list', ['uses' => 'ChatController@getListChatByCompanyId']);
            Route::get('total-not-seen', ['uses' => 'ChatController@getTotalNotSeen']);
            Route::get('message-detail/{id}', ['uses' => 'ChatController@getChatMessageDetailById']);
            Route::get('detail/{id}', ['uses' => 'ChatController@getChatDetailById']);
            Route::post('update-message-status/{chat_id}', ['uses' => 'ChatController@updateMessageStatus']);
            Route::post('update-note', ['uses' => 'ChatController@updateNote']);
        });

        //JOB
        Route::group(['prefix' => 'job'], function () {
            Route::post('add', ['uses' => 'JobController@add']);
            Route::post('edit/{id}', ['uses' => 'JobController@edit']);
            Route::post('delete/{id}', ['uses' => 'JobController@delete']);
            Route::get('detail/{id}', ['uses' => 'JobController@detailForCompany']);
            Route::get('list', ['uses' => 'JobController@getByStaff']);


            Route::group(['prefix' => 'applicant'], function () {
                Route::get('list', ['uses' => 'ApplicantController@getByStaff']);
                Route::get('new', ['uses' => 'ApplicantController@getNewByStaff']);
                Route::get('new/count', ['uses' => 'ApplicantController@countNew']);
                Route::get('job/{job_id}', ['uses' => 'ApplicantController@detailJob']);
                Route::post('accept', ['uses' => 'ApplicantController@accept']);
                Route::post('ignore', ['uses' => 'ApplicantController@ignore']);
                Route::post('recruit', ['uses' => 'ApplicantController@recruit']);
                Route::post('bonus', ['uses' => 'ApplicantController@bonus']);
                Route::post('report', ['uses' => 'ApplicantController@report']);
                Route::post('settle', ['uses' => 'ApplicantController@settle']);
                Route::get('detail/{id}', ['uses' => 'ApplicantController@detailWorkConnection']);
                Route::post('delete/{id}', ['uses' => 'ApplicantController@delete']);
                Route::post('unnew/{id}', ['uses' => 'ApplicantController@updateIsNew']);
            });
            
            Route::group(['prefix' => 'chat'], function () {
                Route::get('detail', ['uses' => 'ChatController@getChatDetailByCompany']);
                Route::post('delete/{id}', ['uses' => 'ChatController@delete']);
            });

            Route::group(['prefix' => 'extend'], function () {
                Route::get('category', ['uses' => 'JobExtendController@getListCategory']);
                Route::get('job-category', ['uses' => 'JobExtendController@getListJobCategory']);
                Route::get('job-type', ['uses' => 'JobExtendController@getListType']);
                Route::get('area', ['uses' => 'JobExtendController@getListArea']);
                Route::get('staff', ['uses' => 'JobExtendController@getListStaff']);
                Route::get('position', ['uses' => 'JobExtendController@getListPosition']);
            });
        });

        //CREDIT CARD
        Route::group(['prefix' => 'credit-card'], function () {
            Route::get('list', ['uses' => 'CreditCardController@index']);
            Route::get('detail/{id}', ['uses' => 'CreditCardController@show']);
            Route::post('add', ['uses' => 'CreditCardController@add']);
            Route::post('edit/{id}', ['uses' => 'CreditCardController@edit']);
            Route::post('delete/{id}', ['uses' => 'CreditCardController@delete']);
        });
        
        //PAYMENT
        Route::group(['prefix' => 'payment'], function () {
            Route::get('list', ['uses' => 'PaymentController@getPaymentByCompany']);
            Route::post('update-status/{id}', ['uses' => 'PaymentController@update']);
            Route::post('delete/{id}', ['uses' => 'PaymentController@destroy']);
        });
    });
});

Route::group(['prefix' => 'connector'], function () {
    Route::post('login', ['uses' => 'ConnectorController@login']);
    Route::post('register', ['uses' => 'ConnectorController@add']);
    Route::post('verify', ['uses' => 'ConnectorController@verify']);
    Route::post('forgot-password', ['uses' => 'ConnectorController@forgotPassword']);
    Route::post('reset-password', ['uses' => 'ConnectorController@changePassword']);
   
    Route::group(['middleware' => 'auth:connector-api'], function () {
        Route::post('logout', ['uses' => 'ConnectorController@logout']);
        Route::get('detail/{id}', ['uses' => 'ConnectorController@getInfo']);
        Route::post('edit/{id}', ['uses' => 'ConnectorController@update']);
        Route::get('total-money', ['uses' => 'ConnectorController@getTotalMoneyByIntroductionId']);
        Route::post('report', ['uses' => 'ConnectorController@report']);
        Route::post('update-avatar/{id}', ['uses' => 'ConnectorController@updateAvatar']);
        Route::get('getAreas', ['uses' => 'ConnectorController@getAreas']);
        Route::get('getJobCategories', ['uses' => 'ConnectorController@getJobCategories']);
        Route::get('getCategories', ['uses' => 'ConnectorController@getCategories']);
        Route::get('getCategories', ['uses' => 'ConnectorController@getCategories']);
        Route::get('getJobTypes', ['uses' => 'ConnectorController@getJobTypes']);
        
        Route::group(['prefix' => 'chat'], function () {
            Route::get('list', ['uses' => 'ChatController@getListChatByConnectorId']);
            Route::get('message-detail/{id}', ['uses' => 'ChatController@getChatMessageDetailByIdForApp']);
            Route::post('delete/{id}', ['uses' => 'ChatController@delete']);
            Route::get('infor-extend/{id}', ['uses' => 'ChatController@getChatInforExtendById']);
        });
        Route::group(['prefix' => 'job'], function () {
            Route::get('search', ['uses' => 'JobController@search']);
            Route::get('detail/{id}', ['uses' => 'JobController@detailByConnector']);
            Route::post('apply', ['uses' => 'JobController@apply']);
            Route::post('set-favorite', ['uses' => 'JobController@setFavorite']);
            Route::get('favorite', ['uses' => 'JobController@favorite']);
            Route::get('new', ['uses' => 'JobController@new']);
            Route::get('list-applied', ['uses' => 'JobController@getListJobsAppliedByConnectorId']);
            Route::get('list-by-category', ['uses' => 'JobController@getListJobsByCategoryId']);
            Route::get('list-with-category', ['uses' => 'JobController@getListJobsWithCategory']);
        });
        Route::get('introduction-status', ['uses' => 'IntroductionStatusController@index']);
        Route::group(['prefix' => 'request'], function () {
            Route::post('/', ['uses' => 'IntroductionStatusController@request']);
            Route::post('all', ['uses' => 'PaymentController@requestAll']);
            Route::get('list', ['uses' => 'PaymentController@getListRequest']);
        });
        Route::group(['prefix' => 'payment'], function () {
            Route::get('list', ['uses' => 'PaymentController@getListPaymentByConnectorId']);
        });
        Route::group(['prefix' => 'compensation'], function () {
            Route::get('history', ['uses' => 'PaymentController@getListCompensationHistory']);
        });
        Route::group(['prefix' => 'notification'], function () {
            Route::get('list', ['uses' => 'NotificationController@getByConnector']);
        });
        Route::group(['prefix' => 'user-registration-id'], function () {
            Route::get('list', ['uses' => 'UserRegistrationIdController@getListTokenByConnector']);
            Route::post('add', ['uses' => 'UserRegistrationIdController@add']);
        });
    });
});
